<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.RetweetDetector.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * Retweet Detector
 * Detects retweets and original tweets
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class RetweetDetector {
    public function __construct() {
    }

    /**
     * Determines if $post is a retweet of the $ownerName
     * @param string $post
     * @param string $ownerName
     * @return boolean
     */
    public static function isRetweet($post, $ownerName) {
        if (strpos(strtolower($post), strtolower("RT @".$ownerName)) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Determines the original Post ID of a retweet
     * @param string $retweet_text text of the retweet
     * @param array $recentPosts array of possible posts that retweet_text may be a retweet of
     * @return int original post ID
     */
    public static function detectOriginalTweet($retweet_text, $recentPosts) {
        $originalPostId = false;
        foreach ($recentPosts as $t) {
            if ( self::isRetweetOfTweet($retweet_text, $t->post_text) ) {
                $originalPostId = $t->post_id;
            }
        }
        return $originalPostId;
    }

    /**
     * Determines if $retweet_text is a retweet of $post_text
     * @param string $retweet_text
     * @param string $tweet_text
     * @returns boolean
     */
    public static function isRetweetOfTweet($retweet_text, $tweet_text) {
        $snip = substr($tweet_text, 0, 24);
        if (strpos($retweet_text, $snip) != false) {
            return true;
        } else {
            return false;
        }
         
    }
}