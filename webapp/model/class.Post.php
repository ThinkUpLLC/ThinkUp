<?php
/**
 * Post
 * A post, tweet, or status update on a ThinkTank source network or service (like Twitter or Facebook)
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class Post {
    var $id;
    var $post_id;
    var $author_user_id;
    var $author_fullname;
    var $author_username;
    var $author_avatar;
    var $post_text;
    var $source;
    var $location;
    var $place;
    var $geo;
    var $pub_date;
    var $adj_pub_date;
    var $in_reply_to_user_id;
    var $in_reply_to_post_id;
    var $mention_count_cache;
    var $in_retweet_of_post_id;
    var $retweet_count_cache;
    var $network;

    var $author; //optional user object
    var $link; //optional link object

    function Post($val) {
        $this->id = $val["id"];
        $this->post_id = $val["post_id"];
        $this->author_user_id = $val["author_user_id"];
        $this->author_username = $val["author_username"];
        $this->author_fullname = $val["author_fullname"];
        $this->author_avatar = $val["author_avatar"];
        $this->post_text = $val["post_text"];
        $this->source = $val["source"];
        $this->location = $val["location"];
        $this->place = $val["place"];
        $this->geo = $val["geo"];
        $this->pub_date = $val["pub_date"];
        $this->adj_pub_date = $val["adj_pub_date"];
        $this->in_reply_to_user_id = $val["in_reply_to_user_id"];
        $this->in_reply_to_post_id = $val["in_reply_to_post_id"];
        $this->mention_count_cache = $val["mention_count_cache"];
        $this->in_retweet_of_post_id = $val["in_retweet_of_post_id"];
        $this->retweet_count_cache = $val["retweet_count_cache"];
        $this->network = $val["network"];
    }

    public static function extractURLs($post_text) {
        preg_match_all('!https?://[\S]+!', $post_text, $matches);
        return $matches[0];
    }
}