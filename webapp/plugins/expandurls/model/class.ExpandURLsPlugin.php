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
    /**
     * Max number of links to expand during a given crawl; set in ExpandURLs Plugin options area.
     * @var int
     */
    var $link_limit = 0;

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

        //Limit the number of links expanded each crawl
        $this->link_limit = isset($options['links_to_expand']->option_value) ?
        (int)$options['links_to_expand']->option_value : 1500;

        if ($this->link_limit != 0) {
            //Flickr image thumbnails
            if (isset($options['flickr_api_key']->option_value)) {
                self::expandFlickrThumbnails($options['flickr_api_key']->option_value);
            }

            //Bit.ly URLs
            if (isset($options['bitly_api_key']->option_value,
            $options['bitly_login']->option_value)) {
                self::expandBitlyLinks($options['bitly_api_key']->option_value,
                $options['bitly_login']->option_value);
            }

            //Remaining links
            self::expandRemainingURLs();
        } else {
            $logger->logUserInfo("Limit of links to expand reached.", __METHOD__.','.__LINE__);
        }
    }

    /**
     * Render the config page.
     */
    public function renderConfiguration($owner) {
        $controller = new ExpandURLsPluginConfigurationController($owner, 'expandurls');
        return $controller->go();
    }

    /**
     * Expand shortened Flickr links to image thumbnails if Flickr API key is set.
     * @param $api_key Flickr API key
     */
    public function expandFlickrThumbnails($api_key) {
        $logger = Logger::getInstance();
        $link_dao = DAOFactory::getDAO('LinkDAO');
        //Flickr thumbnails
        $logger->setUsername(null);
        $flickr_api = new FlickrAPIAccessor($api_key);

        $flickr_links_to_expand = $link_dao->getLinksToExpandByURL('http://flic.kr/', $this->link_limit);
        if (count($flickr_links_to_expand) > 0) {
            $logger->logUserInfo(count($flickr_links_to_expand)." Flickr links to expand.",  __METHOD__.','.__LINE__);
        } else {
            $logger->logInfo("There are no Flickr thumbnails to expand.",  __METHOD__.','.__LINE__);
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
        if (count($flickr_links_to_expand) > 0) {
            $logger->logUserSuccess($total_thumbnails." Flickr thumbnails expanded (".$total_errors." errors)",
            __METHOD__.','.__LINE__);
        }
    }

    /**
     * Expand Bit.ly links and recheck click count on old ones.
     *
     * @param str bitly api key
     * @param str bitly login name
     */
    public function expandBitlyLinks($api_key, $bit_login) {
        $logger = Logger::getInstance();
        $link_dao = DAOFactory::getDAO('LinkDAO');

        $logger->setUsername(null);
        $api_accessor = new BitlyAPIAccessor($api_key, $bit_login);

        $bitly_urls = array('http://bit.ly/', 'http://bitly.com/', 'http://j.mp/');
        foreach ($bitly_urls as $bitly_url) {
            if ($this->link_limit != 0) {
                $bitly_links_to_expand = $link_dao->getLinksToExpandByURL($bitly_url, $this->link_limit);

                if (count($bitly_links_to_expand) > 0) {
                    $logger->logUserInfo(count($bitly_links_to_expand). " $bitly_url" . " links to expand.",
                    __METHOD__.','.__LINE__);
                } else {
                    $logger->logUserInfo("There are no " . $bitly_url . " links to expand.", __METHOD__.','.__LINE__);
                }

                $total_links = 0;
                $total_errors = 0;
                foreach ($bitly_links_to_expand as $link) {
                    $link_data = $api_accessor->getBitlyLinkData($link);
                    if ($link_data["expanded_url"] != '') {
                        $link_dao->saveExpandedUrl($link, $link_data["expanded_url"], $link_data["title"], '',
                        $link_data["clicks"]);
                        $total_links = $total_links + 1;
                    } elseif ($link_data["error"] != '') {
                        $link_dao->saveExpansionError($link, $link_data["error"]);
                        $total_errors = $total_errors + 1;
                    }
                }

                $logger->logUserSuccess($total_links. " " . $bitly_url . " links expanded (".$total_errors." errors)",
                __METHOD__.','.__LINE__);
            }
        }
    }

    /**
     * Save expanded version of all unexpanded URLs to data store.
     */
    public function expandRemainingURLs() {
        $logger = Logger::getInstance();
        $link_dao = DAOFactory::getDAO('LinkDAO');
        $links_to_expand = $link_dao->getLinksToExpand($this->link_limit);

        $logger->logUserInfo(count($links_to_expand)." links to expand. Please wait. Working...",
        __METHOD__.','.__LINE__);

        $total_expanded = 0;
        $total_errors = 0;
        foreach ($links_to_expand as $index=>$link) {
            if (Utils::validateURL($link)) {
                $logger->logInfo("Expanding ".($total_expanded+1). " of ".count($links_to_expand)." (".$link.")",
                __METHOD__.','.__LINE__);

                //make sure shortened short links--like t.co--get fully expanded
                $fully_expanded = false;
                $short_link = $link;
                while (!$fully_expanded) {
                    $expanded_url = self::untinyurl($short_link, $link_dao, $link, $index, count($links_to_expand));
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
     * @param int $current_number Current link number
     * @param int $total_number Total links in group
     * @return str Expanded URL
     */
    private function untinyurl($tinyurl, $link_dao, $original_link, $current_number, $total_number) {
        $error_log_prefix = $current_number." of ".$total_number." links: ";
        $logger = Logger::getInstance();
        $url = parse_url($tinyurl);
        if (isset($url['host'])) {
            $host = $url['host'];
        } else {
            $error_msg = $tinyurl.": No host found.";
            $logger->logError($error_log_prefix.$error_msg, __METHOD__.','.__LINE__);
            $link_dao->saveExpansionError($original_link, $error_msg);
            return '';
        }
        $port = isset($url['port']) ? ':'.$url['port'] : '';
        $query = isset($url['query']) ? '?'.$url['query'] : '';
        $fragment = isset($url['fragment']) ? '#'.$url['fragment'] : '';
        if (empty($url['path'])) {
            $path = '';
        } else {
            $path = $url['path'];
        }
        $scheme = isset($url['scheme'])?$url['scheme']:'http';

        $reconstructed_url = $scheme."://$host$port".$path.$query.$fragment;
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
            $logger->logError($error_log_prefix.$error_msg, __METHOD__.','.__LINE__);
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
            $logger->logError($error_log_prefix.$error_msg, __METHOD__.','.__LINE__);
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