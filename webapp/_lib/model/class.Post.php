<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Post.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * Post
 * A post, tweet, or status update on a ThinkUp source network or service (like Twitter or Facebook)
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class Post {
    
    /**
     * @const int
     * twitter currently maxes out on returning a RT count at 100.
     */
    const TWITTER_RT_THRESHOLD = 100;
    
    /**
     *
     * @var int
     */
    var $id;
    /**
     *
     * @var int
     */
    var $post_id;
    /**
     *
     * @var int
     */
    var $author_user_id;
    /**
     *
     * @var str
     */
    var $author_fullname;
    /**
     *
     * @var str
     */
    var $author_username;
    /**
     *
     * @var str
     */
    var $author_avatar;
    /**
     *
     * @var str
     */
    var $post_text;
    /**
     * @var bool
     */
    var $is_protected;
    /**
     *
     * @var str
     */
    var $source;
    /**
     *
     * @var str
     */
    var $location;
    /**
     *
     * @var str
     */
    var $place;
    /**
     *
     * @var str
     */
    var $geo;
    /**
     *
     * @var str
     */
    var $pub_date;
    /**
     *
     * @var str
     */
    var $adj_pub_date;
    /**
     *
     * @var int
     */
    var $in_reply_to_user_id;
    /**
     *
     * @var bool
     */
    var $is_reply_by_friend;
    /**
     *
     * @var int
     */
    var $in_reply_to_post_id;
    /**
     *
     * @var int
     */
    var $reply_count_cache;
    /**
     *
     * @var int
     */
    var $in_retweet_of_post_id;
    /**
     * @var int
     */
    var $in_rt_of_user_id;
    /**
     *
     * @var int
     */
    var $retweet_count_cache;
    /**
     * @var int
     */
    var $old_retweet_count_cache;
    /**
     *
     * @var int
     */
    var $reply_retweet_distance;
    /**
     *
     * @var bool
     */
    var $is_retweet_by_friend;
    /**
     * @var str 'true' or 'false'
     */
    var $favorited;
    /**
     *
     * @var str
     */
    var $network;
    /**
     * @TODO Give these constants meaningful names
     * @var int 0 if Not Geoencoded, 1 if Successful, 2 if ZERO_RESULTS,
     * 3 if OVER_QUERY_LIMIT, 4 if REQUEST_DENIED, 5 if INVALID_REQUEST, 6 if INSUFFICIENT_DATA
     */
    var $is_geo_encoded;
    /**
     *
     * @var User $author Optionally set
     */
    var $author;
    /**
     *
     * @var Link $link Optionally set
     */
    var $link;
    /**
     * @var int, non-persistent, used for UI
     */
    var $all_retweets;
    
    /**
     * @var int, non-persistent, used for UI, indicates whether twitter rt count threshold was reached.
     */
    var $rt_threshold;

    /**
     * Constructor
     * @param array $val Array of key/value pairs
     * @return Post
     */
    public function __construct($val) {
        $this->id = $val["id"];
        $this->post_id = $val["post_id"];
        $this->author_user_id = $val["author_user_id"];
        $this->author_username = $val["author_username"];
        $this->author_fullname = $val["author_fullname"];
        $this->author_avatar = $val["author_avatar"];
        $this->post_text = $val["post_text"];
        $this->is_protected = PDODAO::convertDBToBool($val["is_protected"]);
        $this->source = $val["source"];
        $this->location = $val["location"];
        $this->place = $val["place"];
        $this->geo = $val["geo"];
        $this->pub_date = $val["pub_date"];
        $this->adj_pub_date = $val["adj_pub_date"];
        $this->in_reply_to_user_id = $val["in_reply_to_user_id"];
        $this->in_reply_to_post_id = $val["in_reply_to_post_id"];
        $this->reply_count_cache = $val["reply_count_cache"];
        $this->in_retweet_of_post_id = $val["in_retweet_of_post_id"];
        $this->in_rt_of_user_id = $val["in_rt_of_user_id"];
        $this->retweet_count_cache = $val["retweet_count_cache"];
        $this->old_retweet_count_cache = $val["old_retweet_count_cache"];
        $this->reply_retweet_distance = $val["reply_retweet_distance"];
        $this->is_geo_encoded = $val["is_geo_encoded"];
        $this->network = $val["network"];
        $this->is_reply_by_friend = PDODAO::convertDBToBool($val["is_reply_by_friend"]);
        $this->is_retweet_by_friend = PDODAO::convertDBToBool($val["is_retweet_by_friend"]);

        if (isset($val['is_protected'])) {
            $this->is_protected = PDODAO::convertDBToBool($val["is_protected"]);
        }

        // favorited is non-persistent.  Will be set from xml, but not from database retrieval.
        if (isset($val["favorited"])) {
            $this->favorited = $val["favorited"];
        }
        // non-persistent, sum of two persistent values, used for UI information display
        $this->all_retweets = $val['old_retweet_count_cache'] + $val['retweet_count_cache'];
        if ($val['retweet_count_cache'] >= self::TWITTER_RT_THRESHOLD) {
            // if the new RT count, obtained from twitter, has maxed out, set a non-persistent flag field 
            // to indicate this. The templates will make use of this info to add a '+' after the sum if the 
            // flag is set.
            $this->rt_threshold = 1;
        }
        else {
            $this->rt_threshold = 0;
        }
    }

    /**
     * Extract URLs from post text
     * @param string $post_text
     * @return array $matches
     */
    public static function extractURLs($post_text) {
        preg_match_all('!https?://[\w][\S]+!', $post_text, $matches);
        return $matches[0];
    }
}
