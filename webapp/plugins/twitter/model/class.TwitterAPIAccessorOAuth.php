<?php
/**
 * Twitter API Accessor
 * Accesses the Twitter.com API via OAuth authentication.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

class TwitterAPIAccessorOAuth {
    var $available = true;
    var $next_api_reset = null;
    var $cURL_source;
    var $to;
    var $oauth_access_token;
    var $oauth_access_token_secret;
    var $next_cursor;
    /**
     * Total API errors the crawler should tolerate during a given crawler run
     * @TODO Set this in the Twitter plugin options area
     * @var int
     */
    var $total_errors_to_tolerate = 3;
    /**
     * Tally of the API errors returned during a given run
     * When this number equals or exceeds the $total_errors_to_tolerate, the crawling stops
     * @var ints
     */
    var $total_errors_so_far = 0;

    public function __construct($oauth_access_token, $oauth_access_token_secret, $oauth_consumer_key,
    $oauth_consumer_secret) {
        $this->$oauth_access_token = $oauth_access_token;
        $this->$oauth_access_token_secret = $oauth_access_token_secret;

        $this->to = new TwitterOAuthThinkUp($oauth_consumer_key, $oauth_consumer_secret, $this->$oauth_access_token,
        $this->$oauth_access_token_secret);
        $this->cURL_source = $this->prepAPI();
    }

    public function verifyCredentials() {
        //returns user array; -1 if not.
        $auth = $this->cURL_source['credentials'];
        list($cURL_status, $twitter_data) = $this->apiRequestFromWebapp($auth);
        if ($cURL_status == 200) {
            $user = $this->parseXML($twitter_data);
            return $user[0];
        } else {
            return - 1;
        }
    }

    public function apiRequestFromWebapp($url) {
        $content = $this->to->OAuthRequest($url, 'GET', array());
        $status = $this->to->lastStatusCode();
        return array($status, $content);
    }

    public function prepAPI() {
        # Define how to access Twitter API
        $api_domain = 'https://api.twitter.com/1';
        $api_format = 'xml';
        $search_domain = 'http://search.twitter.com';
        $search_format = 'json';

        # Define method paths ... [id] is a placeholder
        $api_method = array(
            "end_session"=>"/account/end_session", "rate_limit"=>"/account/rate_limit_status", 
            "delivery_device"=>"/account/update_delivery_device", "location"=>"/account/update_location", 
            "profile"=>"/account/update_profile", "profile_background"=>"/account/update_profile_background_image", 
            "profile_colors"=>"/account/update_profile_colors", "profile_image"=>"/account/update_profile_image", 
            "credentials"=>"/account/verify_credentials", "block"=>"/blocks/create/[id]", 
            "remove_block"=>"/blocks/destroy/[id]", "messages_received"=>"/direct_messages", 
            "delete_message"=>"/direct_messages/destroy/[id]", "post_message"=>"/direct_messages/new", 
            "messages_sent"=>"/direct_messages/sent", "bookmarks"=>"/favorites/[id]", 
            "create_bookmark"=>"/favorites/create/[id]", "remove_bookmark"=>"/favorites/destroy/[id]", 
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
            "retweeted_by"=>"/statuses/[id]/retweeted_by");
        //http://api.twitter.com/1/statuses/14947487415/retweeted_by.xml
        # Construct cURL sources
        foreach ($api_method as $key=>$value) {
            $urls[$key] = $api_domain.$value.".".$api_format;
        }
        $urls['search'] = $search_domain."/search.".$search_format;
        $urls['search_web'] = $search_domain."/search";
        $urls['trends'] = $search_domain."/trends.json";

        return $urls;
    }

    public function parseFeed($url, $date = 0) {
        $parsed_payload = array();
        $feed_title = '';
        if (preg_match("/^http/", $url)) {
            try {
                $doc = createDOMfromURL($url);

                $feed_title = $doc->getElementsByTagName('title')->item(0)->nodeValue;

                $item = $doc->getElementsByTagName('item');
                foreach ($item as $item) {
                    $articleInfo = array('title'=>$item->getElementsByTagName('title')->item(0)->nodeValue,
                    'link'=>$item->getElementsByTagName('link')->item(0)->nodeValue, 
                    'id'=>$item->getElementsByTagName('id')->item(0)->nodeValue, 
                    'pubDate'=>$item->getElementsByTagName('pubDate')->item(0)->nodeValue);
                    if (($date == 0) || (strtotime($articleInfo['pubDate']) > strtotime($date))) {
                        array_push($parsed_payload, $articleInfo);
                    }
                }

                $entry = $doc->getElementsByTagName('entry');
                foreach ($entry as $entry) {
                    $articleInfo = array('title'=>$entry->getElementsByTagName('title')->item(0)->nodeValue,
                    'link'=>$entry->getElementsByTagName('link')->item(0)->getAttribute('href'), 
                    'id'=>$entry->getElementsByTagName('id')->item(0)->nodeValue, 
                    'pubDate'=>$entry->getElementsByTagName('pubDate')->item(0)->nodeValue, 
                    'published'=>$entry->getElementsByTagName('published')->item(0)->nodeValue);
                    foreach ($articleInfo as $key=>$value) {
                        $articleInfo[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    }
                    if (($date == 0) || (strtotime($articleInfo['pubDate']) > strtotime($date))
                    || (strtotime($articleInfo['published']) > strtotime($date))) {
                        array_push($parsed_payload, $articleInfo);
                    }
                }
            } catch(Exception $e) {
                $logger = Logger::getInstance();
                $logger->logStatus('parseFeed Exception caught: ' . $e->getMessage(), get_class($this));
            }
        }

        $feed_title = htmlspecialchars($feed_title, ENT_QUOTES, 'UTF-8');
        return array($parsed_payload, $feed_title);
    }

    public function parseJSON($data) {
        $pj = json_decode($data);
        //print_r($pj);
        $parsed_payload = array();
        foreach ($pj->results as $p) {
            $parsed_payload[] = array('post_id'=>$p->id,
            'author_user_id'=>$p->from_user_id, 'user_id'=>$p->from_user_id,
            'pub_date'=>gmdate("Y-m-d H:i:s", strToTime($p->created_at)), 'post_text'=>$p->text, 
            'author_username'=>$p->from_user, 'user_name'=>$p->from_user,
            'in_reply_to_user_id'=>$p->to_user_id, 
            'author_avatar'=>$p->profile_image_url, 'avatar'=>$p->profile_image_url,
            'in_reply_to_post_id'=>'', 'author_fullname'=>'', 'full_name'=>'', 'source'=>'twitter', 
            'location'=>'', 'url'=>'', 
            'description'=>'', 'is_protected'=>0, 'follower_count'=>0, 'post_count'=>0, 'joined'=>'');
        }
        return $parsed_payload;
    }

    public function parseError($data) {
        $parsed_payload = array();
        try {
            $xml = $this->createParserFromString(utf8_encode($data));
            if ($xml != false) {
                $root = $xml->getName();
                switch ($root) {
                    case 'hash':
                        $parsed_payload = array('request'=>$xml->request, 'error'=>$xml->error);
                        break;
                    default:
                        break;
                }
            }
        } catch(Exception $e) {
            $logger = Logger::getInstance();
            $logger->logStatus('parseError Exception caught: ' . $e->getMessage(), get_class($this));
        }

        return $parsed_payload;
    }

    public function parseXML($data) {
        $parsed_payload = array();
        try {
            $xml = $this->createParserFromString(utf8_encode($data));
            if ($xml != false) {
                $root = $xml->getName();
                switch ($root) {
                    case 'user':
                        $parsed_payload[] = array('user_id'=>$xml->id, 'user_name'=>$xml->screen_name,
                            'full_name'=>$xml->name, 'avatar'=>$xml->profile_image_url, 'location'=>$xml->location, 
                            'description'=>$xml->description, 'url'=>$xml->url, 'is_protected'=>$xml->protected , 
                            'follower_count'=>$xml->followers_count, 'friend_count'=>$xml->friends_count, 
                            'post_count'=>$xml->statuses_count, 'favorites_count'=>$xml->favourites_count, 
                            'joined'=>gmdate("Y-m-d H:i:s", strToTime($xml->created_at)), 'network'=>'twitter');
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
                            'author_user_id'=>$xml->user->id, 'user_id'=>$xml->user->id,
                            'author_username'=>$xml->user->screen_name, 'user_name'=>$xml->user->screen_name,
                            'author_fullname'=>$xml->user->name, 'full_name'=>$xml->user->name,
                            'author_avatar'=>$xml->user->profile_image_url, 'avatar'=>$xml->user->profile_image_url, 
                            'location'=>$xml->user->location, 
                            'description'=>$xml->user->description, 'url'=>$xml->user->url, 
                            'is_protected'=>$xml->user->protected , 'followers'=>$xml->user->followers_count, 
                            'following'=>$xml->user->friends_count, 'tweets'=>$xml->user->statuses_count, 
                            'joined'=>gmdate("Y-m-d H:i:s", strToTime($xml->user->created_at)), 
                            'post_text'=>$xml->text, 'pub_date'=>gmdate("Y-m-d H:i:s", strToTime($xml->created_at)), 
                            'in_reply_to_post_id'=>$xml->in_reply_to_status_id, 
                            'in_reply_to_user_id'=>$xml->in_reply_to_user_id, 'source'=>$xml->source, 
                            'geo'=>(isset($georss)?$georss->point:''), 'place'=>$xml->place->full_name, 
                            'network'=>'twitter');
                        break;
                    case 'users_list':
                        $this->next_cursor = $xml->next_cursor;
                        foreach ($xml->users->children() as $item) {
                            $parsed_payload[] = array('post_id'=>$item->status->id, 'user_id'=>$item->id,
                                'user_name'=>$item->screen_name, 'full_name'=>$item->name, 
                                'avatar'=>$item->profile_image_url, 'location'=>$item->location, 
                                'description'=>$item->description, 'url'=>$item->url, 'is_protected'=>$item->protected,
                                'friend_count'=>$item->friends_count, 'follower_count'=>$item->followers_count, 
                                'joined'=>gmdate("Y-m-d H:i:s", strToTime($item->created_at)), 
                                'post_text'=>$item->status->text, 
                                'last_post'=>gmdate("Y-m-d H:i:s", strToTime($item->status->created_at)), 
                                'pub_date'=>gmdate("Y-m-d H:i:s", strToTime($item->status->created_at)), 
                                'favorites_count'=>$item->favourites_count, 'post_count'=>$item->statuses_count,
                                'network'=>'twitter');
                        }
                        break;
                    case 'users':
                        foreach ($xml->children() as $item) {
                            $parsed_payload[] = array('post_id'=>$item->status->id, 'user_id'=>$item->id,
                                'user_name'=>$item->screen_name, 'full_name'=>$item->name, 
                                'avatar'=>$item->profile_image_url, 'location'=>$item->location, 
                                'description'=>$item->description, 'url'=>$item->url, 'is_protected'=>$item->protected,
                                'friend_count'=>$item->friends_count, 'follower_count'=>$item->followers_count, 
                                'joined'=>gmdate("Y-m-d H:i:s", strToTime($item->created_at)), 
                                'post_text'=>$item->status->text, 
                                'last_post'=>gmdate("Y-m-d H:i:s", strToTime($item->status->created_at)), 
                                'pub_date'=>gmdate("Y-m-d H:i:s", strToTime($item->status->created_at)), 
                                'favorites_count'=>$item->favourites_count, 'post_count'=>$item->statuses_count, 
                                'source'=>$item->status->source, 
                                'in_reply_to_post_id'=>$item->status->in_reply_to_status_id, 'network'=>'twitter');
                        }
                        break;
                    case 'statuses':
                        foreach ($xml->children() as $item) {
                            $georss = null;
                            $namespaces = $item->getNameSpaces(true);
                            if(isset($namespaces['georss'])) {
                                $georss = $item->geo->children($namespaces['georss']);
                            }
                            $parsed_payload[] = array('post_id'=>$item->id,
                                'author_user_id'=>$item->user->id, 'user_id'=>$item->user->id,
                                'author_username'=>$item->user->screen_name, 'user_name'=>$item->user->screen_name,
                                'author_fullname'=>$item->user->name, 'full_name'=>$item->user->name,
                                'author_avatar'=>$item->user->profile_image_url,
                                'avatar'=>$item->user->profile_image_url, 
                                'location'=>$item->user->location, 
                                'description'=>$item->user->description, 'url'=>$item->user->url, 
                                'is_protected'=>$item->user->protected , 'follower_count'=>$item->user->followers_count,
                                'friend_count'=>$item->user->friends_count, 'post_count'=>$item->user->statuses_count,
                                'joined'=>gmdate("Y-m-d H:i:s", strToTime($item->user->created_at)), 
                                'post_text'=>$item->text, 
                                'pub_date'=>gmdate("Y-m-d H:i:s", strToTime($item->created_at)), 
                                'favorites_count'=>$item->user->favourites_count, 
                                'in_reply_to_post_id'=>$item->in_reply_to_status_id, 
                                'in_reply_to_user_id'=>$item->in_reply_to_user_id, 'source'=>$item->source, 
                                'geo'=>(isset($georss)?$georss->point:''), 'place'=>$item->place->full_name, 
                                'network'=>'twitter');
                        }
                        break;
                    case 'hash':
                        $parsed_payload = array('remaining-hits'=>$xml-> {'remaining-hits'} ,
                            'hourly-limit'=>$xml-> {'hourly-limit'} , 'reset-time'=>$xml-> {'reset-time-in-seconds'} );
                        break;
                    case 'relationship':
                        $parsed_payload = array('source_follows_target'=>$xml->source->following,
                            'target_follows_source'=>$xml->target->following);
                        break;
                    default:
                        break;
                }
            }
        } catch(Exception $e) {
            $logger = Logger::getInstance();
            $logger->logStatus('parseXML Exception caught: ' . $e->getMessage(), get_class($this));
        }
        return $parsed_payload;
    }

    public function getNextCursor() {
        return $this->next_cursor;
    }

    public function createDOMfromURL($url) {
        $doc = new DOMDocument();
        $doc->load($url);
        return $doc;
    }

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

    private function logXMLError($error, $data) {
        $xml = explode("\n", $data);
        $logger = Logger::getInstance();
        $logger->logStatus('LIBXML '.$xml[$error->line - 1], get_class($this));
        $logger->logStatus('LIBXML '.str_repeat('-', $error->column) . "^", get_class($this));

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $logger->logStatus("LIBXML Warning $error->code: ", get_class($this));
                break;
            case LIBXML_ERR_ERROR:
                $logger->logStatus("LIBXML Error $error->code: ", get_class($this));
                break;
            case LIBXML_ERR_FATAL:
                $logger->logStatus("LIBXML Fatal Error $error->code: ", get_class($this));
                break;
        }
        $logger->logStatus('LIBXML '.trim($error->message). " Line: $error->line, Column $error->column",
        get_class($this));
    }
}
