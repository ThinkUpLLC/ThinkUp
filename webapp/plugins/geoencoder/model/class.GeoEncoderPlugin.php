<?php
/**
 *
 * ThinkUp/webapp/plugins/geoencoder/model/class.GeoEncoderPlugin.php
 *
 * Copyright (c) 2009-2010 ekansh
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * GeoEncoder Plugin
 *
 * The GeoEncoder plugin validates the geolocation information for a post and stores it to use
 * for Geolocation visualization later.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 ekansh
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class GeoEncoderPlugin implements CrawlerPlugin {

    public function crawl() {
        $logger = Logger::getInstance();
        $pdao = DAOFactory::getDAO('PostDAO');
        $crawler = new GeoEncoderCrawler;

        $posts_to_geoencode = $pdao->getPostsToGeoencode(2000);
        $logger->logStatus(count($posts_to_geoencode)." posts to geoencode", "GeoEncoder Plugin");

        foreach ($posts_to_geoencode as $post_data) {
            if ($post_data['geo']!='') {
                $crawler->performReverseGeoencoding($pdao, $post_data);
            } else {
                $crawler->performGeoencoding($pdao, $post_data);
            }
        }
        $logger->logStatus("Geoencoding posts complete", "GeoEncoderPlugin");
        $logger->close(); # Close logging
    }

    public function renderConfiguration($owner) {
        $controller = new GeoEncoderPluginConfigurationController($owner, 'geoencoder');
        return $controller->go();
    }
}