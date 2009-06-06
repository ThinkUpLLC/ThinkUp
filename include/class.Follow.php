<?php

class Follow {
	
//TODO set up this object and use it instead of associative arrays!
	
}


class FollowDAO {
	
	
	function getMostFollowedFollowers($user_id, $count) {
		$sql_query		= "
			SELECT 
				* 
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
		$sql_query		= "
			SELECT 
				*, ROUND(100*friend_count/follower_count,4) AS LikelihoodOfFollow 
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
				*
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
		while ($row = mysql_fetch_assoc($sql_result)) { $least_likely_followers[] = $row; } 
		mysql_free_result($sql_result);	

		return $least_likely_followers;
		
	}	
	
	function getMostActiveFollowees($user_id, $count) {
		$sql_query = "
			select 
				*, round(tweet_count/(datediff(curdate(), joined))) as avg_tweets_per_day 
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
				*, round(tweet_count/(datediff(curdate(), joined))) as avg_tweets_per_day 
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
				*, round(tweet_count/(datediff(curdate(), joined))) as avg_tweets_per_day 
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

?>
