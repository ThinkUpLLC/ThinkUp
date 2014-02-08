<?php
/**
 *
 * ThinkUp/webapp/plugins/foursquare/model/class.FoursquarePlugin.php
 *
 * Copyright (c) 2012-2013 Aaron Kalair
 *
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Foursquare Plugin
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Aaron Kalair
 */

class FoursquarePlugin extends Plugin implements CrawlerPlugin {

    public function __construct($vals=null) {
        // Pass the values to the parents constructor
        parent::__construct($vals);
        // Set the foldername to foursquare
        $this->folder_name = 'foursquare';
        // Set the client secret
        $this->addRequiredSetting('foursquare_client_secret');
        // Set the client id
        $this->addRequiredSetting('foursquare_client_id');
    }

    public function activate() {

    }

    public function deactivate() {

    }

    public function renderConfiguration($owner) {
        // Create a new controller for the plugin
        $controller = new FoursquarePluginConfigurationController($owner, 'foursquare');
        return $controller->go();
    }

    public function renderInstanceConfiguration($owner, $instance_username, $instance_network) {
        return '';
    }

    public function crawl() {
        $logger = Logger::getInstance();
        $config = Config::getInstance();
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('foursquare', true);
        $current_owner = $owner_dao->getByEmail(Session::getLoggedInUser());

        $instances = $instance_dao->getAllActiveInstancesStalestFirstByNetwork('foursquare');

        // Check the client id and secret are set or we can't crawl
        if (isset($options['foursquare_client_id']->option_value)
        && isset($options['foursquare_client_secret']->option_value)) {
            // For each instance of foursquare on this install
            foreach ($instances as $instance) {
                if (!$owner_instance_dao->doesOwnerHaveAccessToInstance($current_owner, $instance)) {
                    // Owner doesn't have access to this instance; let's not crawl it.
                    continue;
                }
                // Set the user name in the log
                $logger->setUsername(ucwords($instance->network) . ' | '.$instance->network_username );
                // Write to the log that we have started to collect data
                $logger->logUserSuccess("Starting to collect data for ".$instance->network_username."'s ".
                ucwords($instance->network), __METHOD__.','.__LINE__);

                // Get the OAuth tokens for this user
                $tokens = $owner_instance_dao->getOAuthTokens($instance->id);
                // Set the access token
                $access_token = $tokens['oauth_access_token'];
                // Update the last time we crawled
                $instance_dao->updateLastRun($instance->id);
                // Create a new crawler
                $crawler = new FoursquareCrawler($instance, $access_token);

                // Check the OAuth tokens we have are valid
                try {
                    $logger->logInfo("About to initializeInstanceUser", __METHOD__.','.__LINE__);
                    $user = $crawler->initializeInstanceUser($access_token, $current_owner->id);
                    if (isset($user) && $user instanceof User) {
                        $logger->logInfo("User initialized", __METHOD__.','.__LINE__);
                    }
                    // Get the data we want and store it in the database
                    $logger->logInfo("About to fetchInstanceUserCheckins", __METHOD__.','.__LINE__);
                    $crawler->fetchInstanceUserCheckins();
                } catch (Exception $e) {
                    // Catch any errors that happen when we check the validity of the OAuth tokens
                    $logger->logUserError('EXCEPTION: '.$e->getMessage(), __METHOD__.','.__LINE__);
                }
                $logger->logInfo("About to cache dashboard modules", __METHOD__.','.__LINE__);

                // Cache the insights to improve the page load speed of the dashboard
                $dashboard_module_cacher = new DashboardModuleCacher($instance);
                $dashboard_module_cacher->cacheDashboardModules();

                $instance_dao->save($crawler->instance, 0, $logger);
                Reporter::reportVersion($instance);
                // Tell the user crawling was sucessful
                $logger->logUserSuccess("Finished collecting data for ".$instance->network_username."'s ".
                ucwords($instance->network), __METHOD__.','.__LINE__);
            }
        }
    }
}
