<?php
/**
 * Retweet Detector
 * Detects retweets and original tweets
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