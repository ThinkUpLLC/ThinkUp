<?php
/**
 *
 * ThinkUp/webapp/plugins/flickrthumbnails/model/class.FlickrThumbnailsPlugin.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 */
/**
 * Flickr Thumbnails Plugin
 *
 * Expands Flickr links to direct path to image thumbnail.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class FlickrThumbnailsPlugin implements CrawlerPlugin {

    public function crawl() {
        $config = Config::getInstance();

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('flickrthumbnails', true);
        $api_key = '';
        if (isset($options['flickr_api_key']->option_value)) {
            $api_key =  $options['flickr_api_key']->option_value;
        }

        if (isset($api_key) && $api_key != '') {
            $logger = Logger::getInstance();
            $logger->setUsername(null);
            $fa = new FlickrAPIAccessor($api_key);
            $ldao = DAOFactory::getDAO('LinkDAO');

            $flickrlinkstoexpand = $ldao->getLinksToExpandByURL('http://flic.kr/');
            if (count($flickrlinkstoexpand) > 0) {
                $logger->logUserInfo(count($flickrlinkstoexpand)." Flickr links to expand.",  __METHOD__.','.__LINE__);
            } else {
                $logger->logUserInfo("There are no Flickr thumbnails to expand.",  __METHOD__.','.__LINE__);
            }

            $total_thumbnails = 0;
            $total_errors = 0;
            foreach ($flickrlinkstoexpand as $fl) {
                $eurl = $fa->getFlickrPhotoSource($fl);
                if ($eurl["expanded_url"] != '') {
                    $ldao->saveExpandedUrl($fl, $eurl["expanded_url"], '', 1);
                    $total_thumbnails = $total_thumbnails + 1;
                } elseif ($eurl["error"] != '') {
                    $ldao->saveExpansionError($fl, $eurl["error"]);
                    $total_errors = $total_errors + 1;
                }
                $logger->logUserSuccess($total_thumbnails." Flickr thumbnails expanded (".$total_errors." errors)",
                __METHOD__.','.__LINE__);
            }
        }
    }

    public function renderConfiguration($owner) {
        $controller = new FlickrThumbnailsPluginConfigurationController($owner, 'flickrthumbnails');
        return $controller->go();
    }
}
