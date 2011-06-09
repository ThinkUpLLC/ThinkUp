Post
====

ThinkUp/webapp/_lib/model/class.Post.php

Copyright (c) 2009-2011 Gina Trapani

Post
A post, tweet, or status update on a ThinkUp source network or service (like Twitter or Facebook)


Properties
----------

id
~~



post_id
~~~~~~~



author_user_id
~~~~~~~~~~~~~~



author_fullname
~~~~~~~~~~~~~~~



author_username
~~~~~~~~~~~~~~~



author_avatar
~~~~~~~~~~~~~



post_text
~~~~~~~~~



is_protected
~~~~~~~~~~~~



source
~~~~~~



location
~~~~~~~~



place
~~~~~



geo
~~~



pub_date
~~~~~~~~



adj_pub_date
~~~~~~~~~~~~



in_reply_to_user_id
~~~~~~~~~~~~~~~~~~~



is_reply_by_friend
~~~~~~~~~~~~~~~~~~



in_reply_to_post_id
~~~~~~~~~~~~~~~~~~~



reply_count_cache
~~~~~~~~~~~~~~~~~



in_retweet_of_post_id
~~~~~~~~~~~~~~~~~~~~~



in_rt_of_user_id
~~~~~~~~~~~~~~~~



retweet_count_cache
~~~~~~~~~~~~~~~~~~~



retweet_count_api
~~~~~~~~~~~~~~~~~



old_retweet_count_cache
~~~~~~~~~~~~~~~~~~~~~~~



reply_retweet_distance
~~~~~~~~~~~~~~~~~~~~~~



is_retweet_by_friend
~~~~~~~~~~~~~~~~~~~~



favorited
~~~~~~~~~



network
~~~~~~~



is_geo_encoded
~~~~~~~~~~~~~~

@TODO Give these constants meaningful names

author
~~~~~~



link
~~~~



all_retweets
~~~~~~~~~~~~



rt_threshold
~~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~
* **@param** array $val Array of key/value pairs
* **@return** Post


Constructor

.. code-block:: php5

    <?php
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


extractURLs
~~~~~~~~~~~
* **@param** string $post_text
* **@return** array $matches


Extract URLs from post text.
Find syntactically correct URLs such as http://foobar.com/data.php and some plausible URL fragments, e.g.
bit.ly/asb12 www.google.com, and fix URL fragments to be valid URLs.
Only return valid URLs
Regex pattern based on http://daringfireball.net/2010/07/improved_regex_for_matching_urls
with a modification in the third group to ensure that https?:/// (third slash) doesn't match.

.. code-block:: php5

    <?php
        public static function extractURLs($post_text) {
            $url_pattern = '(?i)\b'.
            '((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)'. 
            '(?:[^\s()<>/][^\s()<>]*|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+'.
            '(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))';
            preg_match_all('#'.$url_pattern.'#', $post_text, $matches);
            $corrected_urls = array_map( 'Link::addMissingHttp', $matches[0]);
            return array_filter($corrected_urls,'Utils::validateURL');
        }


extractMentions
~~~~~~~~~~~~~~~
* **@param** str $post_text The post text to search.
* **@return** array $matches All mentions in this tweet.


Extracts mentions from a Tweet.

.. code-block:: php5

    <?php
        public static function extractMentions($post_text) {
            preg_match_all('/(^|[^a-z0-9_])@([a-z0-9_]+)/i', $post_text, $matches);
    
            // sometimes there's leading or trailing whitespace on the match, trim it
            foreach ($matches[0] as $key=>$match) {
                $matches[0][$key] = trim($match, ' ');
            }
    
            return $matches[0];
        }


extractHashtags
~~~~~~~~~~~~~~~
* **@param** str $post_text The post text to search.
* **@return** array $matches All hashtags in this tweet.


Extracts hashtags from a Tweet.

.. code-block:: php5

    <?php
        public static function extractHashtags($post_text) {
            preg_match_all('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', $post_text, $matches);
    
            // sometimes there's leading or trailing whitespace on the match, trim it
            foreach ($matches[0] as $key=>$match) {
                $matches[0][$key] = trim($match, ' ');
            }
    
            return $matches[0];
        }




