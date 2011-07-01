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
        foreach ($urls as $u) {
            $logger->logInfo("processing url: $u", __METHOD__.','.__LINE__);
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
            } elseif (substr($u, 0, strlen('http://picplz.com/')) == 'http://picplz.com/') {
                $eurl = $u.'/thumb/';
                $is_image = 1;
            } elseif (substr($u, 0, strlen('http://flic.kr/')) == 'http://flic.kr/') {
                $is_image = 1;
            } elseif (substr($u, 0, strlen('http://instagr.am/')) == 'http://instagr.am/') {
                // see: http://instagr.am/developer/embedding/ for reference
                // the following does a redirect to the actual jpg
                // make a check for an end slash in the url -- if it is there (likely) then adding a second
                // slash prior to the 'media' string will break the expanded url
                if ($u[strlen($u)-1] == '/') {
                    $eurl = $u . 'media/';
                } else {
                    $eurl = $u . '/media/';
                }
                $logger->logDebug("expanded instagram URL to: " . $eurl, __METHOD__.','.__LINE__);
                $is_image = 1;
            }
            if ($link_dao->insert($u, $eurl, $title, $tweet['post_id'], 'twitter', $is_image)) {
                $logger->logSuccess("Inserted ".$u." (".$eurl.", ".$is_image."), into links table",
                __METHOD__.','.__LINE__);
            } else {
                $logger->logError("Did NOT insert ".$u." (".$eurl.") into links table", __METHOD__.','.__LINE__);
            }
        }
    }
}
