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
		if ( isset(  $val['id'] ) ) {
			$this->id = $val['id'];
		}
		$this->username = $val['user_name'];
		$this->full_name = $val['full_name'];
		$this->user_id = $val['user_id'];
		$this->user_name = $val['user_name'];
		$this->avatar = $val['avatar'];
		$this->location = $val['location'];
		$this->description = $val['description'];
		$this->url = $val['url'];
		$this->is_protected = $val['is_protected'];
		if ($this->is_protected == '') {
			$this->is_protected = 0;
		} elseif ($this->is_protected == 'true') {
			$this->is_protected = 1;
		}
		$this->follower_count = $val['follower_count'];
		$this->tweet_count = $val['tweet_count'];
		if (isset($val['last_status_id'])) {
			$this->last_status_id = $val['last_status_id'];
		}
		if (isset($val['friend_count'])) {
			$this->friend_count = $val['friend_count'];
		}
		if (isset($val['last_post'])) {
			$this->last_post = $val['last_post'];
		}
		$this->joined = $val['joined'];
		$this->found_in = $found_in;
	}

}

class UserDAO extends MySQLDAO {

	private function getAverageTweetCount() {
		return "round(tweet_count/(datediff(curdate(), joined)), 2) as avg_tweets_per_day";
	}


	function isUserInDB($user_id) {
		$q = "SELECT user_id ";
		$q .= "FROM ^prefix^users ";
		$q .= "WHERE user_id = %s;";
		$q = sprintf($q, mysql_real_escape_string($user_id));
		$sql_result = $this->executeSQL($q);
		if (mysql_num_rows($sql_result) > 0) {
			return true;
		} else {
			return false;
		}
	}

	function isUserInDBByName($username) {
		$q = "SELECT user_id ";
		$q .= "FROM ^prefix^users ";
		$q .= "WHERE user_name = '%s'";
		$q = sprintf($q, mysql_real_escape_string($username));
		$sql_result = $this->executeSQL($q);
		if (mysql_num_rows($sql_result) > 0) {
			return true;
		} else {
			return false;
		}
	}


	function updateUsers($users_to_update) {
		$status_message = "";
		$count = 0;

		if (count($users_to_update) > 0) {
			$status_message .= count($users_to_update)." users queued for insert or update; ";
			foreach ($users_to_update as $user) {
				$count += $this->updateUser($user);
			}
			$status_message .= "$count users affected.";
		}

		if ( isset($this->logger) && $this->logger != null ) {
			$this->logger->logStatus($status_message, get_class($this));
			$status_message = "";
		}
		return $count;

	}

	function updateUser($user) {
		$status_message = "";
		$has_friend_count = $user->friend_count != '' ? true : false;
		$has_last_post = $user->last_post != '' ? true : false;
		$has_last_status_id = $user->last_status_id != '' ? true : false;

		$q = "
			INSERT INTO
				^prefix^users (user_id,
					user_name,full_name,avatar,location,
					description, url, is_protected,
					follower_count, tweet_count, ".($has_friend_count ? "friend_count, " : "")."
					".($has_last_post ? "last_post, " : "")."
					found_in, joined  ".($has_last_status_id ? ", last_status_id" : "").")
				VALUES (
					".mysql_real_escape_string($user->user_id).", 
					'".mysql_real_escape_string($user->user_name)."','".mysql_real_escape_string($user->full_name)."','".mysql_real_escape_string($user->avatar)."','".mysql_real_escape_string($user->location)."',  
					'".mysql_real_escape_string($user->description)."', '".mysql_real_escape_string($user->url)."',".$user->is_protected.",  							
					".$user->follower_count.",".$user->tweet_count.",
					".($has_friend_count ? $user->friend_count.", " : "")."
					".($has_last_post ? "'".mysql_real_escape_string($user->last_post)."', " : "")."					
					'".mysql_real_escape_string($user->found_in)."', '".mysql_real_escape_string($user->joined)."'
					 ".($has_last_status_id ? ",".$user->last_status_id : "")."
					)
				ON DUPLICATE KEY UPDATE 
					full_name = '".mysql_real_escape_string($user->full_name)."',
					avatar =  '".mysql_real_escape_string($user->avatar)."',
					location = '".mysql_real_escape_string($user->location)."',
					description = '".mysql_real_escape_string($user->description)."',
					url = '".mysql_real_escape_string($user->url)."',
					is_protected = ".$user->is_protected.",
					follower_count = ".$user->follower_count.",
					tweet_count = ".$user->tweet_count.",
					".($has_friend_count ? "friend_count= ".$user->friend_count.", " : "")."
					".($has_last_post ? "last_post= '".mysql_real_escape_string($user->last_post)."', " : "")."
					last_updated = NOW(),
					found_in = '".mysql_real_escape_string($user->found_in)."', 
					joined = '".mysql_real_escape_string($user->joined)."'
					".($has_last_status_id ? ", last_status_id = ".$user->last_status_id : "").";";
		$foo = $this->executeSQL($q);
		if (mysql_affected_rows() > 0) {
			if (isset($this->logger) && $this->logger != null ) {
				$status_message = "User ".$user->user_name." updated in system.";
				$this->logger->logStatus($status_message, get_class($this));
				$status_message = "";
			}
			return 1;
		} else {
			if (isset($this->logger) && $this->logger != null ) {
				$status_message = "User ".$user->user_name." was NOT updated in system.";
				$this->logger->logStatus($status_message, get_class($this));
				$status_message = "";
			}
			return 0;
		}
	}

	function getDetails($user_id) {
		$q = "SELECT * , ".$this->getAverageTweetCount()." ";
		$q .= "FROM ^prefix^users u ";
		$q .= "WHERE u.user_id = %s;";
		$q = sprintf($q, mysql_real_escape_string($user_id));
		$sql_result = $this->executeSQL($q);
		if (mysql_num_rows($sql_result) > 0) {
			$row = mysql_fetch_assoc($sql_result);
			mysql_free_result($sql_result);
			return new User($row, $row['found_in']);
		} else {
			return null;
		}
	}

	function getUserByName($user_name) {
		$q = "SELECT * , ".$this->getAverageTweetCount()." ";
		$q .= "FROM ^prefix^users u ";
		$q .= "WHERE u.user_name = '%s';";
		$q = sprintf($q, mysql_real_escape_string($user_name));
		$sql_result = $this->executeSQL($q);
		if (mysql_num_rows($sql_result) > 0) {
			$row = mysql_fetch_assoc($sql_result);
			mysql_free_result($sql_result);
			return new User($row, $row['found_in']);
		} else {
			return null;
		}

	}


}

class UserErrorDAO extends MySQLDAO {

	function insertError($id, $error_code, $error_text, $issued_to) {
		$q = "INSERT INTO ^prefix^user_errors (user_id, error_code, error_text, error_issued_to_user_id) ";
		$q .= "VALUES (%s, %s, '%s', %s) ";
		$q = sprintf($q, mysql_real_escape_string($id), mysql_real_escape_string($error_code), mysql_real_escape_string($error_text), mysql_real_escape_string($issued_to) );
		$sql_result = $this->executeSQL($q);
		if (mysql_affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}
}

?>
