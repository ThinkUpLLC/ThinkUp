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
	var $friend_count;
	var $tweet_count;
	var $found_in;
	var $last_post;
	var $joined;
	var $last_status_id;


	function User($val, $found_in) {
		// Why is id = $val["user_id"] and so is user_id?
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
		if ( $this-> is_protected == '')
			$this-> is_protected = 0;
		elseif ( $this-> is_protected == 'true')
			$this-> is_protected = 1;
		$this-> follower_count = $val['follower_count'];
		$this-> tweet_count = $val['tweet_count'];
		if (isset($val['last_status_id']))
			$this-> last_status_id = $val['last_status_id'];
		if (isset($val['friend_count']))
			$this-> friend_count = $val['friend_count'];
		if (isset($val['last_post'])) 
			$this-> last_post = $val['last_post'];
		$this -> joined = $val['joined'];
		
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
				users 
			WHERE 
				user_id = ".$user_id;
		$sql_result = Database::exec($q);
		if ( mysql_num_rows($sql_result) > 0 )
			return true;
		else
			return false;
	}

	function isUserInDBByName($username) {
		$q = "
			SELECT 
				user_id 
			FROM 
				users 
			WHERE 
				user_name = '".$username."'";
		$sql_result = Database::exec($q);
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
		$has_friend_count = $user->friend_count != '' ?  true : false;
		$has_last_post = $user->last_post != '' ?  true : false;
		$has_last_status_id = $user->last_status_id != '' ? true : false;
				
		$q = "
			INSERT INTO
				users (user_id,
					user_name,full_name,avatar,location,
					description, url, is_protected,
					follower_count, tweet_count, ". ($has_friend_count ? "friend_count, " : "")."
					". ($has_last_post ? "last_post, " : "")."
					found_in, joined  ". ($has_last_status_id ? ", last_status_id" : "").")
				VALUES (
					".mysql_real_escape_string($user->user_id).", 
					'".mysql_real_escape_string($user->user_name)."','" .mysql_real_escape_string($user->full_name) . "','".mysql_real_escape_string($user->avatar)."','".mysql_real_escape_string($user->location)."',  
					'".mysql_real_escape_string($user->description)."', '".mysql_real_escape_string($user->url)."',". $user->is_protected.",  							
					".$user->follower_count.",". $user->tweet_count.",
					". ($has_friend_count ? $user->friend_count.", " : "")."
					". ($has_last_post ? "'".mysql_real_escape_string($user->last_post)."', " : "")."					
					'".mysql_real_escape_string($user->found_in)."', '".mysql_real_escape_string($user->joined)."'
					 ". ($has_last_status_id ? ",".$user->last_status_id : "")."
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
					". ($has_friend_count ? "friend_count= ".$user->friend_count.", " : "")."
					". ($has_last_post ? "last_post= '".mysql_real_escape_string($user->last_post)."', " : "")."
					last_updated = NOW(),
					found_in = '".mysql_real_escape_string($user->found_in) . "', 
					joined = '".mysql_real_escape_string($user->joined)."'
					".($has_last_status_id ? ", last_status_id = ".$user->last_status_id : "").";";  
		$foo = Database::exec($q);
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
		$q	= "
			SELECT 
				* , ". $this->getAverageTweetCount()."
			FROM
				users u 
			WHERE 
				u.user_id = ". $user_id. ";";
		$sql_result = Database::exec($q);
		$row = mysql_fetch_assoc($sql_result);
		mysql_free_result($sql_result);	
		return $row;		
	}

	function getUserByName($user_name) {
		$q	= "
			SELECT 
				* , ". $this->getAverageTweetCount()."
			FROM
				users u 
			WHERE 
				u.user_name = '". $user_name. "';";
		$sql_result = Database::exec($q);
		$row = mysql_fetch_assoc($sql_result);
		mysql_free_result($sql_result);	
		return $row;		
	}



	
}

class UserErrorDAO {
	function insertError($id, $error_code, $error_text, $issued_to) {
		$q = "
			INSERT INTO
			 	user_errors (user_id, error_code, error_text, error_issued_to_user_id)
			VALUES 
				(".$id.", ".$error_code.", '".$error_text."', ".$issued_to.") ";
		$sql_result = Database::exec($q);
		if (mysql_affected_rows() > 0)
			return true;
		else
			return false;
	}
}

?>