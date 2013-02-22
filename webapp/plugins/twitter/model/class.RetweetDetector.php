<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.RetweetDetector.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 */
/**
 * Retweet Detector
 * Detects retweets and original tweets
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class RetweetDetector {

    /**
     * Determines if $tweet is a retweet of a tweet by the user $owner_name. Supports 3 styles of retweet:
     * 1. The standard RT @owner
     * 2. The "modified tweet", ie, MT @owner
     * 3. The Quote Tweet style in Twitter for Mac & iPad, ie, "@owner says this"
     * @param string $tweet
     * @param string $owner_name
     * @return bool
     */
    public static function isRetweet($tweet, $owner_name) {
        if (self::isStandardRetweet($tweet, $owner_name) || self::isMTRetweet($tweet, $owner_name)
        || self::isQuotedRetweet($tweet, $owner_name)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * PUBLIC FOR TESTING ONLY. DO NOT DIRECTLY ACCESS THIS FUNCTION IN APPLICATION CODE.
     * Determines if $tweet is a retweet of a tweet by the user $owner_name in the format RT @ owner_name
     * @param string $tweet
     * @param string $owner_name
     * @return bool
     */
    public static function isStandardRetweet($tweet, $owner_name) {
        if (strpos(strtolower($tweet), strtolower("RT @".$owner_name)) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * PUBLIC FOR TESTING ONLY. DO NOT DIRECTLY ACCESS THIS FUNCTION IN APPLICATION CODE.
     * Determines if $tweet is a retweet of a tweet by the user $owner_name in the format MT @ owner_name
     * @param string $tweet
     * @param string $owner_name
     * @return boolean
     */
    public static function isMTRetweet($tweet, $owner_name) {
        if (strpos(strtolower($tweet), strtolower("MT @".$owner_name)) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * PUBLIC FOR TESTING ONLY. DO NOT DIRECTLY ACCESS THIS FUNCTION IN APPLICATION CODE.
     * Determines if $tweet is a retweet of a tweet by the user $owner_name in the format "@owner_name: ... "
     * @param string $tweet
     * @param string $owner_name
     * @return boolean
     */
    public static function isQuotedRetweet($tweet, $owner_name) {
        $lower_tweet = strtolower($tweet);
        $lower_name = strtolower($owner_name);
        // Subtract 3 from the length as strlen counts each smart quote as 3 characters
        $length = strlen($tweet)-3;

        // Check the tweet starts with “@owner_name and ends with ”
        if (strpos($lower_tweet, '“@'.$lower_name.':') !== false && strripos($lower_tweet, '”') == $length ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determines the original Post ID of a retweet
     * @param string $retweet_text text of the retweet
     * @param array $recent_posts array of possible posts that retweet_text may be a retweet of
     * @return int original post ID
     */
    public static function detectOriginalTweet($retweet_text, $recent_posts) {
        $original_post_id = false;
        foreach ($recent_posts as $t) {
            if ( self::isRetweetOfTweet($retweet_text, $t->post_text) ) {
                $original_post_id = $t->post_id;
            }
        }
        return $original_post_id;
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