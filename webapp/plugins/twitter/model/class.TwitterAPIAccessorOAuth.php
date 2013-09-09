<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterAPIAccessorOAuth.php
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
 *
 *
 * Twitter API Accessor
 * Accesses the Twitter.com API via OAuth authentication.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TwitterAPIAccessorOAuth {
    /**
     * @var arr Of TwitterAPIEndpoints
     */
    var $endpoints;
    /**
     * @var TwitterOAuthThinkUp
     */
    var $to;
    /**
     * @var str
     */
    var $oauth_access_token;
    /**
     * @var str
     */
    var $oauth_access_token_secret;
    /**
     * @var str
     */
    var $next_cursor;
    /**
     * Default value is 3, the fallback default. However this value should be set in plugin config.
     * @var int defaults to 3
     */
    var $total_errors_to_tolerate = 3;
    /**
     * Tally of the API errors returned during a given run
     * When this number equals or exceeds the $total_errors_to_tolerate, the crawling stops
     * @var int
     */
    var $total_errors_so_far = 0;
    /***
     * @var bool Whether or not to log messages
     */
    var $log;
    /**
     * A list of Twitter API error codes and their explanations as per
     * http://dev.twitter.com/pages/responses_errors
     * @var array
     */
    private $error_codes = array(
         '304' => 'There was no new data to return.',
         '400' => 'The request was invalid.',
         '401' => 'Authentication credentials were missing or incorrect.',
         '403' => 'The request is understood, but it has been refused.',
         '404' => 'The URI requested is invalid or the resource requested, such as a user, does not exist.',
         '406' => 'Invalid format specified in the request.',
         '420' => 'You are being rate limited.',
         '500' => 'Something is broken on Twitter\'s end.',
         '502' => 'Twitter is down or being upgraded.',
         '503' => 'The Twitter servers are up, but overloaded with requests. Try again later.' );
    /**
     * Constructor
     * @param str $oauth_access_token
     * @param str $oauth_access_token_secret
     * @param str $oauth_consumer_key
     * @param str $oauth_consumer_secret
     * @param int $num_twitter_errors
     * @param bool $log Whether or not to log progress (don't on initial web auth, do on crawl)
     * @return TwitterAPIAccessorOAuth
     */
    public function __construct($oauth_access_token, $oauth_access_token_secret, $oauth_consumer_key,
    $oauth_consumer_secret, $num_twitter_errors, $log=true) {
        $this->$oauth_access_token = $oauth_access_token;
        $this->$oauth_access_token_secret = $oauth_access_token_secret;
        $this->log = $log;

        $this->to = new TwitterOAuthThinkUp($oauth_consumer_key, $oauth_consumer_secret, $this->$oauth_access_token,
        $this->$oauth_access_token_secret);
        $this->endpoints = $this->prepAPI();

        $logger = Logger::getInstance();
        $te = (int) $num_twitter_errors;
        if (is_integer($te) && $te > 0) {
            $this->total_errors_to_tolerate = $te;
        }
        if ($this->log) {
            $logger->logInfo('Errors to tolerate: ' . $this->total_errors_to_tolerate, __METHOD__.','.__LINE__);
        }
    }
    /**
     * Verify OAuth Twitter credentials.
     * @return mixed null if not authorized; array of user data if authorized
     * @throws Exception if HTTP status code from Twitter is not 200 OK
     */
    public function verifyCredentials() {
        $endpoint = $this->endpoints['credentials']->getPath();
        $args = array('include_entities'=>'false', 'skip_status'=>'true');

        $payload = $this->to->OAuthRequest($endpoint, 'GET', $args);
        $http_status = $this->to->lastStatusCode();

        if ($http_status == 200) {
            $user = $this->parseJSONUser($payload);
            return $user;
        } else {
            throw new APIErrorException(self::translateErrorCode($http_status, true));
        }
    }
    /**
     * Define how to access the Twitter API.
     * @return array URLs by API call.
     */
    protected function prepAPI() {
        // Define method paths ... :id is a placeholder
        $api_method = array(
            "rate_limits"=>"application/rate_limit_status",
            "credentials"=>"account/verify_credentials",
            "user_timeline"=>"statuses/user_timeline",
            "show_user"=>"users/show/:id",
            "show_tweet"=>"statuses/show/:id",
            "mentions"=>"statuses/mentions_timeline",
            "retweets_of_me"=>"statuses/retweets_of_me",
            "retweeted_by"=>"statuses/retweets/:id",
            "followers_ids"=>"followers/ids",
            "followers"=>"followers/list",
            "check_group_member"=>"lists/members/show",
            "groups"=>"lists/memberships",
            "following"=>"friends/list",
            "following_ids"=>"friends/ids",
            "show_friendship"=>"friendships/show",
            "favorites"=>"favorites/list",
            "search_tweets"=>"search/tweets"

            //1.0 "favorites"=>"/favorites/:id",
        //1.0 "following"=>"/statuses/friends",
        //1.0 "followers"=>"/statuses/followers",
        //1.0 "retweeted_by"=>"/statuses/:id/retweeted_by",

        /* Unused by ThinkUp
         "end_session"=>"/account/end_session",
         "rate_limit"=>"/account/rate_limit_status",
         "delivery_device"=>"/account/update_delivery_device",
         "location"=>"/account/update_location",
         "profile"=>"/account/update_profile",
         "profile_background"=>"/account/update_profile_background_image",
         "profile_colors"=>"/account/update_profile_colors",
         "profile_image"=>"/account/update_profile_image",
         "block"=>"/blocks/create/:id",
         "remove_block"=>"/blocks/destroy/:id",
         "messages_received"=>"/direct_messages",
         "delete_message"=>"/direct_messages/destroy/:id",
         "post_message"=>"/direct_messages/new",
         "messages_sent"=>"/direct_messages/sent",
         "create_favorite"=>"/favorites/create/:id",
         "remove_favorite"=>"/favorites/destroy/:id",
         "follow"=>"/friendships/create/:id",
         "unfollow"=>"/friendships/destroy/:id",
         "confirm_follow"=>"/friendships/exists",
         "test"=>"/help/test",
         "turn_on_notification"=>"/notifications/follow/:id",
         "turn_off_notification"=>"/notifications/leave/:id",
         "delete_tweet"=>"/statuses/destroy/:id",
         "friends_timeline"=>"/statuses/friends_timeline",
         "public_timeline"=>"/statuses/public_timeline",
         "post_tweet"=>"/statuses/update",
         "retweeted_by_me"=>"/statuses/retweeted_by_me",
         */
        );

        $endpoints = array();
        // Construct endpoints
        foreach ($api_method as $key=>$value) {
            $endpoints[$key] = new TwitterAPIEndpoint("/".$value);
        }
        return $endpoints;
    }
    /**
     * Parse JSON list of tweets.
     * @param str $data JSON list of tweets.
     * @return array Posts
     */
    public function parseJSONTweets($data) {
        $json = JSONDecoder::decode($data);
        //print_r($json);
        $parsed_payload = array();
        if (isset($json)) {
            foreach ($json as $tweet) {
                $parsed_payload[] = self::convertJSONtoTweetArray($tweet);
            }
        }
        return $parsed_payload;
    }
    /**
     * Parse JSON list of tweets from search results.
     * @param str $data JSON list of tweets.
     * @return array Posts
     */
    public function parseJSONTweetsFromSearch($data) {
        $json = JSONDecoder::decode($data);
        //print_r($json);
        $parsed_payload = array();
        if (isset($json)) {
            foreach ($json->statuses as $tweet) {
                $parsed_payload[] = self::convertJSONtoTweetArray($tweet);
            }
        }
        return $parsed_payload;
    }
    /**
     * Parse JSON tweet.
     * @param str $data JSON tweet data
     * @return array Post values
     */
    public function parseJSONTweet($data) {
        $json = JSONDecoder::decode($data);
        //print_r($json);
        if (isset($json)) {
            return self::convertJSONtoTweetArray($json);
        }
        return null;
    }
    /**
     * Convert JSON representation of tweet to an array.
     * @param str $json_tweet
     * @return array Post values
     */
    private function convertJSONtoTweetArray($json_tweet) {
        //check for geo coordinates
        if (isset($json_tweet->geo->type) && $json_tweet->geo->type == 'Point'
        && isset($json_tweet->geo->coordinates[0]) && isset($json_tweet->geo->coordinates[1])) {
            $geo = $json_tweet->geo->coordinates[0].' '.$json_tweet->geo->coordinates[1];
        } else {
            $geo = '';
        }

        $result = array(
            'post_id'=>$json_tweet->id_str,
            'author_user_id'=>$json_tweet->user->id_str,
            'user_id'=>$json_tweet->user->id_str,
            'pub_date'=>gmdate("Y-m-d H:i:s", strToTime($json_tweet->created_at)),
            'post_text'=>$json_tweet->text,
            'author_username'=>$json_tweet->user->screen_name,
            'user_name'=>$json_tweet->user->screen_name,
            'in_reply_to_user_id'=>((isset($json_tweet->in_reply_to_user_id_str))?
        $json_tweet->in_reply_to_user_id_str:''),
            'author_avatar'=>$json_tweet->user->profile_image_url,
            'avatar'=>$json_tweet->user->profile_image_url,
            'in_reply_to_post_id'=> ((isset($json_tweet->in_reply_to_status_id_str))?
        $json_tweet->in_reply_to_status_id_str:''),
            'author_fullname'=>$json_tweet->user->name,
            'full_name'=>$json_tweet->user->name,
            'source'=>$json_tweet->source,
            'location'=>$json_tweet->user->location,
            'url'=>(isset($json_tweet->user->url)?$json_tweet->user->url:''),
            'description'=>$json_tweet->user->description,
            'is_verified'=>self::boolToInt($json_tweet->user->verified),
            'is_protected'=>self::boolToInt($json_tweet->user->protected),
            'follower_count'=>$json_tweet->user->followers_count,
            'post_count'=>$json_tweet->user->statuses_count,
            'geo'=>$geo,
            'place'=>(isset($json_tweet->place->full_name))?$json_tweet->place->full_name:'',
            'friend_count'=> (integer)$json_tweet->user->friends_count,
            'joined'=> (string)gmdate("Y-m-d H:i:s", strToTime($json_tweet->user->created_at)),
            'favorites_count'=>(integer)$json_tweet->user->favourites_count,
            'favlike_count_cache'=> ((isset($json_tweet->favorite_count))? (integer)$json_tweet->favorite_count:0),
            'network'=>'twitter'
            );

            //Get the API retweet count for original post
            if (isset($json_tweet->retweet_count) && !isset($json_tweet->retweeted_status)) {
                // do this only for the original post (rt will have rt count too)
                $retweet_count_api = $json_tweet->retweet_count;
                $pos = strrpos($json_tweet->retweet_count, '+');
                if ($pos !== false) {
                    // remove '+', e.g. '100+' --  currently 100 is the max count that will be reported
                    $retweet_count_api = substr($json_tweet->retweet_count, 0, $pos) ;
                }
                // this field holds the reported native rt count from twitter
                $result['retweet_count_api'] = (integer)$retweet_count_api;
            }
            if (isset($json_tweet->retweeted_status)) {
                // then this is a retweet.
                // Process its original too.
                // $logger->logInfo("this is a retweet, will process original post " . $post->retweeted_status->id .
                // "from user " . $post->retweeted_status->user->id, __METHOD__.','.__LINE__);
                $rtp = array();
                $rtp['content']= $this->convertJSONtoTweetArray($json_tweet->retweeted_status);
                $result['retweeted_post'] = $rtp;
                $result['in_retweet_of_post_id'] = $json_tweet->retweeted_status->id_str;
                $result['in_rt_of_user_id'] = $json_tweet->retweeted_status->user->id_str;
            }
            return $result;
    }
    /**
     * Parse user JSON.
     * @param str $data JSON user info.
     * @return array user data
     */
    public function parseJSONUser($data) {
        //@TODO Parse any status info as well like it was in the XML parsing methods
        $json = JSONDecoder::decode($data);
        //print_r($json);
        if (isset($json)) {
            return self::convertJSONtoUserArray($json);
        }
        return null;
    }
    /**
     * Parse JSON list of users
     * @param str $data JSON user info.
     * @return array user data
     */
    public function parseJSONUsers($data) {
        $json = JSONDecoder::decode($data);
        $parsed_payload = array();
        //print_r($json);

        //If it's a list of users, set the cursor
        if (isset($json->users)) {
            if (isset($json->next_cursor)) {
                $this->next_cursor =  $json->next_cursor_str;
            }
            foreach ($json->users as $user) {
                $parsed_payload[] = self::convertJSONtoUserArray($user);
            }
        } else {
            foreach ($json as $user) {
                $parsed_payload[] = self::convertJSONtoUserArray($user);
            }
        }
        return $parsed_payload;
    }
    /**
     * Parse JSON list of IDs
     * @param str $data JSON IDs
     * @return array IDs
     */
    public function parseJSONIDs($data) {
        $json = JSONDecoder::decode($data);
        $parsed_payload = array();
        //print_r($json);

        //If it's a list of IDs, set the cursor
        if (isset($json->ids)) {
            if (isset($json->next_cursor)) {
                $this->next_cursor =  $json->next_cursor_str;
            }
            foreach ($json->ids as $id) {
                $parsed_payload[] = array('id'=>$id);
            }
        } else {
            foreach ($json as $id) {
                $parsed_payload[] = array('id'=>$id);
            }
        }
        return $parsed_payload;
    }
    /**
     * Convert JSON representation of a user to an array.
     * @param str $json_user
     * @return array User values
     */
    private function convertJSONtoUserArray($json_user) {
        $result = array(
                'user_id'         => (string)$json_user->id_str,
                'user_name'       => (string)$json_user->screen_name,
                'full_name'       => (string)$json_user->name,
                'avatar'          => (string)$json_user->profile_image_url,
                'location'        => (string)$json_user->location,
                'description'     => (string)$json_user->description,
                'url'             => (string)$json_user->url,
                'is_verified'     => (integer)self::boolToInt($json_user->verified),
                'is_protected'    => (integer)self::boolToInt($json_user->protected),
                'follower_count'  => (integer)$json_user->followers_count,
                'friend_count'    => (integer)$json_user->friends_count,
                'post_count'      => (integer)$json_user->statuses_count,
                'favorites_count' => (integer)$json_user->favourites_count,
                'joined'          => gmdate("Y-m-d H:i:s", strToTime($json_user->created_at)),
                'network'         => 'twitter' );
        return $result;
    }
    /**
     * Convert JSON relationship data to array.
     * @param str $relationship_data
     * @return array
     */
    public function parseJSONRelationship($relationship_data) {
        $json = JSONDecoder::decode($relationship_data);
        //print_r($json);
        return array(
            'source_follows_target' => $json->relationship->source->following,
            'target_follows_source' => $json->relationship->target->following
        );
    }
    /**
     * Convert JSON lists list to array.
     * @param str $list_data
     * @return array
     */
    public function parseJSONLists($list_data) {
        $json = JSONDecoder::decode($list_data);
        $parsed_payload = array();
        //print_r($json);

        //If it's a list of lists, set the cursor
        if (isset($json->lists)) {
            if (isset($json->next_cursor)) {
                $this->next_cursor =  $json->next_cursor_str;
            }
            foreach ($json->lists as $list) {
                $parsed_payload[] = array(
                    'group_id'   => $list->id,
                    'group_name' => $list->full_name,
                    'owner_id'   => $list->user->id,
                    'owner_name' => $list->user->screen_name,
                    'network'    => 'twitter',
                );
            }
        } else {
            foreach ($json as $list) {
                $parsed_payload[] = array(
                    'group_id'   => $list->id,
                    'group_name' => $list->full_name,
                    'owner_id'   => $list->user->id,
                    'owner_name' => $list->user->screen_name,
                    'network'    => 'twitter',
                );
            }
        }
        return $parsed_payload;
    }
    /**
     * Convert JSON errors to array.
     * @param str $error_data
     * @return array
     */
    public function parseJSONError($error_data) {
        $json = JSONDecoder::decode($error_data);
        $parsed_payload = array();
        if (isset($json->errors)) {
            $parsed_payload['error'] = $json->errors[0]->message;
        }
        return $parsed_payload;
    }
    /**
     * Convert a var whose value is true to 1, else 0.
     * @param bool $bool_val
     * @return int 1 or 0
     */
    private static function boolToInt($bool_val) {
        return ($bool_val) ?1:0;
    }
    /**
     * Get next cursor.
     * @return int
     */
    public function getNextCursor() {
        //Don't cast this to an int; will break on extra large cursors in PHP 5.2
        return $this->next_cursor;
    }
    /**
     * Translates a Twitter API code to its corresponding explanation, as described in this link:
     * http://dev.twitter.com/pages/responses_errors
     *
     * @param str $error_code The error code.
     * @param bool $include_code Whether or not to include the code in the output.
     * @return str Translated error code
     */
    public function translateErrorCode($error_code, $include_code = true) {
        $translation = '';
        $error_code = strval($error_code);
        if (array_key_exists($error_code, $this->error_codes)) {
            $translation = $this->error_codes[$error_code];
        }
        // if the $include_code flag is set, append the error code to the explanation
        if ($include_code) {
            $translation = $error_code . ' ' . $translation;
        }
        return $translation;
    }

    /**
     * Returns an associative array of error_code => explanation pairs.
     *
     * @return array key => pairs of error codes that can be returned from the Twitter API.
     */
    public function getTwitterErrorCodes() {
        return $this->error_codes;
    }
}
