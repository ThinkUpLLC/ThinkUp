<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.URLProcessor.php
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
     * If link is an image (Twitpic/Twitgoo/Yfrog/Flickr for now), insert direct path to thumb as expanded url.
     * @TODO Move image thumbnail processng to Expand URLs plugin.
     * @param Logger $logger
     * @param str $tweet
     * @param Array $urls
     */
    public static function processTweetURLs($logger, $tweet, $urls = null) {
        $link_dao = DAOFactory::getDAO('LinkDAO');
        if (!$urls) {
            $urls = Post::extractURLs($tweet['post_text']);
        }
        foreach ($urls as $url) {
            $logger->logInfo("processing url: $url", __METHOD__.','.__LINE__);
            $is_image = false;
            $title = '';
            $expanded_url = '';
            if (substr($url, 0, strlen('http://twitpic.com/')) == 'http://twitpic.com/') {
                $expanded_url = 'http://twitpic.com/show/thumb/'.substr($url, strlen('http://twitpic.com/'));
                $is_image = true;
            } elseif (substr($url, 0, strlen('http://yfrog.com/')) == 'http://yfrog.com/') {
                $expanded_url = $url.'.th.jpg';
                $is_image = true;
            } elseif (substr($url, 0, strlen('http://twitgoo.com/')) == 'http://twitgoo.com/') {
                $expanded_url = 'http://twitgoo.com/show/thumb/'.substr($url, strlen('http://twitgoo.com/'));
                $is_image = true;
            } elseif (substr($url, 0, strlen('http://picplz.com/')) == 'http://picplz.com/') {
                $expanded_url = $url.'/thumb/';
                $is_image = true;
            } elseif (substr($url, 0, strlen('http://flic.kr/')) == 'http://flic.kr/') {
                $is_image = true;
            } elseif (substr($url, 0, strlen('http://instagr.am/')) == 'http://instagr.am/') {
                // see: http://instagr.am/developer/embedding/ for reference
                // the following does a redirect to the actual jpg
                // make a check for an end slash in the url -- if it is there (likely) then adding a second
                // slash prior to the 'media' string will break the expanded url
                if ($url[strlen($url)-1] == '/') {
                    $expanded_url = $url . 'media/';
                } else {
                    $expanded_url = $url . '/media/';
                }
                $logger->logDebug("expanded instagram URL to: " . $expanded_url, __METHOD__.','.__LINE__);
                $is_image = true;
            }
            $link_array = array('url'=>$url, 'expanded_url'=>$expanded_url, 'post_id'=>$tweet['post_id'],
            'network'=>'twitter', 'is_image'=>$is_image);
            $link = new Link($link_array);
            if ($link_dao->insert($link)) {
                $logger->logSuccess("Inserted ".$url." (".$expanded_url.", ".$is_image."), into links table",
                __METHOD__.','.__LINE__);
            } else {
                $logger->logError("Did NOT insert ".$url." (".$expanded_url.") into links table", __METHOD__.','.
                __LINE__);
            }
        }
    }
}
