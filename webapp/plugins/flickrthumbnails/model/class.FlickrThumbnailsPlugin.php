<?php
class FlickrThumbnailsPlugin implements iCrawlerPlugin {

    function crawl() {
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
                $eurl = $fa->getFlickrPhotoSource($fl);
                if ($eurl["expanded_url"] != '') {
                    $ldao->saveExpandedUrl($fl, $eurl["expanded_url"], '', 1);
                } elseif ($eurl["error"] != '') {
                    $ldao->saveExpansionError($fl, $eurl["error"]);
                }
            }
            $logger->close(); # Close logging
        }
    }
    
    function renderConfiguration() {
        // TODO Add setting for the Flickr API key here
    }
}
?>