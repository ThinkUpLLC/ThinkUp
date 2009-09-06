<?php 
class Crawler {
    var $instance;
    var $owner_object;
    var $ud;
    
    function Crawler($instance) {
        $this->instance = $instance;
        $this->ud = new UserDao();
    }
    
    function fetchInstanceUserInfo($cfg, $api, $logger) {
        // Get owner user details and save them to DB
        $status_message = "";
        $owner_profile = str_replace("[id]", $cfg->twitter_username, $api->cURL_source['show_user']);
        list($cURL_status, $twitter_data) = $api->apiRequest($owner_profile, $logger);
        
        if ($cURL_status == 200) {
            try {
                $users = $api->parseXML($twitter_data);
                foreach ($users as $user)
                    $this->owner_object = new User($user, 'Owner Status');
                    
                if (isset($this->owner_object)) {
                    $status_message = 'Owner info set.';
                    $this->ud->updateUser($this->owner_object, $logger);
                } else {
                    $status_message = 'Owner was not set.';
                }
            }
            catch(Exception $e) {
                $status_message = 'Could not parse profile XML for $cfg->twitter_username';
            }
        } else {
            $status_message = 'cURL status is not 200';
        }
        $logger->logStatus($status_message, get_class($this));
        $status_message = "";
    }
    
    function fetchInstanceUserTweets($cfg, $api, $logger) {
        // Get owner's tweets
        $status_message = "";
        $got_latest_page_of_tweets = false;
        $continue_fetching = true;
        
        while ($api->available && $api->available_api_calls_for_crawler > 0 && $this->owner_object->tweet_count > $this->instance->total_tweets_in_system && $continue_fetching) {
        
            $recent_tweets = str_replace("[id]", $cfg->twitter_username, $api->cURL_source['user_timeline']);
            $args = array();
            $args["count"] = 200;
            $last_page_of_tweets = round($cfg->archive_limit / 200) + 1;
            
            //set page and since_id params for API call
            if ($got_latest_page_of_tweets && $this->owner_object->tweet_count != $this->instance->total_tweets_in_system && $this->instance->total_tweets_in_system < $cfg->archive_limit) {
                if ($this->instance->last_page_fetched_tweets < $last_page_of_tweets)
                    $this->instance->last_page_fetched_tweets = $this->instance->last_page_fetched_tweets + 1;
                else {
                    $continue_fetching = false;
                    $this->instance->last_page_fetched_tweets = 0;
                }
                $args["page"] = $this->instance->last_page_fetched_tweets;
                
            } else {
                if (!$got_latest_page_of_tweets && $this->instance->last_status_id > 0)
                    $args["since_id"] = $this->instance->last_status_id;
            }
            
            list($cURL_status, $twitter_data) = $api->apiRequest($recent_tweets, $logger, $args);
            if ($cURL_status == 200) {
                # Parse the XML file
                try {
                    $count = 0;
                    $tweets = $api->parseXML($twitter_data);
                    
                    $td = new TweetDAO;
                    foreach ($tweets as $tweet) {
                    
                        if ($td->addTweet($tweet, $this->owner_object, $logger) > 0) {
                            $count = $count + 1;
                            $this->instance->total_tweets_in_system = $this->instance->total_tweets_in_system + 1;
                            
                            //expand and insert links contained in tweet
                            $this->processTweetURLs($tweet, $cfg, $logger);
                            
                        }
                        if ($tweet['status_id'] > $this->instance->last_status_id)
                            $this->instance->last_status_id = $tweet['status_id'];
                            
                    }
                    $status_message .= count($tweets)." tweet(s) found and $count saved";
                    $logger->logStatus($status_message, get_class($this));
                    $status_message = "";
                    
                    //if you've got more than the Twitter API archive limit, stop looking for more tweets
                    if ($this->instance->total_tweets_in_system >= $cfg->archive_limit) {
                        $this->instance->last_page_fetched_tweets = 1;
                        $continue_fetching = false;
                        $status_message = "More than Twitter cap of ".$cfg->archive_limit." already in system, moving on.";
                        $logger->logStatus($status_message, get_class($this));
                        $status_message = "";
                    }

                    
                    if ($this->owner_object->tweet_count == $this->instance->total_tweets_in_system)
                        $this->instance->is_archive_loaded_tweets = true;
                        
                    $status_message .= $this->instance->total_tweets_in_system." in system; ".$this->owner_object->tweet_count." by owner";
                    $logger->logStatus($status_message, get_class($this));
                    $status_message = "";
                    
                }
                catch(Exception $e) {
                    $status_message = 'Could not parse tweet XML for $this->twitter_username';
                    $logger->logStatus($status_message, get_class($this));
                    $status_message = "";
                    
                }
                
                $got_latest_page_of_tweets = true;
            }
        }
        
        if ($this->owner_object->tweet_count == $this->instance->total_tweets_in_system)
            $status_message .= "All of ".$this->owner_object->user_name."'s tweets are in the system; Stopping tweet fetch.";

            
        $logger->logStatus($status_message, get_class($this));
        $status_message = "";
        
    }
    
    private function processTweetURLs($tweet, $cfg, $logger) {
    
        $ld = new LinkDAO;
        $lurl = new LongUrlAPIAccessor($cfg);
        $fa = new FlickrAPIAccessor($cfg);
        
        $urls = Tweet::extractURLs($tweet['tweet_text']);
        foreach ($urls as $u) {
            //if it's an image (Twitpic/Twitgoo/Yfrog/Flickr for now), insert direct path to thumb as expanded url, otherwise, just expand
            //set defaults
            $is_image = 0;
            $title = '';
            $eurl = '';
            if (substr($u, 0, strlen('http://twitpic.com/')) == 'http://twitpic.com/') {
                $eurl = 'http://twitpic.com/show/thumb/'.substr($u, strlen('http://twitpic.com/'));
                $is_image = 1;
            } elseif ( substr($u, 0, strlen('http://yfrog.com/')) == 'http://yfrog.com/' ) {
            	$eurl = $u.'.th.jpg';
				$is_image = 1;	 
			} elseif (substr($u, 0, strlen('http://twitgoo.com/')) == 'http://twitgoo.com/') {
                $eurl = 'http://twitgoo.com/show/thumb/'.substr($u, strlen('http://twitgoo.com/'));
                $is_image = 1;
            } elseif ($cfg->flickr_api_key != null && substr($u, 0, strlen('http://flic.kr/p/')) == 'http://flic.kr/p/') {
                $eurl = $fa->getFlickrPhotoSource($u);
                if ($eurl != '')
                    $is_image = 1;
            } else {
                $eurl_arr = $lurl->expandUrl($u);
                if (isset($eurl_arr['response-code']) && $eurl_arr['response-code'] == 200) {
                    $eurl = $eurl_arr['long-url'];
                    if (isset($eurl_arr['title']))
                        $title = $eurl_arr['title'];
                }
            }
            
            if ($ld->insert($u, $eurl, $title, $tweet['status_id'], $is_image))
                $logger->logStatus("Inserted ".$u." (".$eurl.") into links table", get_class($this));
            else
                $logger->logStatus("Did NOT insert ".$u." (".$eurl.") into links table", get_class($this));
                
        }
        
    }
    
    private function fetchAndAddTweetRepliedTo($tid, $td, $api, $logger, $cfg) {
        //fetch tweet from Twitter and add to DB
        $status_message = "";
        $tweet_deets = str_replace("[id]", $tid, $api->cURL_source['show_tweet']);
        list($cURL_status, $twitter_data) = $api->apiRequest($tweet_deets, $logger);
        
        if ($cURL_status == 200) {
            try {
                $tweets = $api->parseXML($twitter_data);
                foreach ($tweets as $tweet) {
                    if ($td->addTweet($tweet, $this->owner_object, $logger) > 0) {
                        $status_message = 'Added replied to tweet ID '.$tid." to database.";
                        //expand and insert links contained in tweet
                        $this->processTweetURLs($tweet, $cfg, $logger);
                    }
                }
            }
            catch(Exception $e) {
                $status_message = 'Could not parse tweet XML for $id';
            }
        } elseif ($cURL_status == 404 || $cURL_status == 403) {
            try {
                $e = $api->parseError($twitter_data);
                $td = new TweetErrorDAO();
                $td->insertError($tid, $cURL_status, $e['error'], $this->owner_object->id);
                $status_message = 'Error saved to tweets.';
            }
            catch(Exception $e) {
                $status_message = 'Could not parse tweet XML for $tid';
            }
        }
        $logger->logStatus($status_message, get_class($this));
        $status_message = "";
    }
    
    function fetchInstanceUserReplies($cfg, $api, $logger) {
        $status_message = "";
        // Get owner's replies
        if ($api->available_api_calls_for_crawler > 0) {
            $got_newest_replies = false;
            $continue_fetching = true;
            
            while ($api->available && $api->available_api_calls_for_crawler > 0 && $continue_fetching) {
                # Get the most recent replies
                $replies = str_replace("[id]", $cfg->twitter_username, $api->cURL_source['replies']);
                $args = array();
                $args['count'] = 200;
                
                if ($got_newest_replies) {
                    $this->last_page_fetched_replies++;
                    $args['page'] = $this->last_page_fetched_replies;
                }
                
                list($cURL_status, $twitter_data) = $api->apiRequest($replies, $logger, $args);
                if ($cURL_status > 200) {
                    $continue_fetching = false;
                } else {
                    try {
                        $count = 0;
                        $tweets = $api->parseXML($twitter_data);
                        if (count($tweets) == 0 && $got_newest_replies) {# you're paged back and no new tweets
                            $this->last_page_fetched_replies = 1;
                            $continue_fetching = false;
                            $this->instance->is_archive_loaded_replies = true;
                            $status_message = 'Paged back bu