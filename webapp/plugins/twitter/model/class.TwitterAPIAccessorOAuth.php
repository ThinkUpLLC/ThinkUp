<?php 
class TwitterAPIAccessorOAuth {
    var $available = true;
    var $next_api_reset = null;
    var $cURL_source;
    var $to;
    var $oauth_access_token;
    var $oauth_access_token_secret;
    var $next_cursor;
    
    function TwitterAPIAccessorOAuth($oauth_access_token, $oauth_access_token_secret, $oauth_consumer_key, $oauth_consumer_secret) {
        $this->$oauth_access_token = $oauth_access_token;
        $this->$oauth_access_token_secret = $oauth_access_token_secret;
        
        $this->to = new TwitterOAuthThinkTank($oauth_consumer_key, $oauth_consumer_secret, $this->$oauth_access_token, $this->$oauth_access_token_secret);
        $this->cURL_source = $this->prepAPI();
    }
    
    function verifyCredentials() {
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
    
    function apiRequestFromWebapp($url) {
        $content = $this->to->OAuthRequest($url, 'GET', array());
        $status = $this->to->lastStatusCode();
        return array($status, $content);
    }
    
    function prepAPI() {
    
        # Define how to access Twitter API
        $api_domain = 'https://api.twitter.com';
        $api_format = 'xml';
        $search_domain = 'http://search.twitter.com';
        $search_format = 'json';
        
        # Define method paths ... [id] is a placeholder
        $api_method = array("end_session"=>"/account/end_session", "rate_limit"=>"/account/rate_limit_status", "delivery_device"=>"/account/update_delivery_device", "location"=>"/account/update_location", "profile"=>"/account/update_profile", "profile_background"=>"/account/update_profile_background_image", "profile_colors"=>"/account/update_profile_colors", "profile_image"=>"/account/update_profile_image", "credentials"=>"/account/verify_credentials", "block"=>"/blocks/create/[id]", "remove_block"=>"/blocks/destroy/[id]", "messages_received"=>"/direct_messages", "delete_message"=>"/direct_messages/destroy/[id]", "post_message"=>"/direct_messages/new", "messages_sent"=>"/direct_messages/sent", "bookmarks"=>"/favorites/[id]", "create_bookmark"=>"/favorites/create/[id]", "remove_bookmark"=>"/favorites/destroy/[id]", "followers_ids"=>"/followers/ids", "following_ids"=>"/friends/ids", "follow"=>"/friendships/create/[id]", "unfollow"=>"/friendships/destroy/[id]", "confirm_follow"=>"/friendships/exists", "show_friendship"=>"/friendships/show", "test"=>"/help/test", "turn_on_notification"=>"/notifications/follow/[id]", "turn_off_notification"=>"/notifications/leave/[id]", "delete_tweet"=>"/statuses/destroy/[id]", "followers"=>"/statuses/followers", "following"=>"/statuses/friends", "friends_timeline"=>"/statuses/friends_timeline", "public_timeline"=>"/statuses/public_timeline", "mentions"=>"/statuses/mentions", "show_tweet"=>"/statuses/show/[id]", "post_tweet"=>"/statuses/update", "user_timeline"=>"/statuses/user_timeline/[id]", "show_user"=>"/users/show/[id]", "retweeted_by_me"=>"/statuses/retweeted_by_me");
        
        # Construct cURL sources
        foreach ($api_method as $key=>$value) {
            $urls[$key] = $api_domain.$value.".".$api_format;
        }
        $urls['search'] = $search_domain."/search.".$search_format;
        $urls['search_web'] = $search_domain."/search";
        $urls['trends'] = $search_domain."/trends.json";
        
        return $urls;
    }
    
    function parseFeed($url, $date = 0) {
        $thisFeed = array();
        $feed_title = '';
        if (preg_match("/^http/", $url)) {
            try {
                $doc = createDOMfromURL($url);
                
                $feed_title = $doc->getElementsByTagName('title')->item(0)->nodeValue;
                
                $item = $doc->getElementsByTagName('item');
                foreach ($item as $item) {
                    $articleInfo = array('title'=>$item->getElementsByTagName('title')->item(0)->nodeValue, 'link'=>$item->getElementsByTagName('link')->item(0)->nodeValue, 'id'=>$item->getElementsByTagName('id')->item(0)->nodeValue, 'pubDate'=>$item->getElementsByTagName('pubDate')->item(0)->nodeValue);
                    if (($date == 0) || (strtotime($articleInfo['pubDate']) > strtotime($date))) {
                        array_push($thisFeed, $articleInfo);
                    }
                }
                
                $entry = $doc->getElementsByTagName('entry');
                foreach ($entry as $entry) {
                    $articleInfo = array('title'=>$entry->getElementsByTagName('title')->item(0)->nodeValue, 'link'=>$entry->getElementsByTagName('link')->item(0)->getAttribute('href'), 'id'=>$entry->getElementsByTagName('id')->item(0)->nodeValue, 'pubDate'=>$entry->getElementsByTagName('pubDate')->item(0)->nodeValue, 'published'=>$entry->getElementsByTagName('published')->item(0)->nodeValue);
                    foreach ($articleInfo as $key=>$value) {
                        $articleInfo[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    }
                    if (($date == 0) || (strtotime($articleInfo['pubDate']) > strtotime($date)) || (strtotime($articleInfo['published']) > strtotime($date))) {
                        array_push($thisFeed, $articleInfo);
                    }
                }
            }
            catch(Exception $e) {
                $form_error = 15;
            }
        }
        
        $feed_title = htmlspecialchars($feed_title, ENT_QUOTES, 'UTF-8');
        return array($thisFeed, $feed_title);
    }
    
    function parseJSON($data) {
        $pj = json_decode($data);
        //print_r($pj);
        $thisFeed = array();
        foreach ($pj->results as $p) {
            $thisFeed[] = array('post_id'=>$p->id, 'user_id'=>$p->from_user_id, 'pub_date'=>gmdate("Y-m-d H:i:s", strToTime($p->created_at)), 'post_text'=>$p->text, 'user_name'=>$p->from_user, 'in_reply_to_user_id'=>$p->to_user_id, 'avatar'=>$p->profile_image_url, 'in_reply_to_post_id'=>'', 'full_name'=>'', 'source'=>'twitter', 'location'=>'', 'url'=>'', 'description'=>'', 'is_protected'=>0, 'follower_count'=>0, 'post_count'=>0, 'joined'=>'');
        }
        return $thisFeed;
    }
    
    function parseError($data) {
        $thisFeed = array();
        try {
            $xml = $this->createParserFromString(utf8_encode($data));
            if ($xml != false) {
                $root = $xml->getName();
                switch ($root) {
                    case 'hash':
                        $thisFeed = array('request'=>$xml->request, 'error'=>$xml->error);
                        break;
                    default:
                        break;
                }
            }
        }
        catch(Exception $e) {
            $form_error = 15;
        }
        
        return $thisFeed;
    }
    
    function parseXML($data) {
        $thisFeed = array();
        try {
            $xml = $this->createParserFromString(utf8_encode($data));
            if ($xml != false) {
                $root = $xml->getName();
                switch ($root) {
                    case 'user':
                        $thisFeed[] = array('user_id'=>$xml->id, 'user_name'=>$xml->screen_name, 'full_name'=>$xml->name, 'avatar'=>$xml->profile_image_url, 'location'=>$xml->location, 'description'=>$xml->description, 'url'=>$xml->url, 'is_protected'=>$xml->protected , 'follower_count'=>$xml->followers_count, 'friend_count'=>$xml->friends_count, 'post_count'=>$xml->statuses_count, 'favorites_count'=>$xml->favourites_count, 'joined'=>gmdate("Y-m-d H:i:s", strToTime($xml->created_at)), );
                        break;
                    case 'ids':
                        foreach ($xml->children() as $item) {
                            $thisFeed[] = array('id'=>$item);
                        }
                        break;
                    case 'id_list':
                        $this->next_cursor = $xml->next_cursor;
                        foreach ($xml->ids->children() as $item) {
                            $thisFeed[] = array('id'=>$item);
                        }
                        break;
                    case 'status':
                        $thisFeed[] = array('post_id'=>$xml->id, 'user_id'=>$xml->user->id, 'user_name'=>$xml->user->screen_name, 'full_name'=>$xml->user->name, 'avatar'=>$xml->user->profile_image_url, 'location'=>$xml->user->location, 'description'=>$xml->user->description, 'url'=>$xml->user->url, 'is_protected'=>$xml->user->protected , 'followers'=>$xml->user->followers_count, 'following'=>$xml->user->friends_count, 'tweets'=>$xml->user->statuses_count, 'joined'=>gmdate("Y-m-d H:i:s", strToTime($xml->user->created_at)), 'post_text'=>$xml->text, 'pub_date'=>gmdate("Y-m-d H:i:s", strToTime($xml->created_at)), 'in_reply_to_post_id'=>$xml->in_reply_to_status_id, 'in_reply_to_user_id'=>$xml->in_reply_to_user_id, 'source'=>$xml->source);
                        break;
                    case 'users_list':
                        $this->next_cursor = $xml->next_cursor;
                        foreach ($xml->users->children() as $item) {
                            $thisFeed[] = array('post_id'=>$item->status->id, 'user_id'=>$item->id, 'user_name'=>$item->screen_name, 'full_name'=>$item->name, 'avatar'=>$item->profile_image_url, 'location'=>$item->location, 'description'=>$item->description, 'url'=>$item->url, 'is_protected'=>$item->protected , 'friend_count'=>$item->friends_count, 'follower_count'=>$item->followers_count, 'joined'=>gmdate("Y-m-d H:i:s", strToTime($item->created_at)), 'post_text'=>$item->status->text, 'last_post'=>gmdate("Y-m-d H:i:s", strToTime($item->status->created_at)), 'pub_date'=>gmdate("Y-m-d H:i:s", strToTime($item->status->created_at)), 'favorites_count'=>$item->favourites_count, 'post_count'=>$item->statuses_count);
                        }
                        break;
                    case 'users':
                        foreach ($xml->children() as $item) {
                            $thisFeed[] = array('post_id'=>$item->status->id, 'user_id'=>$item->id, 'user_name'=>$item->screen_name, 'full_name'=>$item->name, 'avatar'=>$item->profile_image_url, 'location'=>$item->location, 'description'=>$item->description, 'url'=>$item->url, 'is_protected'=>$item->protected , 'friend_count'=>$item->friends_count, 'follower_count'=>$item->followers_count, 'joined'=>gmdate("Y-m-d H:i:s", strToTime($item->created_at)), 'post_text'=>$item->status->text, 'last_post'=>gmdate("Y-m-d H:i:s", strToTime($item->status->created_at)), 'pub_date'=>gmdate("Y-m-d H:i:s", strToTime($item->status->created_at)), 'favorites_count'=>$item->favourites_count, 'post_count'=>$item->statuses_count);
                        }
                        break;
                    case 'statuses':
                        foreach ($xml->children() as $item) {
                            $thisFeed[] = array('post_id'=>$item->id, 'user_id'=>$item->user->id, 'user_name'=>$item->user->screen_name, 'full_name'=>$item->user->name, 'avatar'=>$item->user->profile_image_url, 'location'=>$item->user->location, 'description'=>$item->user->description, 'url'=>$item->user->url, 'is_protected'=>$item->user->protected , 'follower_count'=>$item->user->followers_count, 'friend_count'=>$item->user->friends_count, 'post_count'=>$item->user->statuses_count, 'joined'=>gmdate("Y-m-d H:i:s", strToTime($item->user->created_at)), 'post_text'=>$item->text, 'pub_date'=>gmdate("Y-m-d H:i:s", strToTime($item->created_at)), 'favorites_count'=>$item->user->favourites_count, 'in_reply_to_post_id'=>$item->in_reply_to_status_id, 'in_reply_to_user_id'=>$item->in_reply_to_user_id, 'source'=>$item->source);
                        }
                        break;
                    case 'hash':
                        $thisFeed = array('remaining-hits'=>$xml-> {'remaining-hits'} , 'hourly-limit'=>$xml-> {'hourly-limit'} , 'reset-time'=>$xml-> {'reset-time-in-seconds'} );
                        break;
                    case 'relationship':
                        $thisFeed = array('source_follows_target'=>$xml->source->following, 'target_follows_source'=>$xml->target->following);
                        break;
                    default:
                        break;
                }
                
            }
        }
        catch(Exception $e) {
            $form_error = 15;
        }
        
        return $thisFeed;
    }
    
    function getNextCursor() {
        return $this->next_cursor;
    }
    
    function createDOMfromURL($url) {
        $doc = new DOMDocument();
        $doc->load($url);
        return $doc;
    }
    
    function createParserFromString($data) {
        $xml = simplexml_load_string($data);
        return $xml;
    }
    
}

class CrawlerTwitterAPIAccessorOAuth extends TwitterAPIAccessorOAuth {
    var $api_calls_to_leave_unmade;
    var $api_calls_to_leave_unmade_per_minute;
    var $available_api_calls_for_crawler = null;
    var $available_api_calls_for_twitter = null;
    var $api_hourly_limit = null;
    var $archive_limit;
    
    function CrawlerTwitterAPIAccessorOAuth($oauth_token, $oauth_token_secret, $oauth_consumer_key, $oauth_consumer_secret, $instance, $archive_limit) {
        parent::TwitterAPIAccessorOAuth($oauth_token, $oauth_token_secret, $oauth_consumer_key, $oauth_consumer_secret);
        $this->api_calls_to_leave_unmade_per_minute = $instance->api_calls_to_leave_unmade_per_minute;
        $this->archive_limit = $archive_limit;
    }
    
    function init($logger) {
        $status_message = "";
        
        $account_status = $this->cURL_source['rate_limit'];
        list($cURL_status, $twitter_data) = $this->apiRequest($account_status, $logger);
        $this->available_api_calls_for_crawler++; //status check doesnt' count against balance
        
        if ($cURL_status > 200) {
            $this->available = false;
        } else {
            try {
                # Parse file
                $status_message = "Parsing XML data from $account_status ";
                $status = $this->parseXML($twitter_data);
                
                if (isset($status['remaining-hits']) && isset($status['hourly-limit']) && isset($status['reset-time'])) {
                    $this->available_api_calls_for_twitter = $status['remaining-hits'];//get this from API
                    $this->api_hourly_limit = $status['hourly-limit'];//get this from API
                    $this->next_api_reset = $status['reset-time'];//get this from API
                } else {
                    throw new Exception('API status came back malformed');
                }
                //Figure out how many minutes are left in the hour, then multiply that x 1 for api calls to leave unmade
                $next_reset_in_minutes = (int) date('i', (int) $this->next_api_reset);
                $current_time_in_minutes = (int) date("i", time());
                $minutes_left_in_hour = 60;
                if ($next_reset_in_minutes > $current_time_in_minutes)
                    $minutes_left_in_hour = $next_reset_in_minutes - $current_time_in_minutes;
                elseif ($next_reset_in_minutes < $current_time_in_minutes)
                    $minutes_left_in_hour = 60 - ($current_time_in_minutes - $next_reset_in_minutes);

                    
                //echo $minutes_left_in_hour . " minutes left in the hour till ".  date('H:i:s', (int) $this->next_api_reset);
                $this->api_calls_to_leave_unmade = $minutes_left_in_hour * $this->api_calls_to_leave_unmade_per_minute;
                //echo "  ".$this->api_calls_to_leave_unmade . " API calls to leave unmade\n";
                $this->available_api_calls_for_crawler = $this->available_api_calls_for_twitter - round($this->api_calls_to_leave_unmade);

                
            }
            catch(Exception $e) {
                $status_message = 'Could not parse account status: '.$e->getMessage();
            }
        }
        $logger->logStatus($status_message, get_class($this));
        $logger->logStatus($this->getStatus(), get_class($this));
        
    }
    
    function apiRequest($url, $logger, $args = array(), $auth = true) {
        if ($auth) {
            $content = $this->to->OAuthRequest($url, 'GET', $args);
            $status = $this->to->lastStatusCode();
            
            $this->available_api_calls_for_twitter = $this->available_api_calls_for_twitter - 1;
            $this->available_api_calls_for_crawler = $this->available_api_calls_for_crawler - 1;
            $status_message = "";
            if ($status > 200) {
                $status_message = "Could not retrieve $url";
                if (sizeof($args) > 0)
                    $status_message .= "?";
                foreach ($args as $key=>$value)
                    $status_message .= $key."=".$value."&";
                $status_message .= " | API ERROR: $status";
                $status_message .= "\n\n$content\n\n";
                if ($status != 404 && $status != 403)
                    $this->available = false;
                $logger->logStatus($status_message, get_class($this));
                $status_message = "";
            } else {
                $status_message = "API request: ".$url;
                if (sizeof($args) > 0)
                    $status_message .= "?";
                foreach ($args as $key=>$value)
                    $status_message .= $key."=".$value."&";
            }
            
            $logger->logStatus($status_message, get_class($this));
            $status_message = "";
            
            if ($url != "https://twitter.com/account/rate_limit_status.xml") {
                $status_message = $this->getStatus();
                $logger->logStatus($status_message, get_class($this));
                $status_message = "";
            }
        } else {
            $logger->logStatus("OAuth-free request: $url", get_class($this));
            $content = $this->to->noAuthRequest($url);
            $status = $this->to->lastStatusCode();
            //$logger->logStatus("no OAuth content returned: $content", get_class($this));
        }
        
        return array($status, $content);
        
    }
    
    function getStatus() {
        return $this->available_api_calls_for_twitter." of ".$this->api_hourly_limit." API calls left this hour; ".round($this->available_api_calls_for_crawler)." for crawler until ".date('H:i:s', (int) $this->next_api_reset);
        
    }
}
?>
