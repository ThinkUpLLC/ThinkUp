<?php

class FollowDAO extends MySQLDAO {

	function followExists($user_id, $follower_id) {
		$q = " SELECT user_id, follower_id ";
		$q .= " FROM #prefix#follows ";
		$q .= " WHERE user_id = %s AND follower_id=%s;";

		$q = sprintf($q, mysql_real_escape_string($user_id),mysql_real_escape_string($follower_id) );

		$sql_result = $this->executeSQL($q);
		if (mysql_num_rows($sql_result) > 0) {
			return true;
		} else {
			return false;
		}
	}

	function update($user_id, $follower_id) {
		$q = " UPDATE #prefix#follows ";
		$q .= " SET last_seen=NOW() ";
		$q .= " WHERE user_id = %s AND follower_id= %s;";

		$q = sprintf($q, mysql_real_escape_string($user_id), mysql_real_escape_string($follower_id) );

		$sql_result = $this->executeSQL($q);

		if (mysql_affected_rows() > 0){
			return true;
		} else {
			return false;
		}
	}

	function deactivate($user_id, $follower_id) {
		$q = " UPDATE #prefix#follows ";
		$q .= " SET active = 0 ";
		$q .= " WHERE user_id = %s AND follower_id= %s;";

		$q = sprintf($q, mysql_real_escape_string($user_id), mysql_real_escape_string($follower_id) );

		$sql_result = $this->executeSQL($q);
		if (mysql_affected_rows() > 0){
			return true;
		} else {
			return false;
		}
	}

	function insert($user_id, $follower_id) {
		$q = "INSERT INTO #prefix#follows (user_id, follower_id, last_seen) ";
		$q .= "	VALUES ( %s, %s, NOW() );";

		$q = sprintf($q, mysql_real_escape_string($user_id), mysql_real_escape_string($follower_id) );

		$sql_result = $this->executeSQL($q);

		if (mysql_affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}

	function getUnloadedFollowerDetails($user_id) {
		$q = "SELECT follower_id FROM #prefix#follows f ";
		$q .= "WHERE f.user_id=%s ";
		$q .= "AND f.follower_id NOT IN (SELECT user_id FROM #prefix#users) ";
		$q .= "AND f.follower_id NOT IN (SELECT user_id FROM #prefix#user_errors) LIMIT 100;";

		$q = sprintf($q, mysql_real_escape_string($user_id));

		$sql_result = $this->executeSQL($q);
		$strays = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$strays[] = $row;
		}
		mysql_free_result($sql_result);
		return $strays;
	}


	function getTotalFollowsWithErrors($user_id) {
		$q = "SELECT count(follower_id) as follows_with_errors ";
		$q .= "FROM  #prefix#follows f WHERE ";
		$q .= "f.user_id=%s AND ";
		$q .= "f.follower_id IN (SELECT user_id FROM #prefix#user_errors WHERE error_issued_to_user_id=%s);";

		$q = sprintf($q, mysql_real_escape_string($user_id), mysql_real_escape_string($user_id));

		$sql_result = $this->executeSQL($q);
		$ferrors = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$ferrors[] = $row;
		}
		mysql_free_result($sql_result);
		return $ferrors[0]['follows_with_errors'];
	}

	function getTotalFriendsWithErrors($user_id) {
		$q = "SELECT count(follower_id) as friends_with_errors FROM #prefix#follows f ";
		$q .= "WHERE f.follower_id=%s AND ";
		$q .= "f.user_id IN (SELECT user_id FROM #prefix#user_errors WHERE error_issued_to_user_id=%s);";

		$q = sprintf($q, mysql_real_escape_string($user_id), mysql_real_escape_string($user_id));

		$sql_result = $this->executeSQL($q);
		$ferrors = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$ferrors[] = $row;
		}
		mysql_free_result($sql_result);
		return $ferrors[0]['friends_with_errors'];
	}


	function getTotalFollowsWithFullDetails($user_id) {
		$q = "SELECT count( * ) as follows_with_details FROM #prefix#follows f ";
		$q .= "INNER JOIN #prefix#users u ON u.user_id = f.follower_id ";
		$q .= "WHERE f.user_id = %s";

		$q = sprintf($q, mysql_real_escape_string($user_id));

		$sql_result = $this->executeSQL($q);
		$details = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$details[] = $row;
		}
		mysql_free_result($sql_result);
		return $details[0]['follows_with_details'];
	}

	function getTotalFollowsProtected($user_id) {
		$q = "SELECT count( * ) as follows_protected FROM #prefix#follows f ";
		$q .= "INNER JOIN #prefix#users u ON u.user_id = f.follower_id ";
		$q .= "WHERE f.user_id = %s AND u.is_protected=1";

		$q = sprintf($q, mysql_real_escape_string($user_id));

		$sql_result = $this->executeSQL($q);
		$details = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$details[] = $row;
		}
		mysql_free_result($sql_result);
		return $details[0]['follows_protected'];
	}

	function getTotalFriends($user_id) {
		$q = "SELECT count( * ) as total_friends FROM #prefix#follows f ";
		$q .= "INNER JOIN #prefix#users u ON u.user_id = f.user_id ";
		$q .= "WHERE f.follower_id = %s";

		$q = sprintf($q, mysql_real_escape_string($user_id));

		$sql_result = $this->executeSQL($q);
		$details = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$details[] = $row;
		}
		mysql_free_result($sql_result);
		return $details[0]['total_friends'];
	}

	function getTotalFriendsProtected($user_id) {
		$q = "SELECT count( * ) as friends_protected FROM #prefix#follows f ";
		$q .= "INNER JOIN #prefix#users u ON u.user_id = f.user_id ";
		$q .= "WHERE f.follower_id = %s AND u.is_protected=1";

		$q = sprintf($q, mysql_real_escape_string($user_id));

		$sql_result = $this->executeSQL($q);
		$details = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$details[] = $row;
		}
		mysql_free_result($sql_result);
		return $details[0]['friends_protected'];
	}

	function getStalestFriend($user_id) {
		$q = "SELECT u.* FROM #prefix#users u INNER JOIN #prefix#follows f ON f.user_id = u.user_id ";
		$q .= "WHERE f.follower_id=%s ";
		$q .= "AND u.user_id NOT IN (SELECT user_id FROM #prefix#user_errors) ";
		$q .= "AND u.last_updated < DATE_SUB(NOW(), INTERVAL 1 DAY) ";
		$q .= "ORDER BY u.last_updated ASC LIMIT 1;";

		$q = sprintf($q, mysql_real_escape_string($user_id));

		$sql_result = $this->executeSQL($q);
		$oldfriend = array();
		if (mysql_num_rows($sql_result) > 0) {
			while ($row = mysql_fetch_assoc($sql_result)) {
				$oldfriend[] = $row;
			}
			mysql_free_result($sql_result);
			$friend_object = new User($oldfriend[0], "Friends");
		} else {
			$friend_object = null;
		}
		return $friend_object;
	}

	function getOldestFollow() {
		$q = "SELECT  user_id as followee_id, follower_id FROM #prefix#follows f ";
		$q .= "WHERE active = 1 ORDER BY f.last_seen ASC LIMIT 1;";

		$sql_result = $this->executeSQL($q);

		$oldfollow = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$oldfollow[] = $row;
		}
		mysql_free_result($sql_result);
		if ( count($oldfollow) > 1 ) {
			return $oldfollow[0];
		} else {
			return null;
		}
	}


	private function getAverageTweetCount() {
		return "round(post_count/(datediff(curdate(), joined)), 2) as avg_tweets_per_day";
	}


	function getMostFollowedFollowers($user_id, $count) {
		$q = "SELECT * , ".$this->getAverageTweetCount()." ";
		$q .= "FROM #prefix#users u  INNER JOIN #prefix#follows f ";
		$q .= "ON u.user_id = f.follower_id ";
		$q .= "WHERE f.user_id = %s and active=1 ";
		$q .= "ORDER BY u.follower_count DESC LIMIT %s;";

		$q = sprintf($q, mysql_real_escape_string($user_id), mysql_real_escape_string($count));

		$sql_result = $this->executeSQL($q);
		$most_followed_followers = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$most_followed_followers[] = $row;
		}
		mysql_free_result($sql_result);

		return $most_followed_followers;

	}

	//TODO: Remove hardcoded 10k follower threshold in query below
	function getLeastLikelyFollowers($user_id, $count) {

		$q = "SELECT *, ROUND(100*friend_count/follower_count,4) AS LikelihoodOfFollow, ".$this->getAverageTweetCount()." ";
		$q .= "FROM #prefix#users u INNER JOIN #prefix#follows f ";
		$q .= "ON u.user_id = f.follower_id ";
		$q .= "WHERE f.user_id = %s  and active=1 and follower_count > 10000 and friend_count > 0 ";
		$q .= "ORDER BY LikelihoodOfFollow ASC #u.follower_count DESC LIMIT %s;";

		$q = sprintf($q, mysql_real_escape_string($user_id), mysql_real_escape_string($count));

		$sql_result = $this->executeSQL($q);
		$least_likely_followers = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$least_likely_followers[] = $row;
		}
		mysql_free_result($sql_result);

		return $least_likely_followers;
	}

	function getEarliestJoinerFollowers($user_id, $count) {
		$q = "SELECT *, ".$this->getAverageTweetCount()." FROM #prefix#users u ";
		$q .= "INNER JOIN #prefix#follows f ON u.user_id = f.follower_id ";
		$q .= "WHERE f.user_id = %s and active=1 ";
		$q .= "ORDER BY u.user_id ASC LIMIT %s;";

		$q = sprintf($q, mysql_real_escape_string($user_id), mysql_real_escape_string($count));

		$sql_result = $this->executeSQL($q);
		$earliest_joiner_followers = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$earliest_joiner_followers[] = $row;
		}
		mysql_free_result($sql_result);

		return $earliest_joiner_followers;
	}

	function getMostActiveFollowees($user_id, $count) {
		$q = "select *, ".$this->getAverageTweetCount()." from #prefix#users u ";
		$q .= "inner join #prefix#follows f on f.user_id = u.user_id ";
		$q .= "where f.follower_id = %s and active=1 ";
		$q .= "order by avg_tweets_per_day DESC LIMIT %s";

		$q = sprintf($q, mysql_real_escape_string($user_id), mysql_real_escape_string($count));
			
		$sql_result = $this->executeSQL($q);
		$most_active_friends = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$most_active_friends[] = $row;
		}
		mysql_free_result($sql_result);

		return $most_active_friends;

	}

	function getFormerFollowees($user_id, $count) {
		$q = "select * from #prefix#users u inner join #prefix#follows f ";
		$q .= "on f.user_id = u.user_id where f.follower_id = %s and active=0 ";
		$q .= "order by u.follower_count DESC LIMIT %s";

		$q = sprintf($q, mysql_real_escape_string($user_id), mysql_real_escape_string($count));
			
		$sql_result = $this->executeSQL($q);
		$former_friends = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$former_friends[] = $row;
		}
		mysql_free_result($sql_result);

		return $former_friends;
	}

	function getFormerFollowers($user_id, $count) {
		$q = "select u.* from #prefix#users u inner join #prefix#follows f ";
		$q .= "on f.follower_id = u.user_id where f.user_id = %s and active=0 ";
		$q .= "order by u.follower_count DESC LIMIT %s";

		$q = sprintf($q, mysql_real_escape_string($user_id), mysql_real_escape_string($count));

		$sql_result = $this->executeSQL($q);
		$former_followers = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$former_followers[] = $row;
		}
		mysql_free_result($sql_result);

		return $former_followers;
	}


	function getLeastActiveFollowees($user_id, $count) {
		$q = "select *, ".$this->getAverageTweetCount()." from #prefix#users u ";
		$q .= "inner join #prefix#follows f on f.user_id = u.user_id ";
		$q .= "where f.follower_id = %s and active=1 ";
		$q .= "order by avg_tweets_per_day ASC ";
		$q .= "LIMIT %s";

		$q = sprintf($q, mysql_real_escape_string($user_id), mysql_real_escape_string($count));
			
		$sql_result = $this->executeSQL($q);
		$least_active_followees = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$least_active_followees[] = $row;
		}
		mysql_free_result($sql_result);

		return $least_active_followees;
	}


	function getMostFollowedFollowees($user_id, $count) {
		$q = "select *, ".$this->getAverageTweetCount()." from #prefix#users u ";
		$q .= "inner join #prefix#follows f on f.user_id = u.user_id ";
		$q .= "where f.follower_id = %s and active=1 ";
		$q .= "order by follower_count DESC LIMIT %s";

		$q = sprintf($q, mysql_real_escape_string($user_id), mysql_real_escape_string($count));
			
		$sql_result = $this->executeSQL($q);
		$most_followed_friends = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$most_followed_friends[] = $row;
		}
		mysql_free_result($sql_result);

		return $most_followed_friends;
	}

	function getMutualFriends($uid, $instance_uid) {
		$q = "SELECT u.*, ".$this->getAverageTweetCount()." FROM #prefix#follows f ";
		$q .= "INNER JOIN #prefix#users u ON u.user_id = f.user_id ";
		$q .= "WHERE follower_id = %s AND f.user_id IN ";
		$q .= "( SELECT user_id FROM #prefix#follows WHERE follower_id = %s and active=1) ";
		$q .= "ORDER BY follower_count ASC;";

		$q = sprintf($q, mysql_real_escape_string($instance_uid), mysql_real_escape_string($uid));

		$sql_result = $this->executeSQL($q);
		$mutual_friends = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$mutual_friends[] = $row;
		}
		mysql_free_result($sql_result);

		return $mutual_friends;
	}

	function getFriendsNotFollowingBack($uid) {
		$q = "SELECT u.* FROM #prefix#follows f INNER JOIN #prefix#users u ";
		$q .= "ON f.user_id = u.user_id WHERE f.follower_id = %s ";
		$q .= "AND f.user_id NOT IN (SELECT follower_id FROM #prefix#follows WHERE user_id = %s) ";
		$q .= "ORDER BY follower_count	";
		
		$q = sprintf($q, mysql_real_escape_string($uid), mysql_real_escape_string($uid));
			
		$sql_result = $this->executeSQL($q);
		$nonmutual_friends = array();
		while ($row = mysql_fetch_assoc($sql_result)) {
			$nonmutual_friends[] = $row;
		}
		mysql_free_result($sql_result);

		return $nonmutual_friends;
	}
}

?>
