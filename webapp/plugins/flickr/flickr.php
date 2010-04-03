<?php 
/*
 Plugin Name: Flickr
 Plugin URI: http://github.com/ginatrapani/thinktank/tree/master/common/plugins/longurl/
 Icon: flickr_icon.png
 Description: Expands shortened links to Flickr photos to the image thumbnail. (NOT YET IMPLEMENTED; DOES NOTHING RIGHT NOW.)
 Version: 0.01
 Author: Gina Trapani
 */

function flickr_crawl() {
    global $THINKTANK_CFG;
    global $db;
    global $conn;
    
    //TODO Select all URLs from the links table that are shortened Flickr URLs
    // for each, expand and save
    /* The following code extracted from the TwitterCrawler:processTweetURLs method
     elseif ($fa->api_key != null && substr($u, 0, strlen('http://flic.kr/p/')) == 'http://flic.kr/p/') {
     $eurl = $fa->getFlickrPhotoSource($u);
     if ($eurl != '') {
     $is_image = 1;
     }
     }
     */
}

function flickr_webapp_configuration() {

    // TODO Add settings for the Flickr API key here
}


$crawler->registerCallback('longurl_crawl', 'crawl');

$webapp->addToConfigMenu('longurl', 'Twitter');
$webapp->registerCallback('flickr_webapp_configuration', 'configuration|flickr');
?>
