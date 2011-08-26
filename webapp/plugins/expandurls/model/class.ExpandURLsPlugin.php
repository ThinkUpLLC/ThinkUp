<?php
/**
 *
 * ThinkUp/webapp/plugins/expandurls/model/class.ExpandURLsPlugin.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Christoffer Viken, Guillaume Boudreau, Mark Wilkie
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
 *
 *
 * ExpandURLs Crawler Plugin
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani, Christoffer Viken, Guillaume Boudreau, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ExpandURLsPlugin implements CrawlerPlugin {

    public function activate() {
    }

    public function deactivate() {
    }

    /**
     * Run when the crawler does
     */
    public function crawl() {
        $logger = Logger::getInstance();
        $logger->setUsername(null);

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('expandurls', true);

        //Flickr image thumbnails
        if (isset($options['flickr_api_key']->option_value)) {
            self::expandFlickrThumbnails($options['flickr_api_key']->option_value);
        }

        //@TODO: Bit.ly URLs

        //Remaining URLs
        $link_limit = isset($options['links_to_expand']->option_value) ?
        (int)$options['links_to_expand']->option_value : 1500;

        self::expandRemainingURLs($link_limit);
    }

    /**
     * Render the config page.
     */
    public function renderConfiguration($owner) {
        $controller = new ExpandURLsPluginConfigurationController($owner, 'expandurls');
        return $controller->go();
    }

    /**
     * Expand shortened Flickr links to image thumbnails if Flickr API key is set
     * @param $api_key Flickr API key
     */
    public function expandFlickrThumbnails($api_key) {
        $logger = Logger::getInstance();
        $link_dao = DAOFactory::getDAO('LinkDAO');
        //Flickr thumbnails
        $logger->setUsername(null);
        $flickr_api = new FlickrAPIAccessor($api_key);

        $flickr_links_to_expand = $link_dao->getLinksToExpandByURL('http://flic.kr/');
        if (count($flickr_links_to_expand) > 0) {
            $logger->logUserInfo(count($flickr_links_to_expand)." Flickr links to expand.",  __METHOD__.','.__LINE__);
        } else {
            $logger->logUserInfo("There are no Flickr thumbnails to expand.",  __METHOD__.','.__LINE__);
        }

        $total_thumbnails = 0;
        $total_errors = 0;
        foreach ($flickr_links_to_expand as $flickr_link) {
            $photo_details = $flickr_api->getFlickrPhotoSource($flickr_link);
            if ($photo_details["image_src"] != '') {
                //@TODO Make another Flickr API call to get the photo title & description and save to tu_links
                $link_dao->saveExpandedUrl($flickr_link, $flickr_link, '', $photo_details["image_src"]);
                $total_thumbnails = $total_thumbnails + 1;
            } elseif ($photo_details["error"] != '') {
                $link_dao->saveExpansionError($flickr_link, $photo_details["error"]);
                $total_errors = $total_errors + 1;
            }
        }
        $logger->logUserSuccess($total_thumbnails." Flickr thumbnails expanded (".$total_errors." errors)",
        __METHOD__.','.__LINE__);
    }

    /**
     * Expand all unexpanded URLs
     * @param $total_links_to_expand The number of links to expand
     */
    public function expandRemainingURLs($total_links_to_expand) {
        $logger = Logger::getInstance();
        $link_dao = DAOFactory::getDAO('LinkDAO');
        $links_to_expand = $link_dao->getLinksToExpand($total_links_to_expand);

        $logger->logUserInfo(count($links_to_expand)." links to expand. Please wait. Working...",
        __METHOD__.','.__LINE__);

        $total_expanded = 0;
        $total_errors = 0;
        foreach ($links_to_expand as $link) {
            if (Utils::validateURL($link)) {
                $logger->logInfo("Expanding ".($total_expanded+1). " of ".count($links_to_expand)." (".$link.")",
                __METHOD__.','.__LINE__);

                //make sure shortened short links--like t.co--get fully expanded
                $fully_expanded = false;
                $short_link = $link;
                while (!$fully_expanded) {
                    $expanded_url = self::untinyurl($short_link, $link_dao, $link);
                    if ($expanded_url == $short_link || $expanded_url == '') {
                        $fully_expanded = true;
                    }
                    $short_link = $expanded_url;
                }
                if ($expanded_url != '') {
                    $image_src = URLProcessor::getImageSource($expanded_url);
                    $link_dao->saveExpandedUrl($link, $expanded_url, '', $image_src);
                    $total_expanded = $total_expanded + 1;
                } else {
                    $total_errors = $total_errors + 1;
                }
            } else {
                $total_errors = $total_errors + 1;
                $logger->logError($link." not a valid URL", __METHOD__.','.__LINE__);
                $link_dao->saveExpansionError($link, "Invalid URL");
            }
        }
        $logger->logUserSuccess($total_expanded." URLs successfully expanded (".$total_errors." errors).",
        __METHOD__.','.__LINE__);
    }

    /**
     * Return the expanded version of a given short URL or save an error for the $original_link in links table and
     * return an empty string.
     *
     * @param str $tinyurl Shortened URL
     * @param LinkDAO $link_dao
     * @param str $original_link
     * @return str Expanded URL
     */
    private function untinyurl($tinyurl, $link_dao, $original_link) {
        $logger = Logger::getInstance();
        $url = parse_url($tinyurl);
        if (isset($url['host'])) {
            $host = $url['host'];
        } else {
            $error_msg = $tinyurl.": No host found.";
            $logger->logError($error_msg, __METHOD__.','.__LINE__);
            $link_dao->saveExpansionError($original_link, $error_msg);
            return '';
        }
        $port = isset($url['port']) ? $url['port'] : 80;
        $query = isset($url['query']) ? '?'.$url['query'] : '';
        $fragment = isset($url['fragment']) ? '#'.$url['fragment'] : '';
        if (empty($url['path'])) {
            $path = '';
        } else {
            $path = $url['path'];
        }

        $reconstructed_url = "http://$host:$port".$path.$query.$fragment;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $reconstructed_url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // seconds
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $response = curl_exec($ch);
        if ($response === false) {
            $error_msg = $reconstructed_url." cURL error: ".curl_error($ch);
            $logger->logError($error_msg, __METHOD__.','.__LINE__);
            $link_dao->saveExpansionError($original_link, $error_msg);
            $tinyurl = '';
        }
        curl_close($ch);

        $lines = explode("\r\n", $response);
        foreach ($lines as $line) {
            if (stripos($line, 'Location:') === 0) {
                list(, $location) = explode(':', $line, 2);
                return ltrim($location);
            }
        }

        if (strpos($response, 'HTTP/1.1 404 Not Found') === 0) {
            $error_msg = $reconstructed_url." returned '404 Not Found'";
            $logger->logError($error_msg, __METHOD__.','.__LINE__);
            $link_dao->saveExpansionError($original_link, $error_msg);
            return '';
        }
        return $tinyurl;
    }

    /**
     * Safe wrapper for the feof function that implements a timeout.
     * See Example #1:
     * http://php.net/manual/en/function.feof.php
     * @param socket $fp Open socket
     * @param mixed $start Int or null
     */
    private function safe_feof($fp, &$start = null) {
        $start = microtime(true);
        return feof($fp);
    }
}
