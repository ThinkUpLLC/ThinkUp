<?php
/**
 * Flickr Thumbnails Plugin
 *
 * Expands Flickr links to direct path to image thumbnail.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class FlickrThumbnailsPlugin implements CrawlerPlugin {

    public function crawl() {
        $config = Config::getInstance();

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('flickrthumbnails', true);
        $api_key =  $options['flickr_api_key']->option_value;

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

    public function renderConfiguration($owner) {
        $controller = new FlickrThumbnailsPluginConfigurationController($owner, 'flickrthumbnails');
        return $controller->go();
    }
}
