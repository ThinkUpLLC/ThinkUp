<?php

class Crawler {
	var $instance;
	var $owner_object;
	var $ud;
	
	function Crawler($instance) {
		$this->instance = $instance;
		$this->ud = new UserDao();
	}

	function fetchOwnerInfo($cfg, $api, $logger) {
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
	
	function fetchOwnerTweets($cfg, $api, $logger) {
		// Get owner's tweets
		$status_message = "";
		$got_latest_page_of_tweets = false;
		$continue_fetching = true;

		while ( $api->available && $api->available_api_calls_for_crawler > 0 && $this->owner_object->tweet_count > $this->instance->total_tweets_in_system && $continue_fetching) {	

			$recent_tweets 		= str_replace("[id]",$cfg->twitter_username,$api->cURL_source['user_timeline']);
			$args = array();
			$args["count"] = 200;			
			$last_page_of_tweets = round($this->owner_object->tweet_count / 200)+1;

			if ( $got_latest_page_of_tweets && $this->owner_object->tweet_count != $this->instance->total_tweets_in_system ) {
				if ($this->instance->last_page_fetched_tweets < $last_page_of_tweets )
					$this->instance->last_page_fetched_tweets++;
				else {
					$continue_fetching = false;
					$this->instance->last_page_fetched_tweets = 1;
				}
				$args["page"] = $this->instance->last_page_fetched_tweets;			
					
			} else {
				if ($this->instance->last_status_id > 0)  
					$args["since_id"] = $this->instance->last_status_id;
			}

			list($cURL_status,$twitter_data) = $api->apiRequest($recent_tweets, $logger, $args);
			if ( $cURL_status == 200) {
				# Parse the XML file
				try { 
					$count = 0;
					$tweets = $api->parseXML($twitter_data);

					$td = new TweetDAO;
					foreach($tweets as $tweet) {

						if ( $td->addTweet($tweet, $this->owner_object, $logger) > 0 ) {
							$count++;
							$this->instance->total_tweets_in_system++;
						}
						if ( $tweet['status_id'] > $this->instance->last_status_id ) 
							$this->instance->last_status_id = $tweet['status_id'];
					}
					$status_message .= count($tweets) ." tweet(s) found and $count saved"; 
					$logger->logStatus($status_message, get_class($this) );		
					$status_message = "";
					
					//Can't rely on Twitter to return all the tweets it says it has
					if ( count($tweets) == 0 && $got_latest_page_of_tweets /*&& $this->instance->last_page_fetched_tweets >= $last_page_of_tweets */) {
						$this->instance->last_page_fetched_tweets = 1;
						$continue_fetching=false;
						$status_message = "Paged back ". $this->instance->last_page_fetched_tweets ." pages and not finding new tweets; moving on."; 
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

		if ( $this->owner_object->tweet_count == $this->instance->total_tweets_in_system ) {
			$status_message .= "All of ".$this->owner_object->user_name."'s tweets are in the system; Stopping tweet fetch.";
			$this->instance->is_archive_loaded_tweets = true;
		}


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


	function fetchOwnerReplies($cfg, $api, $logger) {
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
	
	private function fetchOwnerFollowersByIDs($cfg, $api, $logger) {
		$continue_fetching = true;
		$last_page_fetched_follower_ids = 0;
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
					$status_message = "Parsing XML. "; 
					$ids = $api->parseXML($twitter_data);
					$status_message .= "Page ".$last_page_fetched_follower_ids." has ".count($ids) ." follower IDs. ";		

					if ( count($ids) == 0 ) {
						$this->instance->is_archive_loaded_follows = true;
						$continue_fetching = false;
					}

					$updated_follow_count = 0;
					$inserted_follow_count = 0;
					foreach($ids as $id) {
						
						# add/update follow relationship
						if ( $fd->followExists($this->instance->twitter_user_id,$id['id'] ) ) {
							//update it
							if ( $fd->update( $this->instance->twitter_user_id,$id['id'] ) )
								$updated_follow_count++;
						} else {
							//insert it
							if ( $fd->insert(  $this->instance->twitter_user_id,$id['id'] ))
								$inserted_follow_count++;
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
	}
	
	function fetchOwnerFollowers($cfg, $api, $logger) {
		$status_message = "";
		// Get owner's followers: Page back only if more than 2% of follows are missing from database
		// See how many are missing from last run
		if ( $this->instance->is_archive_loaded_follows  ) { //all pages have been loaded
			$status_message = "Follower archive marked as loaded";
			
			//find out how many new follows owner has compared to what's in db
			$new_follower_count = $this->owner_object->follower_count - $this->instance->total_follows_in_system;
			$status_message = "New follower count is ". $this->owner_object->follower_count." and system has ".$this->instance->total_follows_in_system."; ". $new_follower_count ." new follows to load";
			$logger->logStatus($status_message, get_class($this) );
			$status_message = "";
			
			$status_message .= " Fetching follows via IDs";
			$logger->logStatus($status_message, get_class($this) );
			$status_message = "";
			$this->fetchOwnerFollowersByIDs($cfg, $api, $logger);
		} else {
			$status_message = "Follower archive is not loaded; fetch should begin.";
			$logger->logStatus($status_message, get_class($this) );
			$status_message = "";
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
					$status_message = "Parsing XML. "; 
					$users = $api->parseXML($twitter_data);
					$status_message .= "Page ".$this->instance->last_page_fetched_followers.": ".count($users) ." follows queued to update. ";		

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


	function fetchOwnerFriends($cfg, $api, $logger) {
		$status_message = "";
		$this->instance->is_archive_loaded_friends = false;
		# $this->is_archive_loaded_friends == compare friend count to what's in DB
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

					$fd = new FollowDAO();
					foreach($users as $u) {
						$utu = new User($u, 'Friends');
						$this->ud->updateUser($utu, $logger);

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
				$this->fetchAndAddUser($s['follower_id'], $api, $logger, $cfg);
		}
	}

	private function fetchAndAddUser($fid, $api, $logger, $cfg) {
		//fetch user from Twitter and add to DB
		$status_message = "";
		$u_deets 	= str_replace("[id]",$fid,$api->cURL_source['show_user']);	
		list($cURL_status,$twitter_data) = $api->apiRequest($u_deets, $logger);

		if ($cURL_status == 200) { 
			try { 
				$user_arr = $api->parseXML($twitter_data);
				$user = new User($user_arr[0], "Follower IDs");
				$this->ud->updateUser($user, $logger);
				$status_message = 'Added new user '.$user->username." to database."; 
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
	
}
?>