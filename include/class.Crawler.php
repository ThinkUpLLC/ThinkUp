<?php

class Crawler {
	var $instance;
	var $owner_object;
	//data queues
	var $users_to_update = array();
	
	function Crawler($instance) {
		$this->instance = $instance;

	}

	function fetchOwnerInfo($cfg, $api, $logger) {
		// Get owner user details and put them in queue
		$status_message = "";
		$owner_profile 	= str_replace("[id]",$cfg->owner_username,$api->cURL_source['show_user']);	
		list($cURL_status,$twitter_data) = $api->apiRequest($owner_profile, $logger);

		if ($cURL_status == 200) { 
			try { 
				$status_message = "Parsing XML data from $owner_profile "; 
				$users = $api->parseXML($twitter_data);
				foreach($users as $user) 
					$this->owner_object = new User($user, 'Owner Status');
					
				if ( isset($this->owner_object) )
					$status_message = 'Owner info set.'; 
				else
					$status_message = 'Owner was not set.'; 
				
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

			$recent_tweets 		= str_replace("[id]",$cfg->owner_username,$api->cURL_source['user_timeline']);
			$recent_tweets 		.= "?&count=200";
			$last_page_of_tweets = round($this->owner_object->tweet_count / 200)+1;

			if ( $got_latest_page_of_tweets && $this->owner_object->tweet_count != $this->instance->total_tweets_in_system ) {
				if ( $this->instance->last_page_fetched_tweets < 2)
					$this->instance->last_page_fetched_tweets = 2;
				else {
					if ($this->instance->last_page_fetched_tweets < $last_page_of_tweets )
						$this->instance->last_page_fetched_tweets++;
					else
						$this->instance->last_page_fetched_tweets = 1;
				}
				$recent_tweets 		.= "&page=".$this->instance->last_page_fetched_tweets;	
			} else {
				if ($this->instance->last_status_id > 0)  
					$recent_tweets .= "&since_id=".$this->instance->last_status_id; 
			}

			list($cURL_status,$twitter_data) = $api->apiRequest($recent_tweets, $logger);
			if ( $cURL_status == 200) {
				# Parse the XML file
				try { 
					$status_message = "Parsing XML data from $recent_tweets "; 
					$logger->logStatus($status_message, get_class($this) );		
					$status_message = "";

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

					if ( count($tweets) == 0 && $got_latest_page_of_tweets ) {# you're paged back and no new tweets
						$this->instance->last_page_fetched_tweets = 1;
						$continue_fetching=false;
						$status_message = 'Paged back but not finding new tweets; moving on.'; 
						$logger->logStatus($status_message, get_class($this) );		
						$status_message = "";
					}


					if ( $this->owner_object->tweet_count == $this->instance->total_tweets_in_system)  
						$this->instance->is_archive_loaded_tweets = true;

					$status_message .= $this->instance->total_tweets_in_system." in system; ".$this->owner_object->tweet_count." by owner\n";
					$logger->logStatus($status_message, get_class($this) );		
					$status_message = "";

				} catch (Exception $e) { 
					$status_message = 'Could not parse tweet XML for $this->owner_username'; 
					$logger->logStatus($status_message, get_class($this) );		
					$status_message = "";

				} 
			}
			$got_latest_page_of_tweets = true;

		}

		if ( $this->owner_object->tweet_count == $this->instance->total_tweets_in_system ) {
			$status_message .= "All of ".$this->owner_object->user_name."'s tweets are in the system; Stopping tweet fetch.";
			$this->instance->is_archive_loaded_tweets = true;
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
				$replies 		= str_replace("[id]",$cfg->owner_username,$api->cURL_source['replies']);
				$replies 		.= "?&count=200";

				if ( $got_newest_replies ) {
					$this->last_page_fetched_replies++;
					$replies 		.= "&page=".$this->last_page_fetched_replies;	
				}

				list($cURL_status,$twitter_data) = $api->apiRequest($replies, $logger);
				if ($cURL_status > 200) { 
					$continue_fetching = false;
				} else {
					try { 
						$status_message = "Parsing XML data from $replies"; 
						$logger->logStatus($status_message, get_class($this) );
						$status_message = "";
						
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

								if ( $tweet['user_id'] != $cfg->owner_user_id) { //don't update owner info from reply
									$u = new User($tweet, 'Replies');
									array_push($this->users_to_update, $u);
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
						$status_message = 'Could not parse replies XML for $cfg->owner_username'; 
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
	
	
	function fetchOwnerFollowers($cfg, $api, $logger) {
		// Get owner's followers: Page back only if more than 5% of follows are missing from database
		// See how many are missing from last run
		if ( $this->instance->is_archive_loaded_follows  ) { //all pages have been loaded
			//find out how many new follows owner has compared to what's in db
			$new_follower_count = $this->owner_object->follower_count - $this->instance->total_follows_in_system;
			$status_message = "New follower count is ". $this->owner_object->follower_count." and system has ".$this->instance->total_follows_in_system."; ". $new_follower_count ." new follows to load";
			$logger->logStatus($status_message, get_class($this) );
			$status_message = "";
			
			if ( $new_follower_count > 0 ) {
				//figure out percentage 
				$percent_follows_missing = 100 - (($this->instance->total_follows_in_system*100)/$this->owner_object->follower_count);
				$percent_follows_missing = round($percent_follows_missing, 1);
				$status_message .= " $percent_follows_missing% of follows are missing.";
				if ( $percent_follows_missing > 2 ) {
					$status_message .= " Fetching follows, more than 2% are missing from system";
					$this->instance->is_archive_loaded_follows = false;
					$logger->logStatus($status_message, get_class($this) );
					$status_message = "";
					
				}
			}
		}


		# Fetch follower pages
		$continue_fetching = true;
		while ( $api->available && 
			$api->available_api_calls_for_crawler > 0 && 
			$continue_fetching && 
			!$this->instance->is_archive_loaded_follows) {

			$this->instance->last_page_fetched_followers = $this->instance->last_page_fetched_followers + 1;

			$follower_ids 	= str_replace("[id]",$cfg->owner_username,$api->cURL_source['followers']);
			$follower_ids  .= "?page=".$this->instance->last_page_fetched_followers;

			list($cURL_status,$twitter_data) = $api->apiRequest($follower_ids, $logger);

			if ($cURL_status > 200) { 
				$continue_fetching = false;
			} else {

				try { 
					$status_message = "Parsing XML. "; 
					$users = $api->parseXML($twitter_data);
					$status_message .= "Page ".$this->instance->last_page_fetched_followers.": ".count($users) ." follows queued to update. ";		
					$count = 0;
					if ( count($users) == 0 ) {
						$this->instance->last_page_fetched_followers = 0;
						$this->instance->is_archive_loaded_follows = true;
					}

					foreach($users as $u) {
						$utu = new User($u, 'Follows');
						array_push($this->users_to_update, $utu);

						# add/update follow relationship
						# TODO: move this to Follow DAO
						$sql_query['Insert_or_Update_Follow'] = "
							INSERT INTO
								follows (user_id,follower_id,last_seen)
								VALUES (
									".$this->instance->owner_user_id.",".$u['user_id'].",NOW()
								)
								ON DUPLICATE KEY UPDATE 
									last_seen=NOW();
								"; 
						$foo = mysql_query($sql_query['Insert_or_Update_Follow']) or die('Error, insert query failed: '. $sql_query['Insert_or_Update_Follow'] );
						if (mysql_affected_rows() > 0)
							$count++;
					}

					$status_message .= "$count rows affected."; 
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

			$friend_ids 	= str_replace("[id]",$cfg->owner_username,$api->cURL_source['following']);
			$friend_ids  .= "?page=".$this->instance->last_page_fetched_friends;

			list($cURL_status,$twitter_data) = $api->apiRequest($friend_ids, $logger);

			if ($cURL_status > 200) { 
				$continue_fetching = false;
			} else {

				try { 
					$status_message = "Parsing XML. "; 
					$users = $api->parseXML($twitter_data);
					$status_message .= "Page ".$this->instance->last_page_fetched_friends.": ".count($users) ." friends queued to update. ";		
					$count = 0;
					if ( count($users) == 0 ) {
						$this->instance->last_page_fetched_friends = 0;
						$this->instance->is_archive_loaded_friends = true;
					}

					foreach($users as $u) {
						$utu = new User($u, 'Friends');
						array_push($this->users_to_update, $utu);

						# add/update follow relationship
						# TODO: move this to Follow DAO
						$sql_query['Insert_or_Update_Follow'] = "
							INSERT INTO
								follows (user_id,follower_id,last_seen)
								VALUES (
									".$u['user_id'].", ".$this->instance->owner_user_id.", NOW()
								)
								ON DUPLICATE KEY UPDATE 
									last_seen=NOW();
								"; 
						$foo = mysql_query($sql_query['Insert_or_Update_Follow']) or die('Error, insert query failed: '. $sql_query['Insert_or_Update_Follow'] );
						if (mysql_affected_rows() > 0)
							$count++;
					}

					$status_message .= "$count rows affected."; 
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


	
	function updateMostFollowedUsers($logger) {
		
		
		
		
	}
	

	function updateQueuedUsers($logger) {
		$ud = new UserDao();
		
		// Update queued users
		$ud->updateUser($this->owner_object, $logger);
		$ud -> updateUsers($this->users_to_update, $logger);
	}

}
?>