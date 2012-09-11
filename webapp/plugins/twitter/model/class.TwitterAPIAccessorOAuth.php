<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterAPIAccessorOAuth.php
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
 * Twitter API Accessor
 * Accesses the Twitter.com API via OAuth authentication.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

class TwitterAPIAccessorOAuth {
    /**
     * @var boolean
     */
    var $available = true;
    /**
     * @var str
     */
    var $next_api_reset = null;
    /**
     * @var str
     */
    var $cURL_source;
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
     * @var int defaults to 3
     */
    // this is now the fallback default- should be set in plugin config
    var $total_errors_to_tolerate = 3;
    /**
     * Tally of the API errors returned during a given run
     * When this number equals or exceeds the $total_errors_to_tolerate, the crawling stops
     * @var ints
     */
    var $total_errors_so_far = 0;
    /**
     * The maximum number of API calls that should be made during a given crawl. This setting is here to ratchet
     * down activity for whitelisted Twitter accounts which get 20k calls per hour.
     * @var int Defaults to 350
     */
    var $max_api_calls_per_crawl = 350;
    /***
     * @var bool Whether or not to log messages
     */
    var $log ;
    /**
     * Constructor
     * @param str $oauth_access_token
     * @param str $oauth_access_token_secret
     * @param str $oauth_consumer_key
     * @param str $oauth_consumer_secret
     * @param int $num_twitter_errors
     * @param int $max_api_calls_per_crawl
     * @param bool $log Whether or not to log progress (don't on initial web auth, do on crawl)
     * @return TwitterAPIAccessorOAuth
     */
    public function __construct($oauth_access_token, $oauth_access_token_secret, $oauth_consumer_key,
    $oauth_consumer_secret, $num_twitter_errors, $max_api_calls_per_crawl, $log=true) {
        $this->$oauth_access_token = $oauth_access_token;
        $this->$oauth_access_token_secret = $oauth_access_token_secret;
        $this->log = $log;

        $this->to = new TwitterOAuthThinkUp($oauth_consumer_key, $oauth_consumer_secret, $this->$oauth_access_token,
        $this->$oauth_access_token_secret);
        $this->cURL_source = $this->prepAPI();

        $logger = Logger::getInstance();
        $te = (int) $num_twitter_errors;
        if (is_integer($te) && $te > 0) {
            $this->total_errors_to_tolerate = $te;
        }

        $this->max_api_calls_per_crawl = $max_api_calls_per_crawl;
        if ($this->log) {
            $logger->logInfo('Errors to tolerate: ' . $this->total_errors_to_tolerate, __METHOD__.','.__LINE__);
        }
    }

    /**
     * Verify OAuth Twitter credentials.
     * @return mixed -1 if not authorized; array of user data if authorized
     */
    public function verifyCredentials() {
        $auth = $this->cURL_source['credentials'];
        list($cURL_status, $twitter_data) = $this->apiRequestFromWebapp($auth);
        if ($cURL_status == 200) {
            $user = $this->parseXML($twitter_data);
            return $user[0];
        } else {
            return - 1;
        }
    }
    /**
     * Make an API request from the webapp (as opposed to the crawler)
     * @param str $url
     * @return array (cURL status, cURL retrieved content)
     */
    public function apiRequestFromWebapp($url) {
        $content = $this->to->OAuthRequest($url, 'GET', array());
        $status = $this->to->lastStatusCode();
        return array($status, $content);
    }

    /**
     * Define how to access the Twitter API.
     * @return array URLs by API call.
     */
    protected function prepAPI() {
        // Define how to access Twitter API
        $api_domain = 'https://api.twitter.com/1';
        $api_format = 'xml';
        $search_domain = 'http://search.twitter.com';
        $search_format = 'json';

        // Define method paths ... [id] is a placeholder
        $api_method = array(
            "end_session"=>"/account/end_session", "rate_limit"=>"/account/rate_limit_status",
            "delivery_device"=>"/account/update_delivery_device", "location"=>"/account/update_location",
            "profile"=>"/account/update_profile", "profile_background"=>"/account/update_profile_background_image",
            "profile_colors"=>"/account/update_profile_colors", "profile_image"=>"/account/update_profile_image",
            "credentials"=>"/account/verify_credentials", "block"=>"/blocks/create/[id]",
            "remove_block"=>"/blocks/destroy/[id]", "messages_received"=>"/direct_messages",
            "delete_message"=>"/direct_messages/destroy/[id]", "post_message"=>"/direct_messages/new",
            "messages_sent"=>"/direct_messages/sent", "favorites"=>"/favorites/[id]",
            "create_favorite"=>"/favorites/create/[id]", "remove_favorite"=>"/favorites/destroy/[id]",
            "followers_ids"=>"/followers/ids", "following_ids"=>"/friends/ids", "follow"=>"/friendships/create/[id]",
            "unfollow"=>"/friendships/destroy/[id]", "confirm_follow"=>"/friendships/exists",
            "show_friendship"=>"/friendships/show", "test"=>"/help/test",
            "turn_on_notification"=>"/notifications/follow/[id]", "turn_off_notification"=>"/notifications/leave/[id]",
            "delete_tweet"=>"/statuses/destroy/[id]", "followers"=>"/statuses/followers",
            "following"=>"/statuses/friends", "friends_timeline"=>"/statuses/friends_timeline",
            "public_timeline"=>"/statuses/public_timeline", "mentions"=>"/statuses/mentions",
            "show_tweet"=>"/statuses/show/[id]", "post_tweet"=>"/statuses/update",
            "user_timeline"=>"/statuses/user_timeline/[id]", "show_user"=>"/users/show/[id]",
            "retweeted_by_me"=>"/statuses/retweeted_by_me", "retweets_of_me"=>"/statuses/retweets_of_me",
            "retweeted_by"=>"/statuses/[id]/retweeted_by", "groups"=>"/lists/memberships",
            "check_group_member"=>"/lists/members/show",
        );
        // Construct cURL sources
        foreach ($api_method as $key=>$value) {
            $urls[$key] = $api_domain.$value.".".$api_format;
        }
        $urls['search'] = $search_domain."/search.".$search_format;
        $urls['search_web'] = $search_domain."/search";
        $urls['trends'] = $search_domain."/trends.json";

        return $urls;
    }

    /**
     * Parse JSON list of tweets.
     * @param str $data JSON list of tweets.
     * @return array Posts
     */
    public function parseJSON($data) {
        $pj = json_decode($data);
        //print_r($pj);
        $parsed_payload = array();
        foreach ($pj->results as $p) {
            $parsed_payload[] = array('post_id'=>$p->id_str,
            'author_user_id'=>$p->from_user_id, 'user_id'=>$p->from_user_id,
            'pub_date'=>gmdate("Y-m-d H:i:s", strToTime($p->created_at)), 'post_text'=>$p->text,
            'author_username'=>$p->from_user, 'user_name'=>$p->from_user,
            'in_reply_to_user_id'=>$p->to_user_id,
            'author_avatar'=>$p->profile_image_url, 'avatar'=>$p->profile_image_url,
            'in_reply_to_post_id'=>$p->in_reply_to_status_id_str, 'author_fullname'=>'', 'full_name'=>'',
            'source'=>'twitter', 'location'=>'', 'url'=>'',
            'description'=>'', 'is_protected'=>0, 'follower_count'=>0, 'post_count'=>0);
        }
        return $parsed_payload;
    }
    /**
     * Parse error XML
     * @param str $data
     * @return array Error
     */
    public function parseError($data) {
        $parsed_payload = array('request'=>'', 'error'=>'');
        try {
            $xml = $this->createParserFromString(utf8_encode($data));
            if ($xml !== false) {
                $root = $xml->getName();
                switch ($root) {
                    case 'hash':
                        $parsed_payload['request'] = $xml->request;
                        $parsed_payload['error'] = $xml->error;
                        break;
                    case 'errors':
                        $parsed_payload['error'] = $xml->error;
                        break;
                    default:
                        break;
                }
            }
        } catch (Exception $e) {
            $logger = Logger::getInstance();
            $logger->logUserError('parseError Exception caught: ' . $e->getMessage(), __METHOD__.','.__LINE__);
        }

        return $parsed_payload;
    }

    /**
     * Convert a SimpleXMLElement whose value is 'true' to 1, else 0.
     * @param SimpleXMLElement $bool_val
     * @return int 1 or 0
     */
    private static function boolXMLToInt($bool_val) {
        return ((string)$bool_val === 'true') ?1:0;
    }

    /**
     * Parse XML data returned from Twitter.
     * @param str $data
     * @return array Mixed data types, users, IDs, tweets, etc
     */
    public function parseXML($data) {
        $parsed_payload = array();
        try {
            $xml = $this->createParserFromString(utf8_encode($data));
            if ($xml !== false) {
                $root = $xml->getName();
                switch ($root) {
                    case 'user':
                        $parsed_payload[] = array(
                            'user_id'         => (string)$xml->id,
                            'user_name'       => (string)$xml->screen_name,
                            'full_name'       => (string)$xml->name,
                            'avatar'          => (string)$xml->profile_image_url,
                            'location'        => (string)$xml->location,
                            'description'     => (string)$xml->description,
                            'url'             => (string)$xml->url,
                            'is_protected'    => (integer)self::boolXMLToInt($xml->protected),
                            'follower_count'  => (integer)$xml->followers_count,
                            'friend_count'    => (integer)$xml->friends_count,
                            'post_count'      => (integer)$xml->statuses_count,
                            'favorites_count' => (integer)$xml->favourites_count,
                            'joined'          => gmdate("Y-m-d H:i:s", strToTime($xml->created_at)),
                            'network'         => 'twitter'
                            );
                            break;
                    case 'ids':
                        foreach ($xml->children() as $item) {
                            $parsed_payload[] = array('id'=>$item);
                        }
                        break;
                    case 'id_list':
                        $this->next_cursor = $xml->next_cursor;
                        foreach ($xml->ids->children() as $item) {
                            $parsed_payload[] = array('id'=>$item);
                        }
                        break;
                    case 'status':
                        $georss = null;
                        $namespaces = $xml->getNameSpaces(true);
                        if (isset($namespaces['georss'])) {
                            $georss = $xml->geo->children($namespaces['georss']);
                        }
                        $parsed_payload[] = array('post_id'=>$xml->id,
                            'author_user_id'      => (string)$xml->user->id,
                            'user_id'             => (string)$xml->user->id,
                            'author_username'     => (string)$xml->user->screen_name,
                            'user_name'           => (string)$xml->user->screen_name,
                            'author_fullname'     => (string)$xml->user->name,
                            'full_name'           => (string)$xml->user->name,
                            'author_avatar'       => (string)$xml->user->profile_image_url,
                            'avatar'              => (string)$xml->user->profile_image_url,
                            'location'            => (string)$xml->user->location,
                            'description'         => (string)$xml->user->description,
                            'url'                 => (string)$xml->user->url,
                            'is_protected'        => self::boolXMLToInt($xml->user->protected),
                            'followers'           => (integer)$xml->user->followers_count,
                            'following'           => (integer)$xml->user->friends_count,
                            'tweets'              => (integer)$xml->user->statuses_count,
                            'joined'              => gmdate("Y-m-d H:i:s", strToTime($xml->user->created_at)),
                            'post_text'           => (string)$xml->text,
                            'pub_date'            => gmdate("Y-m-d H:i:s", strToTime($xml->created_at)),
                            'in_reply_to_post_id' => (string)$xml->in_reply_to_status_id,
                            'in_reply_to_user_id' => (string)$xml->in_reply_to_user_id,
                            'source'              => (string)$xml->source,
                            'favorited'           => (string) $xml->favorited,
                            'geo'                 => (string)(isset($georss)?$georss->point:''),
                            'place'               => (string)$xml->place->full_name,
                            'network'             =>'twitter'
                            );
                            break;
                    case 'users_list':
                        $this->next_cursor = $xml->next_cursor;
                        foreach ($xml->users->children() as $item) {
                            $parsed_payload[] = array(
                                'user_id'         => (string)$item->id,
                                'user_name'       => (string)$item->screen_name,
                                'full_name'       => (string)$item->name,
                                'avatar'          => (string)$item->profile_image_url,
                                'location'        => (string)$item->location,
                                'description'     => (string)$item->description,
                                'url'             => (string)$item->url,
                                'is_protected'    => self::boolXMLToInt($item->protected),
                                'friend_count'    => (integer)$item->friends_count,
                                'follower_count'  => (integer)$item->followers_count,
                                'favorites_count' => (integer)$item->favourites_count,
                                'post_count'      => (integer)$item->statuses_count,
                                'network'         =>'twitter'
                                );
                                $current_index = (count($parsed_payload))-1;

                                //If a user hasn't posted, there are no statuses
                                if ( isset($item->status->created_at) ) {
                                    $parsed_payload[$current_index]['last_post'] = gmdate("Y-m-d H:i:s",
                                    strToTime($item->status->created_at) );
                                    $parsed_payload[$current_index]['joined'] = gmdate("Y-m-d H:i:s",
                                    strToTime($item->status->created_at) );
                                    $parsed_payload[$current_index]['pub_date'] = gmdate("Y-m-d H:i:s",
                                    strToTime($item->status->created_at) );
                                }
                                if (isset($item->status->text)) {
                                    $parsed_payload[$current_index]['post_text'] = (string)$item->status->text;
                                }
                                if (isset($item->status->id)) {
                                    $parsed_payload[$current_index]['post_id'] = (string)$item->status->id;
                                }
                        }
                        break;
                    case 'users':
                        foreach ($xml->children() as $item) {
                            $parsed_payload[] = array(
                                'user_id'             => (string)$item->id,
                                'user_name'           => (string)$item->screen_name,
                                'full_name'           => (string)$item->name,
                                'avatar'              => (string)$item->profile_image_url,
                                'location'            => (string)$item->location,
                                'description'         => (string)$item->description,
                                'url'                 => (string)$item->url,
                                'is_protected'        => self::boolXMLToInt($item->protected),
                                'friend_count'        => (integer)$item->friends_count,
                                'follower_count'      => (integer)$item->followers_count,
                                'favorites_count'     => (integer)$item->favourites_count,
                                'post_count'          => (integer)$item->statuses_count,
                                'network'             => 'twitter'
                                );
                                $current_index = (count($parsed_payload))-1;
                                //If a user hasn't posted, there are no statuses
                                if ( isset($item->status->created_at) ) {
                                    $parsed_payload[$current_index]['last_post'] = gmdate("Y-m-d H:i:s",
                                    strToTime($item->status->created_at) );
                                    $parsed_payload[$current_index]['joined'] = gmdate("Y-m-d H:i:s",
                                    strToTime($item->status->created_at) );
                                    $parsed_payload[$current_index]['pub_date'] = gmdate("Y-m-d H:i:s",
                                    strToTime($item->status->created_at) );
                                }
                                if (isset($item->status->text)) {
                                    $parsed_payload[$current_index]['post_text'] = (string)$item->status->text;
                                }
                                if (isset($item->status->id)) {
                                    $parsed_payload[$current_index]['post_id'] = (string)$item->status->id;
                                    $parsed_payload[$current_index]['source'] = (string)$item->status->source;
                                    $parsed_payload[$current_index]['in_reply_to_post_id'] =
                                    (string)$item->status->in_reply_to_status_id;
                                }
                        }
                        break;
                    case 'statuses':
                        foreach ($xml->children() as $item) {
                            $parsed_payload[] = $this->parsePostXML($item);
                        }
                        break;
                    case 'hash':
                        $parsed_payload = array(
                            'remaining-hits' => (integer)$xml->{'remaining-hits'},
                            'hourly-limit'   => (integer)$xml->{'hourly-limit'},
                            'reset-time'     => (integer)$xml->{'reset-time-in-seconds'}
                        );
                        break;
                    case 'relationship':
                        $parsed_payload = array(
                            'source_follows_target' => $xml->source->following,
                            'target_follows_source' => $xml->target->following
                        );
                        break;
                    case 'lists_list':
                        $this->next_cursor = $xml->next_cursor;
                        foreach ($xml->lists->children() as $item) {
                            $parsed_payload[] = array(
                            // might want to get additional fields:
                            // slug, subscriber_count, member_count, created_at, mode
                                'group_id'   => (string)$item->id,
                                'group_name' => (string)$item->full_name,
                                'owner_id'   => (string)$item->user->id,
                                'owner_name' => (string)$item->user->screen_name,
                                'network'    => 'twitter',
                            );
                        }
                        break;
                    default:
                        break;
                }
            }
        } catch(Exception $e) {
            $logger = Logger::getInstance();
            $logger->logUserError('parseXML Exception caught: ' . $e->getMessage(), __METHOD__.','.__LINE__);
        }
        return $parsed_payload;
    }

    private function parsePostXML($post) {
        $logger = Logger::getInstance();
        // $logger->logInfo("In parsePostXML for post " . $post->id . ", " . $post->text, __METHOD__.','.__LINE__);

        $georss = null;
        $namespaces = $post->getNameSpaces(true);
        if (isset($namespaces['georss'])) {
            $georss = $post->geo->children($namespaces['georss']);
        }
        $parsed_data = array(
            'post_id'             => (string)$post->id,
            'author_user_id'      => (string)$post->user->id,
            'user_id'             => (string)$post->user->id,
            'author_username'     => (string)$post->user->screen_name,
            'user_name'           => (string)$post->user->screen_name,
            'author_fullname'     => (string)$post->user->name,
            'full_name'           => (string)$post->user->name,
            'author_avatar'       => (string)$post->user->profile_image_url,
            'avatar'              => (string)$post->user->profile_image_url,
            'location'            => (string)$post->user->location,
            'description'         => (string)$post->user->description,
            'url'                 => (string)$post->user->url,
            'is_protected'        => (integer)self::boolXMLToInt($post->user->protected),
            'follower_count'      => (integer)$post->user->followers_count,
            'friend_count'        => (integer)$post->user->friends_count,
            'post_count'          => (integer)$post->user->statuses_count,
            'joined'              => (string)gmdate("Y-m-d H:i:s", strToTime($post->user->created_at)),
            'post_text'           => (string)$post->text,
            'pub_date'            => (string)gmdate("Y-m-d H:i:s", strToTime($post->created_at)),
            'favorites_count'     => (integer)$post->user->favourites_count,
            'in_reply_to_post_id' => (string)$post->in_reply_to_status_id,
            'in_reply_to_user_id' => (string)$post->in_reply_to_user_id,
            'source'              => (string)$post->source,
        // 'favorited' => $xml->favorited, // what did this do?
            'geo'                 => (string)(isset($georss)?$georss->point:''),
            'place'               => (string) $post->place->full_name,
            'network'             => 'twitter');
        if (isset($post->retweet_count) && !isset($post->retweeted_status)) {
            // do this only for the original post (rt will have rt count too)
            $retweet_count_api = $post->retweet_count;
            $pos = strrpos($post->retweet_count, '+');
            if ($pos !== false) {
                // remove '+', e.g. '100+' --  currently 100 is the max count that will be reported
                $retweet_count_api = substr($post->retweet_count, 0, $pos) ;
            }
            // this field holds the reported native rt count from twitter
            $parsed_data['retweet_count_api'] = (integer)$retweet_count_api;
        }
        if (isset($post->retweeted_status)) {
            // then this is a retweet.
            // Process its original too.
            // $logger->logInfo("this is a retweet, will process original post " . $post->retweeted_status->id .
            // "from user " . $post->retweeted_status->user->id, __METHOD__.','.__LINE__);
            $rtp = array();
            $rtp['content']= $this->parsePostXML($post->retweeted_status);
            $parsed_data['retweeted_post'] = $rtp;
            $parsed_data['in_retweet_of_post_id'] = (string)$post->retweeted_status->id;
            $parsed_data['in_rt_of_user_id'] = (string)$post->retweeted_status->user->id;

        }
        return $parsed_data;
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
     * Create DOM from URL.
     * @param str $url
     * @return DOMDocument
     */
    public function createDOMfromURL($url) {
        $doc = new DOMDocument();
        $doc->load($url);
        return $doc;
    }
    /**
     * Create XML parser from string.
     * @param str $data
     * @return object
     */
    public function createParserFromString($data) {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($data);
        if (!$xml) {
            foreach (libxml_get_errors() as $error) {
                $this->logXMLError($error, $data);
            }
            libxml_clear_errors();
        }
        return $xml;
    }
    /**
     * Log XML error.
     * @param object $error
     * @param str $data
     */
    private function logXMLError($error, $data) {
        $xml = explode("\n", $data);
        $logger = Logger::getInstance();
        $logger->logUserError('LIBXML '.$xml[$error->line - 1], __METHOD__.','.__LINE__);
        $logger->logUserError('LIBXML '.str_repeat('-', $error->column) . "^", __METHOD__.','.__LINE__);

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $logger->logInfo("LIBXML Warning $error->code: ", __METHOD__.','.__LINE__);
                break;
            case LIBXML_ERR_ERROR:
                $logger->logInfo("LIBXML Error $error->code: ", __METHOD__.','.__LINE__);
                break;
            case LIBXML_ERR_FATAL:
                $logger->logInfo("LIBXML Fatal Error $error->code: ", __METHOD__.','.__LINE__);
                break;
        }
        $logger->logUserError('LIBXML '.trim($error->message). " Line: $error->line, Column $error->column",
        __METHOD__.','.__LINE__);
    }
}
