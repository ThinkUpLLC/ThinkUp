<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.URLProcessor.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Amy Unruh
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
            foreach ($urls as $url) {
                $logger->logInfo("Processing URL: $url", __METHOD__.','.__LINE__);
                $image_src = self::getImageSource($url);

                //if we have an image_src, the URL is a known image source not in need of expansion
                $expanded_url = isset($image_src)?$url:'';
                $link_array = array('url'=>$url, 'expanded_url'=>$expanded_url, "image_src"=>$image_src,
                'post_id'=>$post_id, 'network'=>$network);
                $link = new Link($link_array);
                if ($link_dao->insert($link)) {
                    $logger->logSuccess("Inserted ".$url." (thumbnail ".$image_src."), into links table",
                    __METHOD__.','.__LINE__);
                } else {
                    $logger->logError("Did NOT insert ".$url." (thumbnail ".$image_src.") into links table",
                    __METHOD__.','.__LINE__);
                }
            }
        }
    }

    /**
     * Get a direct link to an image thumbnail for a given URL if it exists. Currently supports Twitpic, Twitgoo,
     * Picplz, Yfrog, and Instagr.am.
     * @param str $url
     * @return str $image_src
     */
    public static function getImageSource($url) {
        $image_src = null;
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
        }
        return $image_src;
    }
}
