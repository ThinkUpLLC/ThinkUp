<?php
/**
 *
 * ThinkUp/webapp/plugins/googleplus/model/class.GooglePlusPlugin.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 */
class GooglePlusPlugin extends Plugin implements CrawlerPlugin {

    public function __construct($vals=null) {
        parent::__construct($vals);
        $this->folder_name = 'googleplus';
        $this->addRequiredSetting('google_plus_client_secret');
        $this->addRequiredSetting('google_plus_client_id');
    }

    public function activate() {
    }

    public function deactivate() {
    }

    public function renderConfiguration($owner) {
        $controller = new GooglePlusPluginConfigurationController($owner, 'googleplus');
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
        $options = $plugin_option_dao->getOptionsHash('googleplus', true); //get cached

        $current_owner = $owner_dao->getByEmail(Session::getLoggedInUser());

        //crawl Google+ users
        $instances = $instance_dao->getActiveInstancesStalestFirstForOwnerByNetworkNoAuthError($current_owner,
        'google+');

        if (isset($options['google_plus_client_id']->option_value)
        && isset($options['google_plus_client_secret']->option_value)) {
            foreach ($instances as $instance) {
                $logger->setUsername(ucwords($instance->network) . ' | '.$instance->network_username );
                $logger->logUserSuccess("Starting to collect data for ".$instance->network_username."'s ".
                ucwords($instance->network), __METHOD__.','.__LINE__);

                $tokens = $owner_instance_dao->getOAuthTokens($instance->id);
                $access_token = $tokens['oauth_access_token'];
                $refresh_token = $tokens['oauth_access_token_secret'];

                $instance_dao->updateLastRun($instance->id);
                $google_plus_crawler = new GooglePlusCrawler($instance, $access_token);
                $dashboard_module_cacher = new DashboardModuleCacher($instance);
                try {
                    $google_plus_crawler->initializeInstanceUser($options['google_plus_client_id']->option_value,
                    $options['google_plus_client_secret']->option_value, $access_token, $refresh_token,
                    $current_owner->id);

                    $google_plus_crawler->fetchInstanceUserPosts();
                } catch (Exception $e) {
                    $logger->logUserError('EXCEPTION: '.$e->getMessage(), __METHOD__.','.__LINE__);
                }

                $dashboard_module_cacher->cacheDashboardModules();
                $instance_dao->save($google_plus_crawler->instance, 0, $logger);
                Reporter::reportVersion($instance);
                $logger->logUserSuccess("Finished collecting data for ".$instance->network_username."'s ".
                ucwords($instance->network), __METHOD__.','.__LINE__);
            }
        }
    }
}