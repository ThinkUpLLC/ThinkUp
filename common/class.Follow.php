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
		$sql_result = Database::exec($q);
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
		$sql_result = Database::exec($q);
		if (mysql_affected_rows() > 0)
			return true;
		else
			return false;
	}

	function deactivate($user_id, $follower_id) {
		$q = "
			UPDATE 
			 	follows
			SET
				active = 0
			WHERE
				user_id = ".$user_id." AND follower_id=".$follower_id.";";
		$sql_result = Database::exec($q);
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
		$foo = Database::exec($q);
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
				AND f.follower_id NOT IN (SELECT user_id FROM user_errors)
			LIMIT 100;";
		$sql_result = Database::exec($q);
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
		$sql_result = Database::exec($q);
		$ferrors = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $ferrors[] = $row; }
		mysql_free_result($sql_result);	
		return $ferrors[0]['follows_with_errors'];		
		
	}

	function getTotalFriendsWithErrors($user_id) {
		$q = "
			SELECT
				count(follower_id) as friends_with_errors
			FROM 
				follows f 
			WHERE 
				f.follower_id=".$user_id."
				AND f.user_id IN (SELECT user_id FROM user_errors WHERE error_issued_to_user_id=".$user_id.");";
		$sql_result = Database::exec($q);
		$ferrors = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $ferrors[] = $row; }
		mysql_free_result($sql_result);	
		return $ferrors[0]['friends_with_errors'];		
		
	}

	
	function getTotalFollowsWithFullDetails($user_id) {
		$q = "
			 SELECT count( * ) as follows_with_details
			FROM `follows` f
			INNER JOIN users u ON u.user_id = f.follower_id
			WHERE f.user_id = ".$user_id;
		$sql_result = Database::exec($q);
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
		$sql_result = Database::exec($q);
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
		$sql_result = Database::exec($q);
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
		$sql_result = Database::exec($q);
		$details = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $details[] = $row; }
		mysql_free_result($sql_result);	
		return $details[0]['friends_protected'];		
	}
	
	function getStalestFriend($user_id) {
		$q = "
			SELECT
				u.*
			FROM 
				users u
			INNER JOIN
				follows f
			ON
			 	f.user_id = u.user_id
			WHERE 
				f.follower_id=".$user_id." 
				AND u.user_id NOT IN (SELECT user_id FROM user_errors) 
				AND u.last_updated < DATE_SUB(NOW(), INTERVAL 1 DAY)
			ORDER BY
				u.last_updated ASC
			LIMIT 1;";
		$sql_result = Database::exec($q);
		$oldfriend = array();
		if ( mysql_num_rows($sql_result) > 0 ) {
			while ($row = mysql_fetch_assoc($sql_result)) { $oldfriend[] = $row; }
			mysql_free_result($sql_result);
			$friend_object = new User($oldfriend[0], "Friends");
		} else {
			$friend_object = null;
		}
		return $friend_object;
	}
	
	function getOldestFollow() {
		$q = "
			SELECT
				user_id as followee_id, follower_id
			FROM 
				follows f
			WHERE
				active = 1
			ORDER BY
				f.last_seen ASC
			LIMIT 1;";
		$sql_result = Database::exec($q);
		$oldfollow = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $oldfollow[] = $row; }
		mysql_free_result($sql_result);
		return $oldfollow[0];
	}

	
	private function getAverageTweetCount() {
		return "round(tweet_count/(datediff(curdate(), joined)), 2) as avg_tweets_per_day";
	}
	

	function getMostFollowedFollowers($user_id, $count) {
		$q		= "
			SELECT 
				* , ". $this->getAverageTweetCount()."
			FROM 
				users u 
			INNER JOIN
			 	follows f 
			ON 
				u.user_id = f.follower_id 
			WHERE
				f.user_id = ".$user_id." and active=1
			ORDER BY 
				u.follower_count DESC 
			LIMIT ".$count.";";
		$sql_result = Database::exec($q);
		$most_followed_followers 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $most_followed_followers[] = $row; } 
		mysql_free_result($sql_result);	

		return $most_followed_followers;
		
	}


	function getLeastLikelyFollowers($user_id, $count) {
		
		//TODO: Remove hardcoded 10k follower threshold in query below
		$q	= "
			SELECT 
				*, ROUND(100*friend_count/follower_count,4) AS LikelihoodOfFollow, ". $this->getAverageTweetCount()."
			FROM 
				users u 
			INNER JOIN
			 	follows f 
			ON 
				u.user_id = f.follower_id
			WHERE
				f.user_id =  ".$user_id." and active=1 and follower_count > 10000 and friend_count > 0
			ORDER BY 
				LikelihoodOfFollow ASC #u.follower_count DESC
			LIMIT ".$count.";";
		$sql_result = Database::exec($q);
		$least_likely_followers 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $least_likely_followers[] = $row; } 
		mysql_free_result($sql_result);	

		return $least_likely_followers;
		
	}

	function getEarliestJoinerFollowers($user_id, $count) {
		$q = "
			SELECT 
				*, ". $this->getAverageTweetCount()."
			FROM 
				users u 
			INNER JOIN
			 	follows f 
			ON 
				u.user_id = f.follower_id 
			WHERE
				f.user_id =  ".$user_id." and active=1
			ORDER BY 
				u.user_id ASC
			LIMIT ".$count.";";
		$sql_result = Database::exec($q);
		$earliest_joiner_followers 		= array();
		$least_likely_followers = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $least_likely_followers[] = $row; } 
		mysql_free_result($sql_result);	

		return $least_likely_followers;
		
	}	
	
	function getMostActiveFollowees($user_id, $count) {
		$q = "
			select 
				*, ". $this->getAverageTweetCount()." 
			from 
				users u 
			inner join 
				follows f 
			on 
				f.user_id = u.user_id 
			where 
				f.follower_id = ".$user_id." and active=1
			order by 
				avg_tweets_per_day DESC 
			LIMIT ".$count;		
			
		$sql_result = Database::exec($q);
		$most_active_friends 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $most_active_friends[] = $row; } 
		mysql_free_result($sql_result);	

		return $most_active_friends;
		
	}

	function getFormerFollowees($user_id, $count) {
		$q = "
			select 
				*
			from 
				users u 
			inner join 
				follows f 
			on 
				f.user_id = u.user_id 
			where 
				f.follower_id = ".$user_id." and active=0
			order by 
				u.follower_count DESC 
			LIMIT ".$count;		
			
		$sql_result = Database::exec($q);
		$most_active_friends 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $most_active_friends[] = $row; } 
		mysql_free_result($sql_result);	

		return $most_active_friends;
		
	}

	function getFormerFollowers($user_id, $count) {
		$q = "
			select 
				u.* 
			from 
				users u 
			inner join 
				follows f 
			on 
				f.follower_id = u.user_id 
			where 
				f.user_id = ".$user_id." and active=0
			order by 
				u.follower_count DESC 
			LIMIT ".$count;		
			
		$sql_result = Database::exec($q);
		$most_active_friends 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $most_active_friends[] = $row; } 
		mysql_free_result($sql_result);	

		return $most_active_friends;
		
	}



	function getLeastActiveFollowees($user_id, $count) {
		$q = "
			select 
				*, ". $this->getAverageTweetCount()."
			from 
				users u 
			inner join 
				follows f 
			on 
				f.user_id = u.user_id 
			where 
				f.follower_id = ".$user_id." and active=1
			order by 
				avg_tweets_per_day ASC 
			LIMIT ".$count;		
			
		$sql_result = Database::exec($q);
		$most_active_friends 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $most_active_friends[] = $row; } 
		mysql_free_result($sql_result);	

		return $most_active_friends;
		
	}


	function getMostFollowedFollowees($user_id, $count) {
		$q = "
			select 
				*, ". $this->getAverageTweetCount()."
			from 
				users u 
			inner join 
				follows f 
			on 
				f.user_id = u.user_id 
			where 
				f.follower_id = ".$user_id." and active=1
			order by 
				follower_count DESC 
			LIMIT ".$count;		
			
		$sql_result = Database::exec($q);
		$most_followed_friends 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $most_followed_friends[] = $row; } 
		mysql_free_result($sql_result);	

		return $most_followed_friends;
		
	}

	function getMutualFriends($uid, $instance_uid) {
		$q = "
			SELECT
			 u.*, ". $this->getAverageTweetCount()."
			FROM
			 follows f
			INNER JOIN
			 users u
			ON
			 u.user_id = f.user_id
			WHERE 
			 follower_id = ".$instance_uid."
			 AND f.user_id IN 
			 ( SELECT user_id FROM follows WHERE follower_id = ".$uid." and active=1)
			ORDER BY 
			 follower_count ASC;";
			
		$sql_result = Database::exec($q);
		$mutual_friends 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $mutual_friends[] = $row; } 
		mysql_free_result($sql_result);	

		return $mutual_friends;
	}
	
	function getFriendsNotFollowingBack($uid) {
		$q = "
			SELECT 
				u.* 
			FROM 
				follows f
			INNER JOIN
			 	users u
			ON 
				f.user_id = u.user_id
			WHERE 
				f.follower_id = ".$uid."
			 	AND f.user_id NOT IN (SELECT follower_id FROM follows WHERE user_id = ".$uid.")
			ORDER BY follower_count	";
			
		$sql_result = Database::exec($q);
		$nonmutual_friends 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $nonmutual_friends[] = $row; } 
		mysql_free_result($sql_result);	

		return $nonmutual_friends;
	}
}

?>
