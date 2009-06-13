<?php

class TwitterAPIAccessor {
	var $app_title; 
	var $cURL;
	var $available = true;
	var $api_calls_to_leave_unmade;
	var $available_api_calls_for_crawler = null;
	var $available_api_calls_for_twitter = null;
	var $next_api_reset = null;
	var $cURL_source;
	var $owner_username;
	

	function TwitterAPIAccessor($app_title, $instance) {
		list($this->owner_username,$this->cURL_source) = $this -> prepAPI($instance->owner_username);
		$this -> cURL = $this -> prepRequest($app_title, $instance->owner_username, $instance->owner_password);
		$this -> api_calls_to_leave_unmade = $instance->api_calls_to_leave_unmade;
		$this -> app_title = $app_title;
	}
	
	function init($logger) {
		$status_message = "";
		//TODO: Standardize this call using config like the others
		//$account_status		= $cURL_source['rate_limit'];
		$account_status		= "https://twitter.com/account/rate_limit_status.json"; 	
		list($cURL_status,$twitter_data) = $this->apiRequest($account_status, $logger);
		$this->available_api_calls_for_crawler++; //status check doesnt' count against balance

		if ($cURL_status > 200) { 
			$this->available = false;
		} else {
			try { 
				# Parse file
				/* XML DOESN'T WORK

				$status_message = "Parsing XML data from $account_status "; 
				$statuses = parseXML($twitter_data);
				foreach($statuses as $status) {
				 	$available_api_calls_for_twitter = $status['remaining-hits'];//get this from API
					$available_api_calls_for_crawler = $available_api_calls_for_twitter - $crawler_calls_to_leave_unmade;
					$twitter_next_api_reset = $status['reset-time'];//get this from API
				}
				*/
		/* Use JSON instead */
				//$status_message .= "Parsing JSON data from $account_status ";
				$json_dump = json_decode($twitter_data);
				
				if ( !isset($json_dump) ) {
					$this->available = false;
					$status_message = 'Could not parse account status JSON'; 
				} else {
					$this -> available_api_calls_for_twitter = $json_dump->remaining_hits;
					$this -> next_api_reset = $json_dump->reset_time;
					$this -> available_api_calls_for_crawler = $this -> available_api_calls_for_twitter - $this -> api_calls_to_leave_unmade;
				}
			} catch (Exception $e) { 
				$status_message = 'Could not parse account status JSON'; 
			} 
		}
		$logger -> logStatus($status_message, get_class($this) );
		$status_message = "";
		$logger -> logStatus($this->getStatus(), get_class($this) );		
		
		
	}


	function prepAPI ($master_username) {

		# Define how to access Twitter API
		$api_domain			= 'https://twitter.com';
		$api_format 		= 'xml';
		$search_domain		= 'http://search.twitter.com';
		$search_format 		= 'atom';

		# Define method paths ... [id] is a placeholder
		$api_method = array(
			"end_session" 			=> "/account/end_session",
			"rate_limit" 			=> "/account/rate_limit_status",
			"delivery_device" 		=> "/account/update_delivery_device",
			"location" 				=> "/account/update_location",
			"profile" 				=> "/account/update_profile",
			"profile_background" 	=> "/account/update_profile_background_image",
			"profile_colors" 		=> "/account/update_profile_colors",
			"profile_image" 		=> "/account/update_profile_image",
			"credentials" 			=> "/account/verify_credentials",
			"block" 				=> "/blocks/create/[id]",
			"remove_block" 			=> "/blocks/destroy/[id]",
			"messages_received"		=> "/direct_messages",
			"delete_message" 		=> "/direct_messages/destroy/[id]",
			"post_message" 			=> "/direct_messages/new",
			"messages_sent" 		=> "/direct_messages/sent",
			"bookmarks" 			=> "/favorites/[id]",
			"create_bookmark" 		=> "/favorites/create/[id]",
			"remove_bookmark" 		=> "/favorites/destroy/[id]",
			"followers_ids" 		=> "/followers/ids/[id]",
			"following_ids" 		=> "/friends/ids/[id]",
			"follow" 				=> "/friendships/create/[id]",
			"unfollow" 				=> "/friendships/destroy/[id]",
			"confirm_follow" 		=> "/friendships/exists",
			"test" 					=> "/help/test",
			"turn_on_notification"	=> "/notifications/follow/[id]",
			"turn_off_notification"	=> "/notifications/leave/[id]",
			"delete_tweet" 			=> "/statuses/destroy/[id]",
			"followers" 			=> "/statuses/followers/[id]",
			"following" 			=> "/statuses/friends/[id]",
			"friends_timeline" 		=> "/statuses/friends_timeline",
			"public_timeline" 		=> "/statuses/public_timeline",
			"replies" 				=> "/statuses/replies",
			"show_tweet" 			=> "/statuses/show/[id]",
			"post_tweet" 			=> "/statuses/update",
			"user_timeline" 		=> "/statuses/user_timeline/[id]",
			"show_user" 			=> "/users/show/[id]"		
		);

		# Construct cURL sources
		foreach ($api_method as $key => $value){
			$urls[$key] 		= $api_domain . $value . "." . $api_format;
		}
		$urls['search'] 		= $search_domain . "/search." . $search_format;
		$urls['search_web'] 	= $search_domain . "/search";
		$urls['trends'] 		= $search_domain . "/trends.json";

	    return array($master_username,$urls);
	}

	function prepRequest ($title='',$username='',$password='') {
		$options = array(
			CURLOPT_USERAGENT => $title,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTPHEADER => array('Expect:')
			);
		if ($username != '') {
			$options[CURLOPT_USERPWD] = "$username:$password";
		}

		$cURL 				= curl_init();					# Initiate cURL connection
		curl_setopt_array($cURL, $options);			# Set all options at once
	    return $cURL;
	}

	function apiRequest ($url, $logger) {
		$cURL = $this -> cURL;
		curl_setopt($cURL, CURLOPT_URL, $url);	
		$foo			= curl_exec($cURL);				
		$status 		= curl_getinfo($cURL, CURLINFO_HTTP_CODE);
		$this->available_api_calls_for_crawler--;
		
		if ( $status > 200 ) {
			$status_message	= "Could not retrieve $url"; 
			$status_message .= " | API ERROR: $status"; 
			$status_message .= " | $this->owner_username"; 
			$status_message .= "\n\n$foo\n\n"; 
			$this->available = false;
			$logger->logStatus($status_message, get_class($this) );		
			$status_message = "";
		} else {
			$status_message = "API request: ".$url."  ";
		}
		$logger->logStatus($status_message, get_class($this) );		
		$status_message = "";

		if ( $url != "https://twitter.com/account/rate_limit_status.json") {
			$status_message = $this->getStatus();
			$logger->logStatus($status_message, get_class($this) );		
			$status_message = "";
		}
				
	    return array($status,$foo);
	}

	function close() {
		curl_close($this -> cURL);
	}
	
	function getStatus() {
		return $this -> available_api_calls_for_crawler . " API calls left for crawler until ". $this -> next_api_reset;

	}
	

	function parseFeed ($url,$date=0) {
		$thisFeed 		= array();
		$feed_title 	= '';
		if(preg_match("/^http/", $url)) {
		try { 
			$doc 		= createDOMfromURL($url);	

			$feed_title = $doc->getElementsByTagName('title')->item(0)->nodeValue;

			$item 		= $doc->getElementsByTagName('item');
			foreach ($item as $item) {
				$articleInfo = array ( 
					'title' => $item->getElementsByTagName('title')->item(0)->nodeValue,
					'link' => $item->getElementsByTagName('link')->item(0)->nodeValue,
					'id' => $item->getElementsByTagName('id')->item(0)->nodeValue,
					'pubDate' => $item->getElementsByTagName('pubDate')->item(0)->nodeValue
				);
				if (($date == 0) || (strtotime($articleInfo['pubDate']) > strtotime($date))) {
					array_push($thisFeed, $articleInfo);
				}
			}

			$entry 		= $doc->getElementsByTagName('entry');
			foreach ($entry as $entry) {
				$articleInfo = array ( 
					'title' => $entry->getElementsByTagName('title')->item(0)->nodeValue,
					'link' => $entry->getElementsByTagName('link')->item(0)->getAttribute('href'),
					'id' => $entry->getElementsByTagName('id')->item(0)->nodeValue,
					'pubDate' => $entry->getElementsByTagName('pubDate')->item(0)->nodeValue,
					'published' => $entry->getElementsByTagName('published')->item(0)->nodeValue
				);
				foreach($articleInfo as $key => $value) {
					$articleInfo[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
				}
				if (($date == 0) 
				|| (strtotime($articleInfo['pubDate']) > strtotime($date)) 
				|| (strtotime($articleInfo['published']) > strtotime($date))) {
					array_push($thisFeed, $articleInfo);
				}
			}
		} catch (Exception $e) 						{ $form_error = 15; } 
		}

		$feed_title = htmlspecialchars($feed_title, ENT_QUOTES, 'UTF-8');
	    return array($thisFeed,$feed_title);
	}

	function parseXML ($data) {
		$thisFeed 	= array();
		try { 
			$xml 		= $this->createParserFromString(utf8_encode($data));	
			if ( isset($xml)) {
				$root		= $xml->getName();	
				switch ($root) {
					case 'user':
		  				$thisFeed[] = array(
		 					'user_id' 			=> $xml->id,
		 					'user_name' 		=> $xml->screen_name,
		 					'full_name' 		=> $xml->name,
		 					'avatar' 			=> $xml->profile_image_url,
		 					'location' 			=> $xml->location,
		 					'description' 		=> $xml->description,
		 					'url' 				=> $xml->url,
		 					'is_protected' 		=> $xml->protected,
		 					'followers' 		=> $xml->followers_count,
		 					'following' 		=> $xml->friends_count,
		 					'tweets' 			=> $xml->statuses_count,
							'favorites_count' 	=> $xml->favourites_count,
							'joined'			=> $xml->created_at
						);
						break;
					case 'status':
		  				$thisFeed[] = array(
		 					'status_id' 		=> $xml->id
						);
						break;
					case 'users':
						foreach($xml->children() as $item) {
		  					$thisFeed[] = array(
								'status_id' 		=> $item->status->id,
		 						'user_id' 			=> $item->id,
		 						'user_name' 		=> $item->screen_name,
		 						'full_name' 		=> $item->name,
		 						'avatar' 			=> $item->profile_image_url,
		 						'location' 			=> $item->location,
		 						'description' 		=> $item->description,
		 						'url' 				=> $item->url,
		 						'is_protected' 		=> $item->protected,
								'following'			=> $item->friends_count,
		 						'followers' 		=> $item->followers_count,
								'joined'			=> $item->created_at,
		 						'tweet_text' 		=> $item->status->text,
		 						'tweet_html' 		=> $item->status->text,
								'last_post'			=> $item->status->created_at,
		 						'pub_date' 			=> gmdate("Y-m-d H:i:s",strToTime($item->status->created_at)),
		 						'favorites_count' 	=> $item->favourites_count,
		 						'tweets' 			=> $item->statuses_count
							);
						}
						break;
		 			case 'statuses':
						foreach($xml->children() as $item) {
		  					$thisFeed[] = array(
								'status_id' 		=> $item->id,
		 						'user_id' 			=> $item->user->id,
		 						'user_name' 		=> $item->user->screen_name,
		 						'full_name' 		=> $item->user->name,
		 						'avatar' 			=> $item->user->profile_image_url,
		 						'location' 			=> $item->user->location,
		 						'description' 		=> $item->user->description,
		 						'url' 				=> $item->user->url,
		 						'is_protected' 		=> $item->user->protected,
		 						'followers' 		=> $item->user->followers_count,
								'following'			=> $item->user->friends_count,
								'tweets' 			=> $item->user->statuses_count,
								'joined'			=> $item->user->created_at,
		 						'tweet_text' 		=> $item->text,
		 						'tweet_html' 		=> $item->text,
		 						'pub_date' 			=> gmdate("Y-m-d H:i:s",strToTime($item->created_at)),
		 						'favorites_count' 	=> $item->user->favourites_count,
		 						'in_reply_to_status_id' => $item->in_reply_to_status_id,
		 						'in_reply_to_user_id' => $item->in_reply_to_user_id
							);
						}
						break;
					case 'hash':
					//DOESN'T WORK!
						$thisFeed[] = array(
							'remaining-hits' 		=> $xml->remaining_hits,
							'hourly-limit'			=> $xml->hourly_limit,
							'reset-time'			=> $xml->reset_time
						);
						echo "remaining hits: ". $xml->remaining_hits ." reset time " .  $thisFeed['reset-time'];
						break;
		  			default:
		    			break;
				}
			}
		} catch (Exception $e) 					{ $form_error = 15; } 

	    return $thisFeed;
	}

	function createDOMfromURL ($url) {
		$doc 		= new DOMDocument();
		$doc->load($url);
		return $doc;
	}

	function createParserFromString ($data) {
		$xml = simplexml_load_string($data);
		return $xml;
	}	

}

?>