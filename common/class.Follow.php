<?php

class Follow {
	
//TODO set up this object and use it instead of associative arrays!
	
}


class FollowDAO {	
 
	function followExists($user_id, $follower_id) {
		$q = "
			SELECT 
				user_id, follower_id
			FROM 
				follows
			WHERE 
				user_id = ".$user_id." AND follower_id=".$follower_id.";";
		$sql_result = mysql_query($q) or die('Error, selection query failed:' .$q );
		if ( mysql_num_rows($sql_result) > 0 )
			return true;
		else
			return false;		
	}


	function update($user_id, $follower_id) {
		$q = "
			UPDATE 
			 	follows
			SET
				last_seen=NOW()
			WHERE
				user_id = ".$user_id." AND follower_id=".$follower_id.";";
		$sql_result = mysql_query($q) or die('Error, update failed:' .$q );
		if (mysql_affected_rows() > 0)
			return true;
		else
			return false;
	}
	
	function insert($user_id, $follower_id) {
		$q = "
			INSERT INTO
				follows (user_id,follower_id,last_seen)
				VALUES (
					".$user_id.",".$follower_id.",NOW()
				);";
		$foo = mysql_query($q) or die('Error, insert query failed: '. $q );
		if (mysql_affected_rows() > 0)
			return true;
		else
			return false;
	}
	
	function getUnloadedFollowerDetails($user_id) {
		$q = "
			SELECT
				follower_id
			FROM 
				follows f 
			WHERE 
				f.user_id=".$user_id."
				AND f.follower_id NOT IN (SELECT user_id FROM users) 
				AND f.follower_id NOT IN (SELECT user_id FROM user_errors);";
		$sql_result = mysql_query($q)  or die("Error, selection query failed: $sql_query");
		$strays = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $strays[] = $row; }
		mysql_free_result($sql_result);	
		return $strays;
		
	}
	
	function getTotalFollowsWithErrors($user_id) {
		$q = "
			SELECT
				count(follower_id) as follows_with_errors
			FROM 
				follows f 
			WHERE 
				f.user_id=".$user_id."
				AND f.follower_id IN (SELECT user_id FROM user_errors WHERE error_issued_to_user_id=".$user_id.");";
		$sql_result = mysql_query($q)  or die("Error, selection query failed: $sql_query");
		$ferrors = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $ferrors[] = $row; }
		mysql_free_result($sql_result);	
		return $ferrors[0]['follows_with_errors'];		
		
	}
	
	function getTotalFollowsWithFullDetails($user_id) {
		$q = "
			 SELECT count( * ) as follows_with_details
			FROM `follows` f
			INNER JOIN users u ON u.user_id = f.follower_id
			WHERE f.user_id = ".$user_id;
		$sql_result = mysql_query($q)  or die("Error, selection query failed: $sql_query");
		$details = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $details[] = $row; }
		mysql_free_result($sql_result);	
		return $details[0]['follows_with_details'];		
	}

	function getTotalFollowsProtected($user_id) {
		$q = "
			 SELECT count( * ) as follows_protected
			FROM `follows` f
			INNER JOIN users u ON u.user_id = f.follower_id
			WHERE f.user_id = ".$user_id." AND u.is_protected=1";
		$sql_result = mysql_query($q)  or die("Error, selection query failed: $sql_query");
		$details = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $details[] = $row; }
		mysql_free_result($sql_result);	
		return $details[0]['follows_protected'];		
	}

	function getTotalFriends($user_id) {
		$q = "
			 SELECT count( * ) as total_friends
			FROM `follows` f
			INNER JOIN users u ON u.user_id = f.user_id
			WHERE f.follower_id = ".$user_id."";
		$sql_result = mysql_query($q)  or die("Error, selection query failed: $sql_query");
		$details = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $details[] = $row; }
		mysql_free_result($sql_result);	
		return $details[0]['total_friends'];		
	}

	function getTotalFriendsProtected($user_id) {
		$q = "
			 SELECT count( * ) as friends_protected
			FROM `follows` f
			INNER JOIN users u ON u.user_id = f.user_id
			WHERE f.follower_id = ".$user_id." AND u.is_protected=1";
		$sql_result = mysql_query($q)  or die("Error, selection query failed: $sql_query");
		$details = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $details[] = $row; }
		mysql_free_result($sql_result);	
		return $details[0]['friends_protected'];		
	}


}

?>
