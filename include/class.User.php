<?php

class User {
	var $id;
	var $username;
	var $full_name;
	var $avatar;
	var $location;
	var $description;
	var $url;
	var $is_protected;
	var $follower_count;
	var $following;
	var $tweet_count;
	var $found_in;
	var $last_post;
	var $joined;


	function User($val, $found_in) {
		$this-> id = $val["user_id"];
		$this-> username = $val["user_name"];
		$this-> full_name = $val["full_name"];
		$this-> user_id = $val['user_id'];
		$this-> user_name = $val['user_name'];
		$this-> full_name = $val['full_name']; 
		$this-> avatar = $val['avatar'];
		$this-> location = $val['location'];
		$this-> description = $val['description'];
		$this-> url = $val['url'];
		$this-> is_protected = $val['is_protected'];
		$this-> follower_count = $val['followers'];
		$this-> tweet_count = $val['tweets'];
		if (isset($val['following']))
			$this-> following = $val['following'];
		if (isset($val['last_post'])) 
			$this-> last_post = date_format(date_create($val['last_post']), "Y-m-d H:i:s");
		$this -> joined = date_format(date_create($val['joined']), "Y-m-d H:i:s");
		
		$this->found_in = $found_in;
	}
	
}

class UserDAO {
	
	private function getAverageTweetCount() {
		return "round(tweet_count/(datediff(curdate(), joined)), 2) as avg_tweets_per_day";
	}
	
	
	
	function isUserInDB($user_id) {
		$q = "
			SELECT 
				user_id 
			FROM 
				user 
			WHERE 
				user_id = ".$user_id;
		$sql_result = mysql_query($q) or die('Error [user_is_in_db]: selection query failed:' .$q );
		if ( mysql_num_rows($sql_result) > 0 )
			return true;
		else
			return false;
	}
	
	
	function updateUsers($users_to_update, $logger) {
		$sql_query = array();
		$status_message = "";
		
		if ( count($users_to_update) > 0 ) {
			$status_message .= count($users_to_update) ." users queued for insert or update; ";
			$count = 0;
			foreach ($users_to_update as $user) 
				$count += $this->updateUser($user, $logger);
			
			$status_message .= "$count users affected."; 		

		}	
		$logger->logStatus($status_message, get_class($this) );
		$status_message = "";
		
	}

	function updateUser($user, $logger) {
		$status_message = "";
		$has_friend_count = $user->following != '' ?  true : false;
		$has_last_post = $user->last_post != '' ?  true : false;
				
		$sql_query = "
			INSERT INTO
				users (user_id,
					user_name,full_name,avatar,location,
					description, url, is_protected,
					follower_count, tweet_count, ". ($has_friend_count ? "friend_count, " : "")."
					". ($has_last_post ? "last_post, " : "")."
					found_in, joined)
				VALUES (
					".mysql_real_escape_string($user->user_id).", 
					'".mysql_real_escape_string($user->user_name)."','" .mysql_real_escape_string($user->full_name) . "','".mysql_real_escape_string($user->avatar)."','".mysql_real_escape_string($user->location)."',  
					'".mysql_real_escape_string($user->description)."', '".mysql_real_escape_string($user->url)."',". $user->is_protected.",  							
					".$user->follower_count.",". $user->tweet_count.",
					". ($has_friend_count ? $user->following.", " : "")."
					". ($has_last_post ? "'".mysql_real_escape_string($user->last_post)."', " : "")."					
					'".mysql_real_escape_string($user->found_in)."', '".mysql_real_escape_string($user->joined)."'
					)
				ON DUPLICATE KEY UPDATE 
					full_name = '".mysql_real_escape_string($user->full_name) ."',
					avatar =  '".mysql_real_escape_string($user->avatar) ."',
					location = '".mysql_real_escape_string($user->location) ."',
					description = '".mysql_real_escape_string($user->description)."',
					url = '".mysql_real_escape_string($user->url)."',
					is_protected = ".$user->is_protected .",
					follower_count = ".$user->follower_count.",
					tweet_count = ".$user->tweet_count.",
					". ($has_friend_count ? "friend_count= ".$user->following.", " : "")."
					". ($has_last_post ? "last_post= '".mysql_real_escape_string($user->last_post)."', " : "")."
					last_updated = NOW(),
					found_in = '".mysql_real_escape_string($user->found_in) . "', 
					joined = '".mysql_real_escape_string($user->joined)."';
				";  
		$foo = mysql_query($sql_query) or die('Error, insert query failed: '. $sql_query );
		
		if (mysql_affected_rows() > 0) {
			//$status_message = "User ". $user->user_name." updated in system.";
			//$logger->logStatus($status_message, get_class($this) );
			//$status_message = "";
			return 1;
		} else {
			//$status_message = $user->user_name." was NOT updated in system.";
			//$logger->logStatus($status_message, get_class($this) );
			//$status_message = "";
			return 0;
		}
	}
 	
	//TODO: make this return the User object, not an assoc array
	function getDetails($user_id) {
		$sql_query		= "
			SELECT 
				* , ". $this->getAverageTweetCount()."
			FROM
				users u 
			WHERE 
				u.user_id = ". $user_id. ";";
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$row = mysql_fetch_assoc($sql_result);
		mysql_free_result($sql_result);	
		return $row;		
	}

	function getMostFollowedFollowers($user_id, $count) {
		$sql_query		= "
			SELECT 
				* , ". $this->getAverageTweetCount()."
			FROM 
				users u 
			INNER JOIN
			 	follows f 
			ON 
				u.user_id = f.follower_id 
			WHERE
				f.user_id = ".$user_id."
			ORDER BY 
				u.follower_count DESC 
			LIMIT ".$count.";";
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$most_followed_followers 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $most_followed_followers[] = $row; } 
		mysql_free_result($sql_result);	

		return $most_followed_followers;
		
	}


	function getLeastLikelyFollowers($user_id, $count) {
		
		//TODO: Remove hardcoded 10k follower threshold in query below
		$sql_query		= "
			SELECT 
				*, ROUND(100*friend_count/follower_count,4) AS LikelihoodOfFollow, ". $this->getAverageTweetCount()."
			FROM 
				users u 
			INNER JOIN
			 	follows f 
			ON 
				u.user_id = f.follower_id 
			WHERE
				f.user_id =  ".$user_id." and follower_count > 10000 and friend_count > 0
			ORDER BY 
				LikelihoodOfFollow ASC #u.follower_count DESC
			LIMIT ".$count.";";
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$least_likely_followers 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $least_likely_followers[] = $row; } 
		mysql_free_result($sql_result);	

		return $least_likely_followers;
		
	}

	function getEarliestJoinerFollowers($user_id, $count) {
		$sql_query		= "
			SELECT 
				*, ". $this->getAverageTweetCount()."
			FROM 
				users u 
			INNER JOIN
			 	follows f 
			ON 
				u.user_id = f.follower_id 
			WHERE
				f.user_id =  ".$user_id." 
			ORDER BY 
				u.user_id ASC
			LIMIT ".$count.";";
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$earliest_joiner_followers 		= array();
		$least_likely_followers = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $least_likely_followers[] = $row; } 
		mysql_free_result($sql_result);	

		return $least_likely_followers;
		
	}	
	
	function getMostActiveFollowees($user_id, $count) {
		$sql_query = "
			select 
				*, ". $this->getAverageTweetCount()." 
			from 
				users u 
			inner join 
				follows f 
			on 
				f.user_id = u.user_id 
			where 
				f.follower_id = ".$user_id."
			order by 
				avg_tweets_per_day DESC 
			LIMIT ".$count;		
			
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$most_active_friends 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $most_active_friends[] = $row; } 
		mysql_free_result($sql_result);	

		return $most_active_friends;
		
	}

	function getLeastActiveFollowees($user_id, $count) {
		$sql_query = "
			select 
				*, ". $this->getAverageTweetCount()."
			from 
				users u 
			inner join 
				follows f 
			on 
				f.user_id = u.user_id 
			where 
				f.follower_id = ".$user_id."
			order by 
				avg_tweets_per_day ASC 
			LIMIT ".$count;		
			
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$most_active_friends 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $most_active_friends[] = $row; } 
		mysql_free_result($sql_result);	

		return $most_active_friends;
		
	}


	function getMostFollowedFollowees($user_id, $count) {
		$sql_query = "
			select 
				*, ". $this->getAverageTweetCount()."
			from 
				users u 
			inner join 
				follows f 
			on 
				f.user_id = u.user_id 
			where 
				f.follower_id = ".$user_id."
			order by 
				follower_count DESC 
			LIMIT ".$count;		
			
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$most_followed_friends 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $most_followed_friends[] = $row; } 
		mysql_free_result($sql_result);	

		return $most_followed_friends;
		
	}


	
}

class UserErrorDAO {
	function insertError($id, $error_code, $error_text, $issued_to) {
		$q = "
			INSERT INTO
			 	user_errors (user_id, error_code, error_text, error_issued_to_user_id)
			VALUES 
				(".$id.", ".$error_code.", '".$error_text."', ".$issued_to.") ";
		$sql_result = mysql_query($q) or die('Error, insert failed:' .$q );
		if (mysql_affected_rows() > 0)
			return true;
		else
			return false;
	}
}

?>