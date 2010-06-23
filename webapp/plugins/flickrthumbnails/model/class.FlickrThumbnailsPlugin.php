<?php
class FlickrThumbnailsPlugin implements CrawlerPlugin {

    function crawl() {
        $config = Config::getInstance();
        $api_key = $config->getValue('flickr_api_key');

        if (isset($api_key) && $api_key != '') {
            $logger = Logger::getInstance();
            $fa = new FlickrAPIAccessor($api_key);
            $ldao = DAOFactory::getDAO('LinkDAO');

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

    function renderConfiguration($owner) {
        // TODO Add setting for the Flickr API key here
    }
}
