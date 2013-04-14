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
class GeoEncoderPlugin extends Plugin implements CrawlerPlugin, PostDetailPlugin {

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

    public function getPostDetailMenuItems($post) {
        $menus = array();
        $map_template_path = Utils::getPluginViewDirectory('geoencoder').'geoencoder.map.tpl';
        if ($post->is_geo_encoded == 1) {
        //Define a menu item
            $map_menu_item = new MenuItem("Response Map", "", $map_template_path, 'GeoEncoder');
            //Define a dataset to be displayed when that menu item is selected
            $map_menu_item_dataset_1 = new Dataset("geoencoder_map", 'PostDAO', "getRelatedPosts",
            array($post->post_id, $post->network, 'location') );
            //Associate dataset with menu item
            $map_menu_item->addDataset($map_menu_item_dataset_1);
            //Add menu item to menu
            $menus["geoencoder_map"] = $map_menu_item;

            $nearest_template_path = Utils::getPluginViewDirectory('geoencoder').'geoencoder.nearest.tpl';
            //Define a menu item
            $nearest_menu_item = new MenuItem("Nearest Replies", "", $nearest_template_path);
            //Define a dataset to be displayed when that menu item is selected
            $nearest_dataset = new Dataset("geoencoder_nearest", 'PostDAO', "getRelatedPosts",
            array($post->post_id, $post->network, !Session::isLoggedIn()));
            //Associate dataset with menu item
            $nearest_menu_item->addDataset($nearest_dataset);
            $nearest_dataset_2 = new Dataset("geoencoder_options", 'PluginOptionDAO', 'getOptionsHash',
            array('geoencoder', true));
            $nearest_menu_item->addDataset($nearest_dataset_2);
            //Add menu item to menu
            $menus["geoencoder_nearest"] = $nearest_menu_item;
        }
        return $menus;
    }
}