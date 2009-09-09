<?php 
class Crawler {
    var $instance;
	var $logger;
	var $api;
    var $owner_object;
    var $ud;
	var $db;
    
    function Crawler($instance, $logger, $api, $db) {
        $this->instance = $instance;
		$this->api = $api;
		$this->db = $db;
		$this->logger = $logger;
        $this->ud = new UserDAO($this->db, $this->logger);
    }
    
    function fetchInstanceUserInfo() {
        // Get owner user details and save them to DB
        $status_message = "";
        $owner_profile = str_replace("[id]", $this->instance->twitter_username, $this->api->cURL_source['show_user']);
        list($cURL_status, $twitter_data) = $this->api->apiRequest($owner_profile, $this->logger);
        
        if ($cURL_status == 200) {
            try {
                $users = $this->api->parseXML($twitter_data);
                foreach ($users as $user)
                    $this->owner_object = new User($user, 'Owner Status');
                    
                if (isset($this->owner_object)) {
                    $status_message = 'Owner info set.';
                    $this->ud->updateUser($this->owner_object, $this->logger);
                } else {
                    $status_message = 'Owner was not set.';
                }
            }
            catch(Exception $e) {
                $status_message = 'Could not parse profile XML for $this->owner_object->username';
            }
        } else {
            $status_message = 'cURL status is not 200';
        }
        $this->logger->logStatus($status_message, get_class($this));
    }
    
    function fetchInstanceUserTweets($lurl, $fa) {
        // Get owner's tweets
        $status_message = "";
        $got_latest_page_of_tweets = false;
        $continue_fetching = true;
        
        while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $this->owner_object->tweet_count > $this->instance->total_tweets_in_system && $continue_fetching) {
        
            $recent_tweets = str_replace("[id]", $this->owner_object->username, $this->api->cURL_source['user_timeline']);
            $args = array();
            $args["count"] = 200;
            $last_page_of_tweets = round($this->api->archive_limit / 200) + 1;
            
            //set page and since_id params for API call
            if ($got_latest_page_of_tweets && $this->owner_object->tweet_count != $this->instance->total_tweets_in_system && $this->instance->total_tweets_in_system < $this->api->archive_limit) {
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
            
            list($cURL_status, $twitter_data) = $this->api->apiRequest($recent_tweets, $this->logger, $args);
            if ($cURL_status == 200) {
                # Parse the XML file
                try {
                    $count = 0;
                    $tweets = $this->api->parseXML($twitter_data);
                    
                    $td = new TweetDAO($this->db, $this->logger);
                    foreach ($tweets as $tweet) {
                    
                        if ($td->addTweet($tweet, $this->owner_object, $this->logger) > 0) {
                            $count = $count + 1;
                            $this->instance->total_tweets_in_system = $this->instance->total_tweets_in_system + 1;
                            
                            //expand and insert links contained in tweet
                            $this->processTweetURLs($tweet, $lurl, $fa);
                            
                        }
                        if ($tweet['status_id'] > $this->instance->last_status_id)
                            $this->instance->last_status_id = $tweet['status_id'];
                            
                    }
                    $status_message .= count($tweets)." tweet(s) found and $count saved";
                    $this->logger->logStatus($status_message, get_class($this));
                    $status_message = "";
                    
                    //if you've got more than the Twitter API archive limit, stop looking for more tweets
                    if ($this->instance->total_tweets_in_system >= $this->api->archive_limit) {
                        $this->instance->last_page_fetched_tweets = 1;
                        $continue_fetching = false;
                        $status_message = "More than Twitter cap of ".$this->api->archive_limit." already in system, moving on.";
                        $this->logger->logStatus($status_message, get_class($this));
                        $status_message = "";
                    }

                    
                    if ($this->owner_object->tweet_count == $this->instance->total_tweets_in_system)
                        $this->instance->is_archive_loaded_tweets = true;
                        
                    $status_message .= $this->instance->total_tweets_in_system." in system; ".$this->owner_object->tweet_count." by owner";
                    $this->logger->logStatus($status_message, get_class($this));
                    $status_message = "";
                    
                }
                catch(Exception $e) {
                    $status_message = 'Could not parse tweet XML for $this->twitter_username';
                    $this->logger->logStatus($status_message, get_class($this));
                    $status_message = "";
                    
                }
                
                $got_latest_page_of_tweets = true;
            }
        }
        
        if ($this->owner_object->tweet_count == $this->instance->total_tweets_in_system)
            $status_message .= "All of ".$this->owner_object->user_name."'s tweets are in the system; Stopping tweet fetch.";

            
        $this->logger->logStatus($status_message, get_class($this));
        $status_message = "";
        
    }
    
    private function processTweetURLs($tweet, $lurl, $fa) {
    
        $ld = new LinkDAO($this->db, $this->logger);
        
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
            } elseif ($fa->api_key != null && substr($u, 0, strlen('http://flic.kr/p/')) == 'http://flic.kr/p/') {
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
                $this->logger->logStatus("Inserted ".$u." (".$eurl.") into links table", get_class($this));
            else
                $this->logger->logStatus("Did NOT insert ".$u." (".$eurl.") into links table", get_class($this));
                
        }
        
    }
    
    private function fetchAndAddTweetRepliedTo($tid, $td, $lurl, $fa) {
        //fetch tweet from Twitter and add to DB
        $status_message = "";
        $tweet_deets = str_replace("[id]", $tid, $this->api->cURL_source['show_tweet']);
        list($cURL_status, $twitter_data) = $this->api->apiRequest($tweet_deets, $this->logger);
        
        if ($cURL_status == 200) {
            try {
                $tweets = $this->api->parseXML($twitter_data);
                foreach ($tweets as $tweet) {
                    if ($td->addTweet($tweet, $this->owner_object, $this->logger) > 0) {
                        $status_message = 'Added replied to tweet ID '.$tid." to database.";
                        //expand and insert links contained in tweet
                        $this->processTweetURLs($tweet, $lurl, $fa);
                    }
                }
            }
            catch(Exception $e) {
                $status_message = 'Could not parse tweet XML for $id';
            }
        } elseif ($cURL_status == 404 || $cURL_status == 403) {
            try {
                $e = $this->api->parseError($twitter_data);
                $td = new TweetErrorDAO($this->db, $this->logger);
                $td->insertError($tid, $cURL_status, $e['error'], $this->owner_object->id);
                $status_message = 'Error saved to tweets.';
            }
            catch(Exception $e) {
                $status_message = 'Could not parse tweet XML for $tid';
            }
        }
        $this->logger->logStatus($status_message, get_class($this));
        $status_message = "";
    }
    
    function fetchInstanceUserReplies($lurl, $fa) {
        $status_message = "";
        // Get owner's replies
        if ($this->api->available_api_calls_for_crawler > 0) {
            $got_newest_replies = false;
            $continue_fetching = true;
            
            while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
                # Get the most recent replies
                $replies = str_replace("[id]", $this->owner_object->username, $this->api->cURL_source['replies']);
                $args = array();
                $args['count'] = 200;
                
                if ($got_newest_replies) {
                    $this->last_page_fetched_replies++;
                    $args['page'] = $this->last_page_fetched_replies;
                }
                
                list($cURL_status, $twitter_data) = $this->api->apiRequest($replies, $this->logger, $args);
                if ($cURL_status > 200) {
                    $continue_fetching = false;
                } else {
                    try {
                        $count = 0;
                        $tweets = $this->api->parseXML($twitter_data);
                        if (count($tweets) == 0 && $got_newest_replies) {# you're paged back and no new tweets
                            $this->last_page_fetched_replies = 1;
                            $continue_fetching = false;
                            $this->instance->is_archive_loaded_replies = true;
                            $status_message = 'Paged back but not finding new replies; moving on.';
                            $this->logger->logStatus($status_message, get_class($this));
                            $status_message = "";
                        }

                        
                        $td = new TweetDAO($this->db, $this->logger);
                        $count = 0;
                        foreach ($tweets as $tweet) {
                            if ($td->addTweet($tweet, $this->owner_object, $this->logger) > 0) {
                                $count++;
                                //expand and insert links contained in tweet
                                $this->processTweetURLs($tweet, $lurl, $fa);
                                if ($tweet['user_id'] != $this->owner_object->id) { //don't update owner info from reply
                                    $u = new User($tweet, 'Replies');
                                    $this->ud->updateUser($u, $this->logger);
                                }
                                
                            }
                            
                        }
                        $status_message .= count($tweets)." replies found and $count saved";
                        $this->logger->logStatus($status_message, get_class($this));
                        $status_message = "";
                        
                        $got_newest_replies = true;
                        
                        $this->logger->logStatus($status_message, get_class($this));
                        $status_message = "";
                        
                        if ($got_newest_replies && $this->instance->is_archive_loaded_replies) {
                            $continue_fetching = false;
                            $status_message .= 'Retrieved newest replies; Reply archive loaded; Stopping reply fetch.';
                            $this->logger->logStatus($status_message, get_class($this));
                            $status_message = "";
                        }
                        
                    }
                    catch(Exception $e) {
                        $status_message = 'Could not parse replies XML for $this->owner_object->username';
                        $this->logger->logStatus($status_message, get_class($this));
                        $status_message = "";
                    }
                }
                
            }
        } else {
            $status_message = 'Crawler API call limit exceeded.';
        }
        
        $this->logger->logStatus($status_message, get_class($this));
        $status_message = "";
    }
    
    private function fetchInstanceUserFollowersByIDs() {
        $continue_fetching = true;
        $last_page_fetched_follower_ids = $this->instance->last_page_fetched_followers;
        $status_message = "";
        
        while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
        
            $last_page_fetched_follower_ids = $last_page_fetched_follower_ids + 1;
            
            $args = array();
            $follower_ids = str_replace("[id]", $this->owner_object->username, $this->api->cURL_source['followers_ids']);
            $args['page'] = $last_page_fetched_follower_ids;
            
            list($cURL_status, $twitter_data) = $this->api->apiRequest($follower_ids, $this->logger, $args);
            
            if ($cURL_status > 200) {
                $continue_fetching = false;
            } else {
                $fd = new FollowDAO($this->db, $this->logger);
                
                try {
                    $ids = $this->api->parseXML($twitter_data);
                    $status_message = "Page ".$last_page_fetched_follower_ids." has ".count($ids)." follower IDs. ";
                    
                    if (count($ids) == 0) {
                        $this->instance->is_archive_loaded_follows = true;
                        $continue_fetching = false;
                        $last_page_fetched_follower_ids = 0;
                    }
                    
                    $updated_follow_count = 0;
                    $inserted_follow_count = 0;
                    foreach ($ids as $id) {
                    
                        # add/update follow relationship
                        if ($fd->followExists($this->instance->twitter_user_id, $id['id'])) {
                            //update it
                            if ($fd->update($this->instance->twitter_user_id, $id['id']))
                                $updated_follow_count = $updated_follow_count + 1;
                        } else {
                            //insert it
                            if ($fd->insert($this->instance->twitter_user_id, $id['id']))
                                $inserted_follow_count = $inserted_follow_count + 1;
                        }
                    }
                    
                    $status_message .= "$updated_follow_count existing follows updated; $inserted_follow_count new follows inserted.";
                }
                catch(Exception $e) {
                    $status_message = 'Could not parse follower ID XML for $crawler_twitter_username';
                }
                $this->logger->logStatus($status_message, get_class($this));
                $status_message = "";
                
            }
            
            $this->logger->logStatus($status_message, get_class($this));
            $status_message = "";
            
        }
        
        $this->instance->last_page_fetched_followers = $last_page_fetched_follower_ids;
    }
    
    function fetchInstanceUserFollowers() {
        $status_message = "";
        // Get owner's followers: Page back only if more than 2% of follows are missing from database
        // See how many are missing from last run
        if ($this->instance->is_archive_loaded_follows) { //all pages have been loaded
            $this->logger->logStatus("Follower archive marked as loaded", get_class($this));
            
            //find out how many new follows owner has compared to what's in db
            $new_follower_count = $this->owner_object->follower_count - $this->instance->total_follows_in_system;
            $status_message = "New follower count is ".$this->owner_object->follower_count." and system has ".$this->instance->total_follows_in_system."; ".$new_follower_count." new follows to load";
            $this->logger->logStatus($status_message, get_class($this));
            
            if ($new_follower_count > 0) {
                $this->logger->logStatus("Fetching follows via IDs", get_class($this));
                $this->fetchInstanceUserFollowersByIDs();
            }
        } else {
            $this->logger->logStatus("Follower archive is not loaded; fetch should begin.", get_class($this));
        }

        
        # Fetch follower pages
        $continue_fetching = true;
        while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching && !$this->instance->is_archive_loaded_follows) {
        
            $this->instance->last_page_fetched_followers = $this->instance->last_page_fetched_followers + 1;
            
            $follower_ids = str_replace("[id]", $this->owner_object->username, $this->api->cURL_source['followers']);
            $args = array();
            $args['page'] = $this->instance->last_page_fetched_followers;
            
            list($cURL_status, $twitter_data) = $this->api->apiRequest($follower_ids, $this->logger, $args);
            
            if ($cURL_status > 200) {
                $continue_fetching = false;
            } else {
                $fd = new FollowDAO($this->db, $this->logger);
                
                try {
                    $users = $this->api->parseXML($twitter_data);
                    $status_message .= "Page ".$this->instance->last_page_fetched_followers.": ".count($users)." follows ready to update. ";
                    
                    if (count($users) == 0) {
                        $this->instance->last_page_fetched_followers = 0;
                        $this->instance->is_archive_loaded_follows = true;
                    }
                    
                    $updated_follow_count = 0;
                    $inserted_follow_count = 0;
                    foreach ($users as $u) {
                        $utu = new User($u, 'Follows');
                        $this->ud->updateUser($utu, $this->logger);
                        
                        # add/update follow relationship
                        if ($fd->followExists($this->instance->twitter_user_id, $utu->user_id)) {
                            //update it
                            if ($fd->update($this->instance->twitter_user_id, $utu->user_id))
                                $updated_follow_count++;
                        } else {
                            //insert it
                            if ($fd->insert($this->instance->twitter_user_id, $utu->user_id))
                                $inserted_follow_count++;
                        }
                    }
                    
                    $status_message .= "$updated_follow_count existing follows updated; $inserted_follow_count new follows inserted.";
                }
                catch(Exception $e) {
                    $status_message = 'Could not parse followers XML for $crawler_twitter_username';
                }
                $this->logger->logStatus($status_message, get_class($this));
                $status_message = "";
                
            }
            
            $this->logger->logStatus($status_message, get_class($this));
            $status_message = "";
            
        }
        
    }
    
    function fetchInstanceUserFriends() {
        $fd = new FollowDAO($this->db, $this->logger);
        $this->instance->total_friends_in_system = $fd->getTotalFriends($this->owner_object->id);
        
        if ($this->instance->total_friends_in_system < $this->owner_object->friend_count) {
            $this->instance->is_archive_loaded_friends = false;
            $this->logger->logStatus($this->instance->total_friends_in_system." friends in system, ".$this->owner_object->friend_count." friends according to Twitter; Friend archive is not loaded", get_class($this));
        } else {
            $this->instance->is_archive_loaded_friends = true;
            $this->logger->logStatus("Friend archive loaded", get_class($this));
        }
        
        $status_message = "";
        $this->instance->last_page_fetched_friends = 0;
        # Fetch friend pages
        $continue_fetching = true;
        while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching && !$this->instance->is_archive_loaded_friends) {
        
            $this->instance->last_page_fetched_friends = $this->instance->last_page_fetched_friends + 1;
            
            $friend_ids = str_replace("[id]", $this->owner_object->username, $this->api->cURL_source['following']);
            $args = array();
            $args['page'] = $this->instance->last_page_fetched_friends;
            
            list($cURL_status, $twitter_data) = $this->api->apiRequest($friend_ids, $this->logger, $args);
            
            if ($cURL_status > 200) {
                $continue_fetching = false;
            } else {
            
                try {
                    $status_message = "Parsing XML. ";
                    $users = $this->api->parseXML($twitter_data);
                    $status_message .= "Page ".$this->instance->last_page_fetched_friends.": ".count($users)." friends queued to update. ";
                    
                    $updated_follow_count = 0;
                    $inserted_follow_count = 0;
                    
                    if (count($users) == 0) {
                        $this->instance->last_page_fetched_friends = 0;
                        $this->instance->is_archive_loaded_friends = true;
                    }
                    
                    foreach ($users as $u) {
                        $utu = new User($u, 'Friends');
                        $this->ud->updateUser($utu, $this->logger);
                        
                        # add/update follow relationship
                        if ($fd->followExists($utu->user_id, $this->instance->twitter_user_id)) {
                            //update it
                            if ($fd->update($utu->user_id, $this->instance->twitter_user_id))
                                $updated_follow_count++;
                        } else {
                            //insert it
                            if ($fd->insert($utu->user_id, $this->instance->twitter_user_id))
                                $inserted_follow_count++;
                        }
                        
                    }
                    
                    $status_message .= "$updated_follow_count existing friends updated; $inserted_follow_count new friends inserted.";
                }
                catch(Exception $e) {
                    $status_message = 'Could not parse friends XML for $crawler_twitter_username';
                }
                $this->logger->logStatus($status_message, get_class($this));
                $status_message = "";
                
            }
            
            $this->logger->logStatus($status_message, get_class($this));
            $status_message = "";
            
        }
        
    }
    
    function fetchFriendTweetsAndFriends($lurl, $fa) {
        $fd = new FollowDAO($this->db, $this->logger);
        $td = new TweetDAO($this->db, $this->logger);
        $ud = new UserDAO($this->db, $this->logger);
        
        $continue_fetching = true;
        while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
            $stale_friend = $fd->getStalestFriend($this->owner_object->id);
            if ($stale_friend != null) {
                $this->logger->logStatus($stale_friend->user_name." is friend most need of update", get_class($this));
                $stale_friend_tweets = str_replace("[id]", $stale_friend->user_name, $this->api->cURL_source['user_timeline']);
                $args = array();
                $args["count"] = 200;
                
                if ($stale_friend->last_status_id > 0) {
                    $args['since_id'] = $stale_friend->last_status_id;
                }
                
                list($cURL_status, $twitter_data) = $this->api->apiRequest($stale_friend_tweets, $this->logger, $args);
                
                if ($cURL_status == 200) {
                    try {
                        $count = 0;
                        $tweets = $this->api->parseXML($twitter_data);
                        
                        if (count($tweets) > 0) {
                            foreach ($tweets as $tweet) {
                            
                                if ($td->addTweet($tweet, $stale_friend, $this->logger) > 0) {
                                    $count++;
                                    //expand and insert links contained in tweet
                                    $this->processTweetURLs($tweet, $lurl, $fa);
                                }
                                //Update stale_friend values here
                                $stale_friend->full_name = $tweet['full_name'];
                                $stale_friend->avatar = $tweet['avatar'];
                                $stale_friend->location = $tweet['location'];
                                $stale_friend->description = $tweet['description'];
                                $stale_friend->url = $tweet['url'];
                                $stale_friend->is_protected = $tweet['is_protected'];
                                $stale_friend->follower_count = $tweet['follower_count'];
                                $stale_friend->friend_count = $tweet['friend_count'];
                                $stale_friend->tweet_count = $tweet['tweet_count'];
                                $stale_friend->joined = date_format(date_create($tweet['joined']), "Y-m-d H:i:s");
                                
                                if ($tweet['status_id'] > $stale_friend->last_status_id) {
                                    $stale_friend->last_status_id = $tweet['status_id'];
                                }
                                $ud->updateUser($stale_friend, $this->logger);
                            }
                        } else {
                            $this->fetchAndAddUser($stale_friend->id, "Friends");
                        }
                        
                        $this->logger->logStatus(count($tweets)." tweet(s) found for ".$stale_friend->username." and $count saved", get_class($this));
                    }
                    catch(Exception $e) {
                        $this->logger->logStatus('Could not parse friends XML for $stale_friend->username', get_class($this));
                    }
                    $this->fetchUserFriends($stale_friend->id, $fd);
                } elseif ($cURL_status == 401 || $cURL_status == 404) {
                    try {
                        $e = $this->api->parseError($twitter_data);
                        $ued = new UserErrorDAO($this->db, $this->logger);
                        $ued->insertError($stale_friend->id, $cURL_status, $e['error'], $this->owner_object->id);
                        $this->logger->logStatus('User error saved', get_class($this));
                    }
                    catch(Exception $e) {
                        $this->logger->logStatus('Could not parse timeline error for $stale_friend->username', get_class($this));
                    }
                }
            } else {
                $this->logger->logStatus('No friend staler than 1 day', get_class($this));
                $continue_fetching = false;
            }
            
        }
    }
    
    function fetchStrayRepliedToTweets($lurl, $fa) {
        $td = new TweetDAO($this->db, $this->logger);
        $strays = $td->getStrayRepliedToTweets($this->owner_object->id);
        $status_message = count($strays).' stray replied-to tweets to load.';
        $this->logger->logStatus($status_message, get_class($this));
        
        foreach ($strays as $s) {
            if ($this->api->available && $this->api->available_api_calls_for_crawler > 0)
                $this->fetchAndAddTweetRepliedTo($s['in_reply_to_status_id'], $td, $lurl, $fa);
        }
    }
    
    function fetchUnloadedFollowerDetails() {
        $fd = new FollowDAO($this->db, $this->logger);
        $strays = $fd->getUnloadedFollowerDetails($this->owner_object->id);
        $status_message = count($strays).' unloaded follower details to load.';
        $this->logger->logStatus($status_message, get_class($this));
        
        foreach ($strays as $s) {
            if ($this->api->available && $this->api->available_api_calls_for_crawler > 0)
                $this->fetchAndAddUser($s['follower_id'], "Follower IDs");
        }
    }
    
    private function fetchUserFriends($uid, $fd) {
        $continue_fetching = true;
        $last_page_fetched_friend_ids = 0;
        $status_message = "";
        
        while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
        
            $last_page_fetched_friend_ids = $last_page_fetched_friend_ids + 1;
            
            $args = array();
            $friend_ids = str_replace("[id]", $uid, $this->api->cURL_source['following_ids']);
            $args['page'] = $last_page_fetched_friend_ids;
            
            list($cURL_status, $twitter_data) = $this->api->apiRequest($friend_ids, $this->logger, $args);
            
            if ($cURL_status > 200) {
                $continue_fetching = false;
            } else {
            
                try {
                    $ids = $this->api->parseXML($twitter_data);
                    $status_message = "Page ".$last_page_fetched_friend_ids." has ".count($ids)." friend IDs. ";
                    
                    if (count($ids) == 0)
                        $continue_fetching = false;
                        
                    $updated_follow_count = 0;
                    $inserted_follow_count = 0;
                    foreach ($ids as $id) {
                    
                        # add/update follow relationship
                        if ($fd->followExists($id['id'], $uid)) {
                            //update it
                            if ($fd->update($id['id'], $uid))
                                $updated_follow_count++;
                        } else {
                            //insert it
                            if ($fd->insert($id['id'], $uid))
                                $inserted_follow_count++;
                        }
                    }
                    
                    $status_message .= "$updated_follow_count existing follows updated; $inserted_follow_count new follows inserted.";
                }
                catch(Exception $e) {
                    $status_message = 'Could not parse follower ID XML for $uid';
                }
                $this->logger->logStatus($status_message, get_class($this));
                $status_message = "";
                
            }
            
            $this->logger->logStatus($status_message, get_class($this));
            $status_message = "";
            
        }
        
    }
    
    private function fetchAndAddUser($fid, $source) {
        //fetch user from Twitter and add to DB
        $status_message = "";
        $u_deets = str_replace("[id]", $fid, $this->api->cURL_source['show_user']);
        list($cURL_status, $twitter_data) = $this->api->apiRequest($u_deets, $this->logger);
        
        if ($cURL_status == 200) {
            try {
                $user_arr = $this->api->parseXML($twitter_data);
                $user = new User($user_arr[0], $source);
                $this->ud->updateUser($user, $this->logger);
                $status_message = 'Added/updated user '.$user->username." in database";
            }
            catch(Exception $e) {
                $status_message = 'Could not parse tweet XML for $uid';
            }
        } elseif ($cURL_status == 404) {
            try {
                $e = $this->api->parseError($twitter_data);
                $ued = new UserErrorDAO($this->db, $this->logger);
                $ued->insertError($fid, $cURL_status, $e['error'], $this->owner_object->id);
                $status_message = 'User error saved.';
                
            }
            catch(Exception $e) {
                $status_message = 'Could not parse tweet XML for $uid';
            }
            
        }
        $this->logger->logStatus($status_message, get_class($this));
        $status_message = "";
        
    }
    
    // For each API call left, grab oldest follow relationship, check if it exists, and update table
    function cleanUpFollows() {
        $fd = new FollowDAO($this->db, $this->logger);
        $continue_fetching = true;
        while ($this->api->available && $this->api->available_api_calls_for_crawler > 0 && $continue_fetching) {
        
            $oldfollow = $fd->getOldestFollow();
            
            $friendship_call = $this->api->cURL_source['show_friendship'];
            $args = array();
            $args["source_id"] = $oldfollow["followee_id"];
            $args["target_id"] = $oldfollow["follower_id"];
            
            list($cURL_status, $twitter_data) = $this->api->apiRequest($friendship_call, $this->logger, $args);
            
            if ($cURL_status == 200) {
                try {
                    $friendship = $this->api->parseXML($twitter_data);
                    if ($friendship['source_follows_target'] == 'true')
                        $fd->update($oldfollow["followee_id"], $oldfollow["follower_id"]);
                    else
                        $fd->deactivate($oldfollow["followee_id"], $oldfollow["follower_id"]);
                        
                    if ($friendship['target_follows_source'] == 'true')
                        $fd->update($oldfollow["follower_id"], $oldfollow["followee_id"]);
                    else
                        $fd->deactivate($oldfollow["follower_id"], $oldfollow["followee_id"]);

                        
                }
                catch(Exception $e) {
                    $status_message = 'Could not parse friendship XML';
                }
            } else {
                $continue_fetching = false;
            }
        }
    }
}
?>
