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

        $instance_dao = DAOFactory::getDAO('ExpandURLsInstanceDAO');
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('expandurls', true);
        
        //Limit the number of links expanded each crawl
        $this->link_limit = isset($options['links_to_expand']->option_value) ?
        (int)$options['links_to_expand']->option_value : 1500;

        //Flickr image thumbnails
        if ($this->link_limit > 0) {
            if (isset($options['flickr_api_key']->option_value)) {
                self::expandFlickrThumbnails($options['flickr_api_key']->option_value);
            }
        }

        if ($this->link_limit > 0) {        
            self::expandInstagramImageURLs();
        }
        
        //Bit.ly URLs
        if ($this->link_limit > 0) {
            if (isset($options['bitly_api_key']->option_value, $options['bitly_login']->option_value, 
    		    $options['day_cap']->option_value, $options['hour_cap']->option_value, 
    			$options['bitly_limit']->option_value)) {
                self::expandBitlyLinks($options['bitly_api_key']->option_value, 
    			$options['bitly_login']->option_value, $options['day_cap']->option_value, 
    			$options['hour_cap']->option_value, $options['bitly_limit']->option_value);
           }
        }
               
        //Remaining links
        if ($this->link_limit > 0) {
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
     * Expand shortened Flickr links to image thumbnails if Flickr API key is set
     * @param $api_key Flickr API key
     */
    public function expandFlickrThumbnails($api_key) {
        $logger = Logger::getInstance();
        $link_dao = DAOFactory::getDAO('LinkDAO');
        //Flickr thumbnails
        $logger->setUsername(null);
        $fa = new FlickrAPIAccessor($api_key);

        $flickrlinkstoexpand = $link_dao->getLinksToExpandByURL('http://flic.kr/');
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
                $link_dao->saveExpandedUrl($fl, $eurl["expanded_url"], '', 1);
                $total_thumbnails = $total_thumbnails + 1;
            } elseif ($eurl["error"] != '') {
                $link_dao->saveExpansionError($fl, $eurl["error"]);
                $total_errors = $total_errors + 1;
            }
            $logger->logUserSuccess($total_thumbnails." Flickr thumbnails expanded (".$total_errors." errors)",
            __METHOD__.','.__LINE__);
        }
		$this->link_limit = $this->link_limit - $total_thumbnails;
    }
    
    /*
    *Expand Bit.ly links and recheck click count on old ones
    *
    *@param api_key bitly api key
    *@param bit_login bitly login name
    *@param day_cap num of days to go back
    *@param hour_cap time between rechecks
    *@param bitly_limit how many links to recheck
    */
    
    public function expandBitlyLinks($api_key, $bit_login, $day_cap, $hour_cap, $bitly_limit) {
        $logger = Logger::getInstance();
        $link_dao = DAOFactory::getDAO('LinkDAO');
		$instance_dao = DAOFactory::getDAO('ExpandURLsInstanceDAO');
        
        $logger->setUsername(null);
        $ba = new BitlyAPIAccessor($api_key, $bit_login, $bitly_limit);

		//Expandable array for bit.ly urls
		$bitly_urls = array('http://bit.ly/', 'http://bitly.com/', 'http://j.mp/');
        foreach ($bitly_urls as $bitly_url) {
		    $bitlylinkstoexpand = $link_dao->getLinksToExpandByURL($bitly_url);
		
            if (count($bitlylinkstoexpand) > 0) {
                $logger->logUserInfo(count($bitlylinkstoexpand). " $bitly_url" . " links to expand.", __METHOD__.','.__LINE__);
            } else {
                $logger->logUserInfo("There are no " . $bitly_url . " links to expand.", __METHOD__.','.__LINE__);
            }
        
        $total_links = 0;
        $total_errors = 0;
        foreach ($bitlylinkstoexpand as $bl) {
            $eurl = $ba->getBitlyLinkData($bl);
            if ($eurl["expanded_url"] != '') {
                $link_dao->saveExpandedUrl($bl, $eurl["expanded_url"], $eurl["title"], 0, $eurl["clicks"]);
                $total_links = $total_links + 1;
            } elseif ($eurl["error"] != '') {
                $link_dao->saveExpansionError($bl, $eurl["error"]);
                $total_errors = $total_errors + 1;
            }
        }
        $this->link_limit = $this->link_limit - $total_links;
        
        $logger->logUserSuccess($total_links. " " . $bitly_url . " links expanded (".$total_errors." errors)", 
        __METHOD__.','.__LINE__);
		$post_id = 13095328022536192;
                $limit = 15;
                $day_cap = 90;
                echo $instance_dao->id;
		   // if ($instance_dao->cursor == 0) {
		//@todo check that timeframe is ok something like: if.check==true...
		//@todo do link_limit subtraction when done with each set of expands
		//@todo a way to get the last post_id checked then pass into getLinksBy...
		    $bitlylinkstorecheck = $link_dao->getLinksByLastTouched($bitly_url, $post_id, $limit, $day_cap);
		   // }
		}
    }
        
    /**
     * Save direct link to Instagr.am images in data store.
     */
    public function expandInstagramImageURLs() {
        $logger = Logger::getInstance();
        $link_dao = DAOFactory::getDAO('LinkDAO');
        $insta_links_to_expand = $link_dao->getLinksToExpandByURL('http://instagr.am/');
        if (count($insta_links_to_expand) > 0) {
            $logger->logUserInfo(count($insta_links_to_expand)." Instagr.am links to expand.",
            __METHOD__.','.__LINE__);
        } else {
            $logger->logUserInfo("There are no Instagr.am thumbnails to expand.",  __METHOD__.','.__LINE__);
        }

        $total_thumbnails = 0;
        $total_errors = 0;
        $eurl = '';
        foreach ($insta_links_to_expand as $il) {
            $html = (string) Utils::getURLContents($il);
            if ($html) {
                preg_match('/<meta property="og:image" content="[^"]+"\/>/i', $html, $matches);
                if (isset($matches[0])) {
                    $eurl = substr($matches[0], 35, -3);
                    $logger->logDebug("Got instagr.am expanded URL: $eurl", __METHOD__.','.__LINE__);
                }
                if ($eurl != '') {
                    $link_dao->saveExpandedUrl($il, $eurl, '', 1);
                    $total_thumbnails = $total_thumbnails + 1;
                } else {
                    $total_errors = $total_errors + 1;
                }
            }
        }
		$this->link_limit = $this->link_limit - $total_thumbnails;
        
        $logger->logUserSuccess($total_thumbnails." Instagr.am thumbnails expanded (".$total_errors." errors)",
        __METHOD__.','.__LINE__);
    }

    /**
     * Expand all unexpanded URLs
     * @param $total_links_to_expand The number of links to expand
     */
    public function expandRemainingURLs() {
        $logger = Logger::getInstance();
        $link_dao = DAOFactory::getDAO('LinkDAO');
        $linkstoexpand = $link_dao->getLinksToExpand($this->link_limit);

        $logger->logUserInfo(count($linkstoexpand)." links to expand. Please wait. Working...",
        __METHOD__.','.__LINE__);

        $total_expanded = 0;
        $total_errors = 0;
        foreach ($linkstoexpand as $l) {
            if (Utils::validateURL($l)) {
                $logger->logInfo("Expanding ".($total_expanded+1). " of ".count($linkstoexpand)." (".$l.")",
                __METHOD__.','.__LINE__);

                $eurl = self::untinyurl($l, $link_dao);
                if ($eurl != '') {
                    $link_dao->saveExpandedUrl($l, $eurl);
                    $total_expanded = $total_expanded + 1;
                } else {
                    $total_errors = $total_errors + 1;
                }
            } else {
                $total_errors = $total_errors + 1;
                $logger->logError($l." is not a valid URL; skipping expansion", __METHOD__.','.__LINE__);
            }
        }
		$this->link_limit = $this->link_limit - $total_expanded;
        
        $logger->logUserSuccess($total_expanded." URLs successfully expanded (".$total_errors." errors).",
        __METHOD__.','.__LINE__);
    }

    /**
     * Expand a given short URL
     *
     * @param str $tinyurl Shortened URL
     * @param LinkDAO $ldao
     * @return str Expanded URL
     */
    private function untinyurl($tinyurl, $ldao) {
        $logger = Logger::getInstance();
        $url = parse_url($tinyurl);
        $host = $url['host'];
        $port = isset($url['port']) ? $url['port'] : 80;
        $query = isset($url['query']) ? '?'.$url['query'] : '';
        $fragment = isset($url['fragment']) ? '#'.$url['fragment'] : '';
        if (empty($url['path'])) {
            $logger->logError("$tinyurl has no path", __METHOD__.','.__LINE__);
            $ldao->saveExpansionError($tinyurl, "Error expanding URL");
            return '';
        } else {
            $path = $url['path'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, "http://$host:$port".$path.$query.$fragment);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // seconds
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $response = curl_exec($ch);
        if ($response === false) {
            $logger->logError("cURL error: ".curl_error($ch), __METHOD__.','.__LINE__);
            $ldao->saveExpansionError($tinyurl, "Error expanding URL");
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
            $logger->logError("Short URL returned '404 Not Found'", __METHOD__.','.__LINE__);
            $ldao->saveExpansionError($tinyurl, "Error expanding URL");
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
