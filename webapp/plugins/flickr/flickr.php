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
    
    if (isset($THINKTANK_CFG['flickr_api_key']) && $THINKTANK_CFG['flickr_api_key'] != '') {
        $logger = new Logger($THINKTANK_CFG['log_location']);
        $fa = new FlickrAPIAccessor($THINKTANK_CFG['flickr_api_key'], $logger);
        $ldao = new LinkDAO($db, $logger);
        
        $flickrlinkstoexpand = $ldao->getLinksToExpandByURL('http://flic.kr/');
        if (count($flickrlinkstoexpand > 0)) {
            $logger->logStatus(count($flickrlinkstoexpand)." Flickr links to expand", "Flickr Plugin");
        } else {
            $logger->logStatus("No Flickr links to expand", "Flickr Plugin");
        }
        
        foreach ($flickrlinkstoexpand as $fl) {
            $eurl = $fa->getFlickrPhotoSource($fl->url);
            if ($eurl != '') {
                $is_image = 1;
            }
            $ldao->saveExpandedUrl($fl->id, $eurl, '', 1);
        }
        $logger->close(); # Close logging
    }
}

function flickr_webapp_configuration() {

    // TODO Add setting for the Flickr API key here
}


$crawler->registerCallback('flickr_crawl', 'crawl');

$webapp->addToConfigMenu('flickr', 'Twitter');
$webapp->registerCallback('flickr_webapp_configuration', 'configuration|flickr');
?>
