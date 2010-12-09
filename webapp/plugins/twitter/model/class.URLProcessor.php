<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.URLProcessor.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Amy Unruh
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
 * URL-processing class, processTweetURLs method pulled out of TwitterCrawler into its own class.
 */
class URLProcessor {

    public function __construct() {
    }

    /**
     * If link is an image (Twitpic/Twitgoo/Yfrog/Flickr for now), insert direct path to thumb as expanded url.
     * @TODO (from TwitterCrawler version): Abstract out this image thumbnail link expansion into a plugin 
     * modeled after the Flickr Thumbnails plugin
     * @param Logger $logger 
     * @param str $tweet
     * @param Array $urls
     */
    public static function processTweetURLs($logger, $tweet, $urls = null) {

        $logger = Logger::getInstance('slog_location');
        // $logger->logInfo("in processTweetURLs for " . $tweet['post_id'], __METHOD__.','.__LINE__);
        
        $ld = DAOFactory::getDAO('LinkDAO');
        if (!$urls) {
            $urls = Post::extractURLs($tweet['post_text']);
        }
        foreach ($urls as $u) {
            // $logger->logInfo("processing url: $u", __METHOD__.','.__LINE__);
            $is_image = 0;
            $title = '';
            $eurl = '';
            if (substr($u, 0, strlen('http://twitpic.com/')) == 'http://twitpic.com/') {
                $eurl = 'http://twitpic.com/show/thumb/'.substr($u, strlen('http://twitpic.com/'));
                $is_image = 1;
            } elseif (substr($u, 0, strlen('http://yfrog.com/')) == 'http://yfrog.com/') {
                $eurl = $u.'.th.jpg';
                $is_image = 1;
            } elseif (substr($u, 0, strlen('http://twitgoo.com/')) == 'http://twitgoo.com/') {
                $eurl = 'http://twitgoo.com/show/thumb/'.substr($u, strlen('http://twitgoo.com/'));
                $is_image = 1;
            } elseif (substr($u, 0, strlen('http://flic.kr/')) == 'http://flic.kr/') {
                $is_image = 1;
            } 
            if ($ld->insert($u, $eurl, $title, $tweet['post_id'], 'twitter', $is_image)) {
                $logger->logSuccess("Inserted ".$u." (".$eurl.", ".$is_image."), into links table",
                __METHOD__.','.__LINE__);
            } else {
                $logger->logError("Did NOT insert ".$u." (".$eurl.") into links table", __METHOD__.','.__LINE__);
            }
        }
    }

}
