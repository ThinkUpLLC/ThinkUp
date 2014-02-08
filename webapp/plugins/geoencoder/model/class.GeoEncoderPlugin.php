<?php
/**
 *
 * ThinkUp/webapp/plugins/geoencoder/model/class.GeoEncoderPlugin.php
 *
 * Copyright (c) 2009-2013 Ekansh Preet Singh, Mark Wilkie
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
 * GeoEncoder Plugin
 *
 * The GeoEncoder plugin validates the geolocation information for a post and stores it to use
 * for Geolocation visualization later.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Ekansh Preet Singh, Mark Wilkie
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class GeoEncoderPlugin extends Plugin implements CrawlerPlugin {

    public function __construct($vals=null) {
        parent::__construct($vals);
        $this->folder_name = 'geoencoder';
        $this->addRequiredSetting('gmaps_api_key');
    }

    public function activate() {
    }

    public function deactivate() {
    }


    public function crawl() {
        $logger = Logger::getInstance();
        $logger->setUsername(null);
        $post_dao = DAOFactory::getDAO('PostDAO');
        $geoencoder_crawler = new GeoEncoderCrawler();

        $posts_to_geoencode = $post_dao->getPostsToGeoencode(2000);
        $logger->logUserSuccess("Starting to collect lat/long points for ".count($posts_to_geoencode)." posts.",
        __METHOD__.','.__LINE__);

        $total_api_requests_fulfilled = 0;
        foreach ($posts_to_geoencode as $post_data) {
            if ($post_data['geo']!='') {
                if ($geoencoder_crawler->performReverseGeoencoding($post_dao, $post_data)) {
                    $total_api_requests_fulfilled++;
                }
            } else {
                if ($geoencoder_crawler->performGeoencoding($post_dao, $post_data)) {
                    $total_api_requests_fulfilled++;
                }
            }
        }
        $logger->logUserSuccess("Post geoencoding complete. ".$total_api_requests_fulfilled.
        " API requests fulfilled successfully.", __METHOD__.','.__LINE__);
    }

    public function renderConfiguration($owner) {
        $controller = new GeoEncoderPluginConfigurationController($owner, 'geoencoder');
        return $controller->go();
    }

    public function renderInstanceConfiguration($owner, $instance_username, $instance_network) {
        return '';
    }
}