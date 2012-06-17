<?php
/**
 *
 * ThinkUp/webapp/plugins/expandurls/model/class.ExpandURLsPlugin.php
 *
 * Copyright (c) 2009-2012 Gina Trapani, Christoffer Viken, Guillaume Boudreau, Mark Wilkie
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
 * @copyright 2009-2012 Gina Trapani, Christoffer Viken, Guillaume Boudreau, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ExpandURLsPlugin extends Plugin implements CrawlerPlugin {
    /**
     * Max number of links to expand during a given crawl; set in ExpandURLs Plugin options area.
     * @var int
     */
    var $link_limit = 0;
    /**
     * @var Logger
     */
    var $logger;
    /**
     *
     * @var LinkDAO
     */
    var $link_dao;
    /**
     * @var ShortLinkDAO
     */
    var $short_link_dao;
    /**
     * Maximum number of times to expand a given URL. This cap prevents endless expansion loops.
     * @var int
     */
    const EXPANSION_CAP = 8;
    public function __construct($vals=null) {
        parent::__construct($vals);
        $this->folder_name = 'expandurls';
        $this->logger = Logger::getInstance();
        $this->link_dao = DAOFactory::getDAO('LinkDAO');
        $this->short_link_dao = DAOFactory::getDAO('ShortLinkDAO');
    }

    public function activate() {
    }

    public function deactivate() {
    }

    /**
     * Render the config page.
     */
    public function renderConfiguration($owner) {
        $controller = new ExpandURLsPluginConfigurationController($owner, 'expandurls');
        return $controller->go();
    }

    /**
     * Run when the crawler does
     */
    public function crawl() {
        $this->logger->setUsername(null);

        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('expandurls', true);

        //Limit the number of links expanded each crawl
        $this->link_limit = isset($options['links_to_expand']->option_value) ?
        (int)$options['links_to_expand']->option_value : 1500;

        if ($this->link_limit != 0) {
            //set Flickr API key
            if (isset($options['flickr_api_key']->option_value)) {
                $flickr_api_key = $options['flickr_api_key']->option_value;
            } else {
                $flickr_api_key = null;
            }

            //Get short links into table via initial expansion
            self::expandOriginalURLs($flickr_api_key);

            //Capture click counts for bit.ly URLs
            if (isset($options['bitly_api_key']->option_value,
            $options['bitly_login']->option_value)) {
                self::acquireBitlyClickStats($options['bitly_api_key']->option_value,
                $options['bitly_login']->option_value);
            }

            //@TODO Capture click counts for goo.gl URLs
            //self::acquireGooglClickStats
        } else {
            $this->logger->logUserInfo("Limit of links to expand reached.", __METHOD__.','.__LINE__);
        }
    }

    /**
     * Save expanded version of all unexpanded URLs to data store, as well as intermediary short links.
     */
    public function expandOriginalURLs($flickr_api_key=null) {
        $links_to_expand = $this->link_dao->getLinksToExpand($this->link_limit);

        $this->logger->logUserInfo(count($links_to_expand)." links to expand. Please wait. Working...",
        __METHOD__.','.__LINE__);

        $total_expanded = 0;
        $total_errors = 0;
        $has_expanded_flickr_link = false;
        foreach ($links_to_expand as $index=>$link) {
            if (Utils::validateURL($link->url)) {
                $endless_loop_prevention_counter = 0;
                $this->logger->logInfo("Expanding ".($total_expanded+1). " of ".count($links_to_expand)." (".
                $link->url.")", __METHOD__.','.__LINE__);

                //make sure shortened short links--like t.co--get fully expanded
                $fully_expanded = false;
                $short_link = $link->url;
                while (!$fully_expanded) {
                    //begin Flickr thumbnail processing
                    if (isset($flickr_api_key)
                    && substr($short_link, 0, strlen('http://flic.kr/')) == 'http://flic.kr/') {
                        self::expandFlickrThumbnail($flickr_api_key, $short_link, $link->url);
                        $has_expanded_flickr_link = true;
                        $fully_expanded = true;
                    }
                    //end Flickr thumbnail processing
                    $expanded_url = URLExpander::expandURL($short_link,$link->url, $index, count($links_to_expand),
                    $this->link_dao, $this->logger);
                    if ($expanded_url == $short_link || $expanded_url == ''
                    || $endless_loop_prevention_counter > self::EXPANSION_CAP) {
                        $fully_expanded = true;
                    } else {
                        $this->short_link_dao->insert($link->id, $short_link);
                    }
                    $short_link = $expanded_url;
                    $endless_loop_prevention_counter++;
                }
                if (!$has_expanded_flickr_link) {
                    if ($expanded_url != '' ) {
                        $image_src = URLProcessor::getImageSource($expanded_url);
                        $this->link_dao->saveExpandedUrl($link->url, $expanded_url, '', $image_src);
                        $total_expanded = $total_expanded + 1;
                    } else {
                        $this->logger->logError($link->url." not a valid URL - relocates to nowhere",
                        __METHOD__.','.__LINE__);
                        $this->link_dao->saveExpansionError($link->url, "Invalid URL - relocates to nowhere");
                        $total_errors = $total_errors + 1;
                    }
                }
            } else {
                $total_errors = $total_errors + 1;
                $this->logger->logError($link->url." not a valid URL", __METHOD__.','.__LINE__);
                $this->link_dao->saveExpansionError($link->url, "Invalid URL");
            }
            $has_expanded_flickr_link = false;
        }
        $this->logger->logUserSuccess($total_expanded." URLs successfully expanded (".$total_errors." errors).",
        __METHOD__.','.__LINE__);
    }

    /**
     * Expand Bit.ly links and recheck click count on any links less than 2 days old.
     *
     * @param str bitly api key
     * @param str bitly login name
     */
    public function acquireBitlyClickStats($api_key, $bit_login) {
        $this->logger->setUsername(null);
        $api_accessor = new BitlyAPIAccessor($api_key, $bit_login);

        $bitly_urls = array('http://bit.ly/', 'http://bitly.com/', 'http://j.mp/');
        foreach ($bitly_urls as $bitly_url) {
            if ($this->link_limit != 0) {
                //all short links first seen in the last 48 hours
                $bitly_links_to_update = $this->short_link_dao->getLinksToUpdate($bitly_url);

                if (count($bitly_links_to_update) > 0) {
                    $this->logger->logUserInfo(count($bitly_links_to_update). " $bitly_url" .
                    " links to acquire click stats for.", __METHOD__.','.__LINE__);
                } else {
                    $this->logger->logUserInfo("There are no " . $bitly_url . " links to fetch click stats for.",
                    __METHOD__.',', __LINE__);
                }

                $total_links = 0;
                $total_errors = 0;
                $total_updated = 0;
                foreach ($bitly_links_to_update as $link) {
                    $this->logger->logInfo("Getting bit.ly click stats for ". ($total_updated+1). " of ".
                    count($bitly_links_to_update)." ".$bitly_url." links (".$link->short_url.")", __METHOD__.','.__LINE__);
                    $link_data = $api_accessor->getBitlyLinkData($link->short_url);
                    if ($link_data["clicks"] != '') {
                        //save click total here
                        $this->short_link_dao->saveClickCount($link->short_url, $link_data["clicks"]);
                        // Save title to links table
                        if ($link_data["title"] != '') {
                            $this->link_dao->updateTitle($link->link_id, $link_data["title"]);
                        }
                        $total_links = $total_links + 1;
                        $total_updated = $total_updated + 1;
                    } elseif ($link_data["error"] != '') {
                        $this->link_dao->saveExpansionError($link->short_url, $link_data["error"]);
                        $total_errors = $total_errors + 1;
                        $total_updated = $total_updated + 1;
                    }
                }

                $this->logger->logUserSuccess($total_links. " " . $bitly_url . " link click stats acquired (".
                $total_errors." errors)", __METHOD__.','.__LINE__);
            }
        }
    }

    /**
     * Expand shortened Flickr links to image thumbnails if Flickr API key is set.
     * @param $api_key Flickr API key
     * @param $flickr_link Flickr URL
     */
    public function expandFlickrThumbnail($api_key, $flickr_link, $original_link) {
        $flickr_api = new FlickrAPIAccessor($api_key);

        $photo_details = $flickr_api->getFlickrPhotoSource($flickr_link);
        if ($photo_details["image_src"] != '') {
            //@TODO Make another Flickr API call to get the photo title & description and save to tu_links
            $this->link_dao->saveExpandedUrl($original_link, $flickr_link, '', $photo_details["image_src"]);
        } elseif ($photo_details["error"] != '') {
            $this->link_dao->saveExpansionError($original_link, $photo_details["error"]);
        }
    }
}