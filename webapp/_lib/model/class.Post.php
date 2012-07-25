<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Post.php
 *
 * Copyright (c) 2009-2012 Gina Trapani
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
 * @copyright 2009-2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class Post {
    /**
     * @const int
     * Twitter currently maxes out on returning a RT count at 100.
     */
    const TWITTER_RT_THRESHOLD = 100;
    /**
     * @var int Internal unique ID..
     */
    var $id;
    /**
     * @var int The ID of the post inside the respective service.
     */
    var $post_id;
    /**
     * @var int The user ID inside the respective service, e.g. Twitter or Facebook user IDs.
     */
    var $author_user_id;
    /**
     * @var str The user's username inside the respective service, e.g. Twitter or Facebook user name.
     */
    var $author_username;
    /**
     * @var str The user's real, full name on a given service, e.g. Gina Trapani.
     */
    var $author_fullname;
    /**
     * @var str The URL to the user's avatar for a given service.
     */
    var $author_avatar;
    /**
     * @var int Post author's follower count. [Twitter-specific]
     */
    var $author_follower_count;
    /**
     * @var str The textual content of a user's post on a given service.
     */
    var $post_text;
    /**
     * @var bool Whether or not this post is protected, e.g. not publicly visible.
     */
    var $is_protected;
    /**
     * @var str The client used to publish this post, e.g. if you post from the Twitter web interface, this will be
     * "web".
     */
    var $source;
    /**
     * @var str Author-level location, e.g., the author's location as set in his or her profile. Use author-level
     location if post-level location is not set.
     */
    var $location;
    /**
     * @var str Post-level name of a place from which a post was published, ie, Woodland Hills, Los Angeles.
     */
    var $place;
    /**
     * @var str Post-level place ID on a given network.
     */
    var $place_id;
    /**
     * @var str The post's latitude and longitude coordinates.
     */
    var $geo;
    /**
     * @var str The UTC date/time when this post was published.
     */
    var $pub_date;
    /**
     * @var int The ID of the user that this post is in reply to.
     */
    var $in_reply_to_user_id;
    /**
     * @var int The ID of the post that this post is in reply to.
     */
    var $in_reply_to_post_id;
    /**
     * @var int The total number of replies this post received in the data store.
     */
    var $reply_count_cache;
    /**
     * @var tinyint Whether or not this reply was authored by a friend of the original post's author.
     */
    var $is_reply_by_friend;
    /**
     * @var int The ID of the post that this post is a retweet of. [Twitter-specific]
     */
    var $in_retweet_of_post_id;
    /**
     * @var int Manual count of old-style retweets as determined by ThinkUp. [Twitter-specific]
     */
    var $old_retweet_count_cache;
    /**
     * @var tinyint Whether or not this retweet was posted by a friend of the original post's author. [Twitter-specific]
     */
    var $is_retweet_by_friend;
    /**
     * @var int The distance (in km) away from the post that this post is in reply or retweet of [Twitter-specific-ish]
     */
    var $reply_retweet_distance;
    /**
     * @var str The network that this post belongs to in lower-case, e.g. twitter or facebook
     */
    var $network;
    /**
     * @var int Whether or not this post has been geo-encoded.
     * @TODO Give these constants meaningful names
     * 0 if Not Geoencoded, 1 if Successful, 2 if ZERO_RESULTS,
     * 3 if OVER_QUERY_LIMIT, 4 if REQUEST_DENIED, 5 if INVALID_REQUEST, 6 if INSUFFICIENT_DATA
     */
    var $is_geo_encoded = false;
    /**
     * @var int The ID of the user that this post is retweeting. [Twitter-specific]
     */
    var $in_rt_of_user_id;
    /**
     * @var int Manual count of native retweets as determined by ThinkUp. [Twitter-specific]
     */
    var $retweet_count_cache;
    /**
     * @var int The total number of native retweets as reported by Twitter API. [Twitter-specific]
     */
    var $retweet_count_api;
    /**
     * @var int The total number of favorites or likes this post received.
     */
    var $favlike_count_cache;
    /**
     *
     * @var User $author Optionally set
     */
    var $author;
    /**
     *
     * @var Array of Links $links Optionally set
     */
    var $links = array();
    /**
     * @var str Non-persistent, 'true' or 'false'
     */
    var $favorited;
    /**
     * @var int Non-persistent, used for UI
     */
    var $all_retweets;
    /**
     * @var int Non-persistent, used for UI, indicates whether Twitter RT count threshold was reached.
     */
    var $rt_threshold;
    /**
     * Constructor
     * @param array $val Array of key/value pairs
     * @return Post
     */
    public function __construct($val) {
        // a fix for getPost() where the join of the links table column links.id overides the posts.id
        if (isset($val["post_key"])) {
            $this->id = $val["post_key"];
        } else {
            $this->id = $val["id"];
        }
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
        $this->favlike_count_cache = $val["favlike_count_cache"];

        // favorited is non-persistent.  Will be set from xml, but not from database retrieval.
        if (isset($val["favorited"])) {
            $this->favorited = $val["favorited"];
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
     * Add link to links array.
     * @param Link $link
     */
    public function addLink(Link $link) {
        $this->links[] = $link;
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
        '(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’,�]))';
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
        if (!class_exists('Twitter_Extractor')) {
            Loader::addSpecialClass('Twitter_Extractor',
            'plugins/twitter/extlib/twitter-text-php/lib/Twitter/Extractor.php');
        }
        $tweet = new Twitter_Extractor($post_text);
        $mentions = $tweet->extractMentionedUsernames();
        foreach ($mentions as $k => $v) {
            $mentions[$k] = '@' . $v;
        }
        return $mentions;
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
