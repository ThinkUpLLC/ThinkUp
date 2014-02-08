<?php
/**
 *
 * webapp/plugins/youtube/model/class.YouTubePlugin.php
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
 *
 * YouTube Plugin
 *
 * Copyright (c) 2013 Aaron Kalair
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot][com]>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 */

class YouTubePlugin extends Plugin implements CrawlerPlugin {

    public function __construct($vals=null) {
        parent::__construct($vals);
        $this->folder_name = 'youtube';
        $this->addRequiredSetting('youtube_client_secret');
        $this->addRequiredSetting('youtube_client_id');
    }

    public function activate() {

    }

    public function deactivate() {

    }

    public function renderConfiguration($owner) {
        $controller = new YouTubePluginConfigurationController($owner);
        return $controller->go();
    }

    public function crawl() {
        $logger = Logger::getInstance();
        $config = Config::getInstance();
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('youtube', true); //get cached

        $max_crawl_time = isset($options['max_crawl_time']) ? $options['max_crawl_time']->option_value : 20;
        //convert to seconds
        $max_crawl_time = $max_crawl_time * 60;

        $developer_key = isset($options['developer_key']) ? $options['developer_key']->option_value : null;

        $max_comments = isset($options['max_comments']) ? $options['max_comments']->option_value : null;

        $current_owner = $owner_dao->getByEmail(Session::getLoggedInUser());

        //crawl youtube users
        $instances = $instance_dao->getActiveInstancesStalestFirstForOwnerByNetworkNoAuthError($current_owner,
        'youtube');

        if (isset($options['youtube_client_id']->option_value)
        && isset($options['youtube_client_secret']->option_value)) {
            foreach ($instances as $instance) {
                $logger->setUsername(ucwords($instance->network) . ' | '.$instance->network_username );
                $logger->logUserSuccess("Starting to collect data for ".$instance->network_username."'s ".
                ucwords($instance->network), __METHOD__.','.__LINE__);

                $tokens = $owner_instance_dao->getOAuthTokens($instance->id);
                $access_token = $tokens['oauth_access_token'];
                $refresh_token = $tokens['oauth_access_token_secret'];
                $instance_dao->updateLastRun($instance->id);
                $youtube_crawler = new YouTubeCrawler($instance, $access_token, $max_crawl_time, $developer_key,
                $max_comments);
                $dashboard_module_cacher = new DashboardModuleCacher($instance);
                try {
                    $youtube_crawler->initializeInstanceUser($options['youtube_client_id']->option_value,
                    $options['youtube_client_secret']->option_value, $access_token, $refresh_token,
                    $current_owner->id);
                    $youtube_crawler->fetchInstanceUserVideos();
                } catch (Exception $e) {
                    $logger->logUserError('EXCEPTION: '.$e->getMessage(), __METHOD__.','.__LINE__);
                }

                $dashboard_module_cacher->cacheDashboardModules();
                $instance_dao->save($youtube_crawler->instance, 0, $logger);
                Reporter::reportVersion($instance);
                $logger->logUserSuccess("Finished collecting data for ".$instance->network_username."'s ".
                ucwords($instance->network), __METHOD__.','.__LINE__);
            }
        }

    }

    public function renderInstanceConfiguration($owner, $instance_username, $instance_network) {
        return "";
    }
}
