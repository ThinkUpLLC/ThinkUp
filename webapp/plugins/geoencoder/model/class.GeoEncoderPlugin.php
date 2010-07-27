<?php
/**
 * GeoEncoder Plugin
 *
 * The GeoEncoder plugin validates the geolocation information for a post and stores it to use
 * for Geolocation visualization later.
 *
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * 
 */
class GeoEncoderPlugin implements CrawlerPlugin {
    
    public function crawl() {
        $logger = Logger::getInstance();
        $pdao = DAOFactory::getDAO('PostDAO');
        $crawler = new GeoEncoderCrawler;
        
        $posts_to_geoencode = $pdao->getPostsToGeoencode(500);
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

    }
}