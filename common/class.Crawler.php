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
		$owner_profile 	= str_replace("[id]",$cfg->twitter_username,$api->cURL_source['show_user']);	
		list($cURL_status,$twitter_data) = $api->apiRequest($owner_profile, $logger);

		if ($cURL_status == 200) { 
			try { 
				$users = $api->parseXML($twitter_data);
				foreach($users as $user) 
					$this->owner_object = new User($user, 'Owner Status');
					
				if ( isset($this->owner_object) ) {
					$status_message = 'Owner info set.'; 
					$this->ud->updateUser($this->owner_object, $logger);
				} else {
					$status_message = 'Owner was not set.'; 
				}
			} catch (Exception $e) { 
				$status_message = 'Could not parse profile XML for $cfg->twitter_username'; 
			} 
		} else {
			$status_message = 'cURL status is not 200'; 
		}
		$logger->logStatus($status_message, get_class($this) );		
		$status_message = "";
	}
	
	function fetchInstanceUserTweets($cfg, $api, $logger) {
		// Get owner's tweets
		$status_message = "";
		$got_latest_page_of_tweets = false;
		$continue_fetching = true;

		while ( $api->available && $api->available_api_calls_for_crawler > 0 && $this->owner_object->tweet_count > $this->instance->total_tweets_in_system && $continue_fetching ) {	

			$recent_tweets 		= str_replace("[id]",$cfg->twitter_username,$api->cURL_source['user_timeline']);
			$args = array();
			$args["count"] = 200;			
			$last_page_of_tweets = round($cfg->archive_limit/200)+1;

			//set page and since_id params for API call
			if ( $got_latest_page_of_tweets && 
				 $this->owner_object->tweet_count != $this->instance->total_tweets_in_system && 
				 $this->instance->total_tweets_in_system < $cfg->archive_limit) {
				if ($this->instance->last_page_fetched_tweets < $last_page_of_tweets )
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

			list($cURL_status,$twitter_data) = $api->apiRequest($recent_tweets, $logger, $args);
			if ( $cURL_status == 200) {
				# Parse the XML file
				try { 
					$count = 0;
					$tweets = $api->parseXML($twitter_data);

					$td = new TweetDAO;
					$ld = new LinkDAO;
					foreach($tweets as $tweet) {

						if ( $td->addTweet($tweet, $this->owner_object, $logger) > 0 ) {
							$count = $count + 1;
							$this->instance->total_tweets_in_system = $this->instance->total_tweets_in_system + 1;

							//tweet inserted; process its URLs
							$urls = Tweet::extractURLs($tweet['tweet_text']);
							foreach ($urls as $u) {
								if ( $ld->insert($u, $tweet['status_id']) )
									$logger->logStatus("Inserted ".$u." into links table", get_class($this) );		
								else
									$logger->logStatus("Did NOT insert ".$u." into links table", get_class($this) );
							}

						}
						if ( $tweet['status_id'] > $this->instance->last_status_id ) 
							$this->instance->last_status_id = $tweet['status_id'];

					}
					$status_message .= count($tweets) ." tweet(s) found and $count saved"; 
					$logger->logStatus($status_message, get_class($this) );		
					$status_message = "";
					
					//if you've got more than the Twitter API archive limit, stop looking for more tweets
					if ( $this->instance->total_tweets_in_system >= $cfg->archive_limit  ) {
						$this->instance->last_page_fetched_tweets = 1;
						$continue_fetching = false;
						$status_message = "More than Twitter cap of ".$cfg->archive_limit." already in system, moving on."; 
						$logger->logStatus($status_message, get_class($this) );		
						$status_message = "";
					}


					if ( $this->owner_object->tweet_count == $this->instance->total_tweets_in_system)  
						$this->instance->is_archive_loaded_tweets = true;

					$status_message .= $this->instance->total_tweets_in_system." in system; ".$this->owner_object->tweet_count." by owner";
					$logger->logStatus($status_message, get_class($this) );		
					$status_message = "";

				} catch (Exception $e) { 
					$status_message = 'Could not parse tweet XML for $this->twitter_username'; 
					$logger->logStatus($status_message, get_class($this) );		
					$status_message = "";

				}
				 
				$got_latest_page_of_tweets = true;
			}
		}

		if ( $this->owner_object->tweet_count == $this->instance->total_tweets_in_system ) 
			$status_message .= "All of ".$this->owner_object->user_name."'s tweets are in the system; Stopping tweet fetch.";


		$logger->logStatus($status_message, get_class($this) );		
		$status_message = "";
		
	}
	
	private function fetchAndAddTweetRepliedTo($tid, $td, $api, $logger) {
		//fetch tweet from Twitter and add to DB
		$status_message = "";
		$tweet_deets 	= str_replace("[id]",$tid,$api->cURL_source['show_tweet']);	
		list($cURL_status,$twitter_data) = $api->apiRequest($tweet_deets, $logger);

		if ($cURL_status == 200) { 
			try { 
				$tweets = $api->parseXML($twitter_data);
				foreach($tweets as $tweet) {
					if ( $td->addTweet($tweet, $this->owner_object, $logger) > 0 ) {
						$status_message = 'Added replied to tweet ID '.$tid." to database."; 
					}
				}
			} catch (Exception $e) { 
				$status_message = 'Could not parse tweet XML for $id'; 
			} 
		} elseif ( $cURL_status == 404 || $cURL_status == 403 ) {
			try { 
				$e = $api->parseError($twitter_data);
				$td = new TweetErrorDAO();
				$td->insertError($tid, $cURL_status, $e['error'],$this->owner_object->id);
				$status_message = 'Error saved to tweets.'; 
			} catch (Exception $e) { 
				$status_message = 'Could not parse tweet XML for $tid'; 
			} 
		} 
		$logger->logStatus($status_message, get_class($this) );		
		$status_message = "";
	}

	function fetchInstanceUserReplies($cfg, $api, $logger) {
		$status_message="";
		// Get owner's replies
		if ( $api->available_api_calls_for_crawler > 0 ) {
			$got_newest_replies = false;
			$continue_fetching = true;

			while ( $api->available && $api->available_api_calls_for_crawler > 0 && $continue_fetching ) {	
				# Get the most recent replies
				$replies 		= str_replace("[id]",$cfg->twitter_username,$api->cURL_source['replies']);
				$args = array();
				$args['count'] = 200;

				if ( $got_newest_replies ) {
					$this->last_page_fetched_replies++;
					$args['page'] = $this->last_page_fetched_replies;	
				}

				list($cURL_status,$twitter_data) = $api->apiRequest($replies, $logger, $args);
				if ($cURL_status > 200) { 
					$continue_fetching = false;
				} else {
					try { 
						$count = 0;
						$tweets = $api->parseXML($twitter_data);
						if ( count($tweets) == 0 && $got_newest_replies ) {# you're paged back and no new tweets
							$this->last_page_fetched_replies = 1;
							$continue_fetching=false;
							$this->instance->is_archive_loaded_replies = true;
							$status_message = 'Paged back but not finding new replies; moving on.'; 
							$logger->logStatus($status_message, get_class($this) );		
							$status_message = "";
						}
						

						$td = new TweetDAO;
						$count = 0;
						foreach($tweets as $tweet) {
							if ( $td->addTweet($tweet, $this->owner_object, $logger) > 0 ) {
								$count ++;

								if ( $tweet['user_id'] != $cfg->twitter_user_id) { //don't update owner info from reply
									$u = new User($tweet, 'Replies');
									$this->ud->updateUser($u, $logger);
								}

							}

						}
						$status_message .=  count($tweets) ." replies found and $count saved";
						$logger->logStatus($status_message, get_class($this) );
						$status_message = "";
						 
						$got_newest_replies = true;

						$logger->logStatus($status_message, get_class($this) );
						$status_message = "";

						if ( $got_newest_replies && $this->instance->is_archive_loaded_replies ) {
							$continue_fetching = false;
							$status_message .= 'Retrieved newest replies; Reply archive loaded; Stopping reply fetch.'; 	
							$logger->logStatus($status_message, get_class($this) );
							$status_message = "";
						}					

					} catch (Exception $e) { 
						$status_message = 'Could not parse replies XML for $cfg->twitter_username'; 
						$logger->logStatus($status_message, get_class($this) );
						$status_message = "";
					} 
				}

			}
		} else {
			$status_message = 'Crawler API call limit exceeded.'; 
		}	

		$logger->logStatus($status_message, get_class($this) );
		$status_message = "";
	}
	
	private function fetchInstanceUserFollowersByIDs($cfg, $api, $logger) {
		$continue_fetching = true;
		$last_page_fetched_follower_ids = $this->instance->last_page_fetched_followers;
		$status_message = "";
		
		while ( $api->available && 
			$api->available_api_calls_for_crawler > 0 && 
			$continue_fetching ) {

			$last_page_fetched_follower_ids = $last_page_fetched_follower_ids+1;

			$args = array();
			$follower_ids 	= str_replace("[id]",$cfg->twitter_username,$api->cURL_source['followers_ids']);
			$args['page'] = $last_page_fetched_follower_ids;

			list($cURL_status,$twitter_data) = $api->apiRequest($follower_ids, $logger, $args);

			if ($cURL_status > 200) { 
				$continue_fetching = false;
			} else {
				$fd = new FollowDAO();

				try { 
					$ids = $api->parseXML($twitter_data);
					$status_message = "Page ".$last_page_fetched_follower_ids." has ".count($ids) ." follower IDs. ";		

					if ( count($ids) == 0 ) {
						$this->instance->is_archive_loaded_follows = true;
						$continue_fetching = false;
						$last_page_fetched_follower_ids = 0;
					}

					$updated_follow_count = 0;
					$inserted_follow_count = 0;
					foreach($ids as $id) {
						
						# add/update follow relationship
						if ( $fd->followExists($this->instance->twitter_user_id,$id['id'] ) ) {
							//update it
							if ( $fd->update( $this->instance->twitter_user_id,$id['id'] ) )
								$updated_follow_count = $updated_follow_count + 1;
						} else {
							//insert it
							if ( $fd->insert(  $this->instance->twitter_user_id,$id['id'] ))
								$inserted_follow_count = $inserted_follow_count + 1;
						}
					}

					$status_message .= "$updated_follow_count existing follows updated; $inserted_follow_count new follows inserted."; 
				} catch (Exception $e) { 
					$status_message = 'Could not parse follower ID XML for $crawler_twitter_username'; 
				} 
				$logger->logStatus($status_message, get_class($this) );
				$status_message = "";
				
			}

			$logger->logStatus($status_message, get_class($this) );
			$status_message = "";

		}
		
		$this->instance->last_page_fetched_followers = $last_page_fetched_follower_ids;
	}
	
	function fetchInstanceUserFollowers($cfg, $api, $logger) {
		$status_message = "";
		// Get owner's followers: Page back only if more than 2% of follows are missing from database
		// See how many are missing from last run
		if ( $this->instance->is_archive_loaded_follows  ) { //all pages have been loaded
			$logger->logStatus("Follower archive marked as loaded", get_class($this) );
			
			//find out how many new follows owner has compared to what's in db
			$new_follower_count = $this->owner_object->follower_count - $this->instance->total_follows_in_system;
			$status_message = "New follower count is ". $this->owner_object->follower_count." and system has ".$this->instance->total_follows_in_system."; ". $new_follower_count ." new follows to load";
			$logger->logStatus($status_message, get_class($this) );
			
			if ( $new_follower_count > 0 ) {
				$logger->logStatus("Fetching follows via IDs", get_class($this) );
				$this->fetchInstanceUserFollowersByIDs($cfg, $api, $logger);
			}
		} else {
			$logger->logStatus("Follower archive is not loaded; fetch should begin.", get_class($this) );
		}


		# Fetch follower pages
		$continue_fetching = true;
		while ( $api->available && 
			$api->available_api_calls_for_crawler > 0 && 
			$continue_fetching && 
			!$this->instance->is_archive_loaded_follows) {

			$this->instance->last_page_fetched_followers = $this->instance->last_page_fetched_followers + 1;

			$follower_ids 	= str_replace("[id]",$cfg->twitter_username,$api->cURL_source['followers']);
			$args = array();
			$args['page'] = $this->instance->last_page_fetched_followers;

			list($cURL_status,$twitter_data) = $api->apiRequest($follower_ids, $logger, $args);

			if ($cURL_status > 200) { 
				$continue_fetching = false;
			} else {
				$fd = new FollowDAO();

				try { 
					$users = $api->parseXML($twitter_data);
					$status_message .= "Page ".$this->instance->last_page_fetched_followers.": ".count($users) ." follows ready to update. ";		

					if ( count($users) == 0 ) {
						$this->instance->last_page_fetched_followers = 0;
						$this->instance->is_archive_loaded_follows = true;
					}

					$updated_follow_count = 0;
					$inserted_follow_count = 0;
					foreach($users as $u) {
						$utu = new User($u, 'Follows');
						$this->ud->updateUser($utu, $logger);
						
						# add/update follow relationship
						if ( $fd->followExists($this->instance->twitter_user_id,$utu->user_id ) ) {
							//update it
							if ( $fd->update( $this->instance->twitter_user_id,$utu->user_id ) )
								$updated_follow_count++;
						} else {
							//insert it
							if ( $fd->insert(  $this->instance->twitter_user_id,$utu->user_id ))
								$inserted_follow_count++;
						}
					}

					$status_message .= "$updated_follow_count existing follows updated; $inserted_follow_count new follows inserted."; 
				} catch (Exception $e) { 
					$status_message = 'Could not parse followers XML for $crawler_twitter_username'; 
				} 
				$logger->logStatus($status_message, get_class($this) );
				$status_message = "";
				
			}

			$logger->logStatus($status_message, get_class($this) );
			$status_message = "";

		}

	}

	function fetchInstanceUserFriends($cfg, $api, $logger) {
		$fd = new FollowDAO();
		$this->instance->total_friends_in_system = $fd->getTotalFriends($cfg->twitter_user_id);
		
		if ($this->instance->total_friends_in_system < $this->owner_object->friend_count) {
			$this->instance->is_archive_loaded_friends = false;
			$logger->logStatus($this->instance->total_friends_in_system." friends in system, ".$this->owner_object->friend_count ." friends according to Twitter; Friend archive is not loaded", get_class($this) );
		} else {
			$this->instance->is_archive_loaded_friends = true;
			$logger->logStatus("Friend archive loaded", get_class($this) );
		}
		
		$status_message = "";
		$this->instance->last_page_fetched_friends = 0;
		# Fetch friend pages
		$continue_fetching = true;
		while ( $api->available && 
			$api->available_api_calls_for_crawler > 0 && 
			$continue_fetching && 
			!$this->instance->is_archive_loaded_friends) {

			$this->instance->last_page_fetched_friends = $this->instance->last_page_fetched_friends + 1;

			$friend_ids 	= str_replace("[id]",$cfg->twitter_username,$api->cURL_source['following']);
			$args = array();
			$args['page'] = $this->instance->last_page_fetched_friends;

			list($cURL_status,$twitter_data) = $api->apiRequest($friend_ids, $logger, $args);

			if ($cURL_status > 200) { 
				$continue_fetching = false;
			} else {

				try { 
					$status_message = "Parsing XML. "; 
					$users = $api->parseXML($twitter_data);
					$status_message .= "Page ".$this->instance->last_page_fetched_friends.": ".count($users) ." friends queued to update. ";		

					$updated_follow_count = 0;
					$inserted_follow_count = 0;

					if ( count($users) == 0 ) {
						$this->instance->last_page_fetched_friends = 0;
						$this->instance->is_archive_loaded_friends = true;
					}

					foreach($users as $u) {
						$utu = new User($u, 'Friends');
						//$this->ud->updateUser($utu, $logger);

						# add/update follow relationship
						if ( $fd->followExists($utu->user_id, $this->instance->twitter_user_id ) ) {
							//update it
							if ( $fd->update( $utu->user_id, $this->instance->twitter_user_id ) )
								$updated_follow_count++;
						} else {
							//insert it
							if ( $fd->insert(  $utu->user_id, $this->instance->twitter_user_id ))
								$inserted_follow_count++;
						}

					}

					$status_message .= "$updated_follow_count existing friends updated; $inserted_follow_count new friends inserted."; 
				} catch (Exception $e) { 
					$status_message = 'Could not parse friends XML for $crawler_twitter_username'; 
				} 
				$logger->logStatus($status_message, get_class($this) );
				$status_message = "";
				
			}

			$logger->logStatus($status_message, get_class($this) );
			$status_message = "";

		}

	}

	function fetchFriendTweetsAndFriends($cfg, $api, $logger) {
		$fd = new FollowDAO();
		$td = new TweetDAO();
		$ud = new UserDAO();

		$continue_fetching = true;
		while ( $api->available && 
			$api->available_api_calls_for_crawler > 0 && 
			$continue_fetching) {
				$stale_friend = $fd->getStalestFriend($cfg->twitter_user_id);
				if ( $stale_friend != null) {
					$logger->logStatus($stale_friend->user_name." is friend most need of update", get_class($this) );		
					$stale_friend_tweets = str_replace("[id]",$stale_friend->user_name,$api->cURL_source['user_timeline']);
					$args = array();
					$args["count"] = 200;			
				
					if ($stale_friend->last_status_id > 0) {
						$args['since_id'] = $stale_friend->last_status_id;
					}

					list($cURL_status,$twitter_data) = $api->apiRequest($stale_friend_tweets, $logger, $args);

					if ($cURL_status == 200) { 
						try { 
							$count = 0;
							$tweets = $api->parseXML($twitter_data);

							if ( count($tweets) > 0 ) {
								foreach($tweets as $tweet) {

									if ( $td->addTweet($tweet, $stale_friend, $logger) > 0 ) 
										$count++;
								
									//Update stale_friend values here
									$stale_friend->full_name=$tweet['full_name'];
									$stale_friend->avatar=$tweet['avatar'];
									$stale_friend->location=$tweet['location'];
									$stale_friend->description=$tweet['description'];
									$stale_friend->url=$tweet['url'];
									$stale_friend->is_protected=$tweet['is_protected'];
									$stale_friend->follower_count=$tweet['follower_count'];
									$stale_friend->friend_count=$tweet['friend_count'];
									$stale_friend->tweet_count=$tweet['tweet_count'];
									$stale_friend->joined=date_format(date_create($tweet['joined']), "Y-m-d H:i:s");
							
									if ( $tweet['status_id'] > $stale_friend->last_status_id ) { 
										$stale_friend->last_status_id = $tweet['status_id'];
									}
									$ud->updateUser($stale_friend, $logger);
								} 
							} else {
								$this->fetchAndAddUser($stale_friend->id, $api, $logger, $cfg, "Friends");	
							}
						
							$logger->logStatus(count($tweets) ." tweet(s) found for ".$stale_friend->username." and $count saved", get_class($this) );		
						} catch (Exception $e) { 
							$logger->logStatus('Could not parse friends XML for $stale_friend->username', get_class($this) );		
						}
						$this->fetchUserFriends($stale_friend->id, $api, $logger, $fd);
					} elseif ( $cURL_status == 401 || $cURL_status == 404 ) {
							try { 
								$e = $api->parseError($twitter_data);
								$ued = new UserErrorDAO();
								$ued->insertError($stale_friend->id, $cURL_status, $e['error'], $cfg->twitter_user_id);
								$logger->logStatus('User error saved', get_class($this) );
							} catch (Exception $e) { 
								$logger->logStatus('Could not parse timeline error for $stale_friend->username', get_class($this) );
							}
					} 
				} else {
					$logger->logStatus('No friend staler than 1 day', get_class($this) );
					$continue_fetching=false;
				}
				
		}
	}

	function fetchStrayRepliedToTweets($cfg, $api, $logger) {
		$td = new TweetDAO();
		$strays = $td->getStrayRepliedToTweets($cfg->twitter_user_id);
		$status_message = count($strays).' stray replied-to tweets to load.'; 
		$logger->logStatus($status_message, get_class($this) );
		
		foreach ($strays as $s) {
			if ( $api->available && $api->available_api_calls_for_crawler > 0 )
				$this->fetchAndAddTweetRepliedTo($s['in_reply_to_status_id'], $td, $api, $logger);
		}
	}

	function fetchUnloadedFollowerDetails($cfg, $api, $logger) {
		$fd = new FollowDAO();
		$strays = $fd->getUnloadedFollowerDetails($cfg->twitter_user_id);
		$status_message = count($strays).' unloaded follower details to load.'; 
		$logger->logStatus($status_message, get_class($this) );
		
		foreach ($strays as $s) {
			if ( $api->available && $api->available_api_calls_for_crawler > 0 )
				$this->fetchAndAddUser($s['follower_id'], $api, $logger, $cfg, "Follower IDs");
		}
	}

	private function fetchUserFriends($uid, $api, $logger, $fd){
		$continue_fetching = true;
		$last_page_fetched_friend_ids = 0;
		$status_message = "";
		
		while ( $api->available && 
			$api->available_api_calls_for_crawler > 0 && 
			$continue_fetching ) {

			$last_page_fetched_friend_ids = $last_page_fetched_friend_ids+1;

			$args = array();
			$friend_ids 	= str_replace("[id]",$uid,$api->cURL_source['following_ids']);
			$args['page'] = $last_page_fetched_friend_ids;

			list($cURL_status,$twitter_data) = $api->apiRequest($friend_ids, $logger, $args);

			if ($cURL_status > 200) { 
				$continue_fetching = false;
			} else {

				try { 
					$ids = $api->parseXML($twitter_data);
					$status_message = "Page ".$last_page_fetched_friend_ids." has ".count($ids) ." friend IDs. ";		

					if ( count($ids) == 0 ) 
						$continue_fetching = false;

					$updated_follow_count = 0;
					$inserted_follow_count = 0;
					foreach($ids as $id) {
						
						# add/update follow relationship
						if ( $fd->followExists($id['id'], $uid ) ) {
							//update it
							if ( $fd->update( $id['id'], $uid ) )
								$updated_follow_count++;
						} else {
							//insert it
							if ( $fd->insert( $id['id'], $uid ))
								$inserted_follow_count++;
						}
					}

					$status_message .= "$updated_follow_count existing follows updated; $inserted_follow_count new follows inserted."; 
				} catch (Exception $e) { 
					$status_message = 'Could not parse follower ID XML for $uid'; 
				} 
				$logger->logStatus($status_message, get_class($this) );
				$status_message = "";
				
			}

			$logger->logStatus($status_message, get_class($this) );
			$status_message = "";

		}
		
	}
	
	private function fetchAndAddUser($fid, $api, $logger, $cfg, $source) {
		//fetch user from Twitter and add to DB
		$status_message = "";
		$u_deets 	= str_replace("[id]",$fid,$api->cURL_source['show_user']);	
		list($cURL_status,$twitter_data) = $api->apiRequest($u_deets, $logger);

		if ($cURL_status == 200) { 
			try { 
				$user_arr = $api->parseXML($twitter_data);
				$user = new User($user_arr[0],  $source);
				$this->ud->updateUser($user, $logger);
				$status_message = 'Added/updated user '.$user->username." in database"; 
			} catch (Exception $e) { 
				$status_message = 'Could not parse tweet XML for $uid'; 
			} 
		} elseif ( $cURL_status == 404) {
			try { 
				$e = $api->parseError($twitter_data);
				$ued = new UserErrorDAO();
				$ued->insertError($fid, $cURL_status, $e['error'], $cfg->twitter_user_id);
				$status_message = 'User error saved.'; 

			} catch (Exception $e) { 
				$status_message = 'Could not parse tweet XML for $uid'; 
			} 
			
		} 
		$logger->logStatus($status_message, get_class($this) );		
		$status_message = "";
		
	}
	
	// For each API call left, grab oldest follow relationship, check if it exists, and update table
	function cleanUpFollows($cfg, $api, $logger) {
		$fd = new FollowDAO();
		$continue_fetching=true;
		while ( $api->available && 
			$api->available_api_calls_for_crawler > 0 && 
			$continue_fetching ) {
			
			$oldfollow = $fd->getOldestFollow();
			
			$friendship_call 	= $api->cURL_source['show_friendship'];	
			$args = array();
			$args["source_id"] = $oldfollow["followee_id"];
			$args["target_id"] = $oldfollow["follower_id"];			

			list($cURL_status,$twitter_data) = $api->apiRequest($friendship_call, $logger, $args);

			if ($cURL_status == 200) { 
				try { 
					$friendship = $api->parseXML($twitter_data);
					if ( $friendship['source_follows_target'] == 'true') 
						$fd->update($oldfollow["followee_id"], $oldfollow["follower_id"]);
					else
						$fd->deactivate($oldfollow["followee_id"], $oldfollow["follower_id"]);

					if ( $friendship['target_follows_source'] == 'true') 
						$fd->update( $oldfollow["follower_id"], $oldfollow["followee_id"]);
					else
						$fd->deactivate( $oldfollow["follower_id"], $oldfollow["followee_id"]);

					
				} catch (Exception $e) { 
					$status_message = 'Could not parse friendship XML'; 
				} 
			} else {
				$continue_fetching = false;
			} 
		}
	}
}
?>