<?php
/**
 *
 * ThinkUp/webapp/_lib/class.URLProcessor.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Amy Unruh
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @author Amy Unruh
 *
 * URL Processor
 * Does post-processing on the URLs contained within a tweet.
 */
class URLProcessor {
    /**
     * For a given post, extract URLs and store them, including image_src if that's from a known source like Twitpic,
     * Twitgoo, Yfrog, Instagr.am.
     * @param str $post_text
     * @param int $post_id
     * @param str $network
     * @param Logger $logger
     * @param arr $urls Array of URLs, optionally set, defaults to null
     */
    public static function processPostURLs($post_text, $post_id, $network, $logger, $urls=null) {
        if (!$urls) {
            $urls = Post::extractURLs($post_text);
        }
        if ($urls) {
            $link_dao = DAOFactory::getDAO('LinkDAO');
            $post_dao = DAOFactory::getDAO('PostDAO');
            $post = $post_dao->getPost($post_id, $network);
            if (isset($post->id)) {
                foreach ($urls as $url) {
                    $logger->logInfo("Processing URL $url", __METHOD__.','.__LINE__);
                    $image_src = self::getImageSource($url);

                    //if we have an image_src, the URL is a known image source not in need of expansion
                    $expanded_url = ($image_src!=='')?$url:'';
                    $link_array = array('url'=>$url, 'expanded_url'=>$expanded_url, "image_src"=>$image_src,
                    'post_key'=>$post->id);
                    $link = new Link($link_array);
                    if ($link_dao->insert($link)) {
                        $logger->logSuccess("Inserted ".$url." ".(($image_src=='')?'':"(thumbnail ".$image_src.") ").
                        "into links table", __METHOD__.','.__LINE__);
                    } else {
                        $logger->logInfo($url." ".(($image_src=='')?'':"(thumbnail ".$image_src.") ").
                        "already exists in links table", __METHOD__.','.__LINE__);
                    }
                }
            }
        }
    }

    /**
     * Get a direct link to an image thumbnail for a given URL if it exists. Currently supports Twitpic, Twitgoo,
     * Picplz, Yfrog, Instagr.am and Lockerz.
     * @param str $url
     * @return str $image_src
     */
    public static function getImageSource($url) {
        $image_src = '';
        if (substr($url, 0, strlen('http://twitpic.com/')) == 'http://twitpic.com/') {
            $image_src = 'http://twitpic.com/show/thumb/'.substr($url, strlen('http://twitpic.com/'));
        } elseif (substr($url, 0, strlen('http://yfrog.com/')) == 'http://yfrog.com/') {
            $image_src = $url.'.th.jpg';
        } elseif (substr($url, 0, strlen('http://twitgoo.com/')) == 'http://twitgoo.com/') {
            $image_src = 'http://twitgoo.com/show/thumb/'.substr($url, strlen('http://twitgoo.com/'));
        } elseif (substr($url, 0, strlen('http://picplz.com/')) == 'http://picplz.com/') {
            $image_src = $url.'/thumb/';
        } elseif (substr($url, 0, strlen('http://instagr.am/')) == 'http://instagr.am/') {
            // see: http://instagr.am/developer/embedding/ for reference
            // the following does a redirect to the actual jpg
            // make a check for an end slash in the url -- if it is there (likely) then adding a second
            // slash prior to the 'media' string will break the expanded url
            if ($url[strlen($url)-1] == '/') {
                $image_src = $url . 'media/';
            } else {
                $image_src = $url . '/media/';
            }
        } elseif (substr($url, 0, strlen('http://lockerz.com/')) == 'http://lockerz.com/') {
            $url = str_replace('lockerz.com/s/', 'plixi.com/p/', $url);
            $image_src = 'http://api.plixi.com/api/tpapi.svc/imagefromurl?url='.$url.'&size=thumbnail';
        }
        return $image_src;
    }
    /**
     * Get final URL if there's a single 302 redirect.
     * @param str $url
     * @param bool $verify_ssl_cert Defaults to true
     * @return str Final URL
     * @throws Exception if there's a cURL error
     */
    public static function getFinalURL($url, $verify_ssl_cert=true) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // seconds
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        if (!$verify_ssl_cert) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception ("cURL error ".curl_errno($ch) ." fetching ".$url." - ".curl_error($ch));
        }
        curl_close($ch);

        $lines = explode("\r\n", $response);
        foreach ($lines as $line) {
            if (stripos($line, 'Location:') === 0) {
                list(, $location) = explode(':', $line, 2);
                return ltrim($location);
            }
        }
        return $url;
    }
}
