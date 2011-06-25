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
 * @copyright 2009-2011 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class Post {
    /**
     * @const int
     * Twitter currently maxes out on returning a RT count at 100.
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
     * @var int
     */
    var $place_id;
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
     * The retweet count from the database
     */
    var $retweet_count_cache;
    /**
     * @var int
     * the retweet count reported from twitter.com
     */
    var $retweet_count_api;
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
     *
     * @var int $favd_count Optionally set
     */
    var $favd_count;
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
        $this->place_id = $val["place_id"];
        $this->geo = $val["geo"];
        $this->pub_date = $val["pub_date"];
        $this->adj_pub_date = $val["adj_pub_date"];
        $this->in_reply_to_user_id = $val["in_reply_to_user_id"];
        $this->in_reply_to_post_id = $val["in_reply_to_post_id"];
        $this->reply_count_cache = $val["reply_count_cache"];
        $this->in_retweet_of_post_id = $val["in_retweet_of_post_id"];
        $this->in_rt_of_user_id = $val["in_rt_of_user_id"];
        $this->retweet_count_cache = $val["retweet_count_cache"];
        $this->retweet_count_api = $val["retweet_count_api"];
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
        if (isset($val['favd_count'])) {
            $this->favd_count = $val['favd_count'];
        }

        // For the retweet count display, we will use the larger of retweet_count_cache and retweet_count_api,
        // and add it to old_retweet_count_cache.
        $largest_native_RT_count = $val['retweet_count_cache'];
        $this->rt_threshold = 0;
        // if twitter's reported count is larger, use that
        if ($val['retweet_count_api'] > $val['retweet_count_cache']) {
            $largest_native_RT_count = $val['retweet_count_api'];
            if ($largest_native_RT_count >= self::TWITTER_RT_THRESHOLD ) {
                // if the new RT count, obtained from twitter, has maxed out, set a non-persistent flag field
                // to indicate this. The templates will make use of this info to add a '+' after the sum if the
                // flag is set.
                $this->rt_threshold = 1;
            }
        }

        // non-persistent, used for UI information display
        $this->all_retweets = $val['old_retweet_count_cache'] + $largest_native_RT_count;
    }

    /**
     * Extract URLs from post text.
     * Find syntactically correct URLs such as http://foobar.com/data.php and some plausible URL fragments, e.g.
     * bit.ly/asb12 www.google.com, and fix URL fragments to be valid URLs.
     * Only return valid URLs
     * Regex pattern based on http://daringfireball.net/2010/07/improved_regex_for_matching_urls
     * with a modification in the third group to ensure that https?:/// (third slash) doesn't match.
     * @param string $post_text
     * @return array $matches
     */
    public static function extractURLs($post_text) {
        $url_pattern = '(?i)\b'.
        '((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)'. 
        '(?:[^\s()<>/][^\s()<>]*|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+'.
        '(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))';
        preg_match_all('#'.$url_pattern.'#', $post_text, $matches);
        $corrected_urls = array_map( 'Link::addMissingHttp', $matches[0]);
        return array_filter($corrected_urls,'Utils::validateURL');
    }

    /**
     * Extracts mentions from a Tweet.
     *
     * @param str $post_text The post text to search.
     * @return array $matches All mentions in this tweet.
     */
    public static function extractMentions($post_text) {
        preg_match_all('/(^|[^a-z0-9_])@([a-z0-9_]+)/i', $post_text, $matches);

        // sometimes there's leading or trailing whitespace on the match, trim it
        foreach ($matches[0] as $key=>$match) {
            $matches[0][$key] = trim($match, ' ');
        }

        return $matches[0];
    }

    /**
     * Extracts hashtags from a Tweet.
     *
     * @param str $post_text The post text to search.
     * @return array $matches All hashtags in this tweet.
     */
    public static function extractHashtags($post_text) {
        preg_match_all('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', $post_text, $matches);

        // sometimes there's leading or trailing whitespace on the match, trim it
        foreach ($matches[0] as $key=>$match) {
            $matches[0][$key] = trim($match, ' ');
        }

        return $matches[0];
    }
}
