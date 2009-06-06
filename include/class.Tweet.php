<?php

class Tweet {

	//TODO: start using object instead of assoc. array

	function Tweet($val) {
		
		
	}
	
}


class TweetDAO {

	function getTweet($status_id) {
		# get tweet
		$sql_query		= "
			SELECT 
				tweet_text, pub_date, status_id, author_username 
			FROM 
				tweets 
			WHERE
			 	status_id=".$status_id.";";	
		$sql_result = mysql_query($sql_query)  or die('Error, selection query failed: '.$sql_query);
		$tweet 		= mysql_fetch_assoc($sql_result);
		mysql_free_result($sql_result);					# Free up memory
		return $tweet;
	}

	function getStandaloneReplies() {
		$this->getRepliesToTweet(0);
	}
	
	function getRepliesToTweet($status_id, $public=false) {
		# get replies to tweet
		$condition = "";
		if ($public)
			$condition = "AND u.is_protected = 0";
		
		$sql_query		= "
			select 
				tweet_html, author_username, author_avatar, follower_count, status_id, is_protected, pub_date - interval 8 hour as adj_pub_date 
			from 
				tweets t
			inner join 
				users u 
			on 
				t.author_user_id = u.user_id 
			where 
				in_reply_to_status_id=".$status_id." 
				"
			. $condition ."	
			order by 
				follower_count desc;";	
		//echo $sql_query;
		$sql_result = mysql_query($sql_query)  or die('Error, selection query failed:'. $sql_query);
		$tweets_stored 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $tweets_stored[] = $row; } 
		mysql_free_result($sql_result);					# Free up memory
		return $tweets_stored;
	}
	
	function getPublicRepliesToTweet($status_id) {
		return $this->getRepliesToTweet($status_id, true);
	}
	
	function addTweet($tweet, $owner, $logger) {
		if ( !$this->isTweetInDB( $tweet['status_id'] ) ) {		
			$sql_query = array();
		
			foreach($tweet as $key => $value) {
				$tweet[$key] = mysql_real_escape_string($value);
			}
			$tweet_sql = $tweet['tweet_text'];
			$tweet_html_sql = $tweet['tweet_html'];
			if ( $tweet['in_reply_to_user_id'] == '') {
				$tweet_in_reply_to_user_id = 'NULL';
			} else {
				$tweet_in_reply_to_user_id = $tweet['in_reply_to_user_id'];
			}

			if ( $tweet['in_reply_to_status_id'] == '') {
				$tweet_in_reply_to_status_id = 'NULL';
			} else {
				$tweet_in_reply_to_status_id = $tweet['in_reply_to_status_id'];
			}
	
			# Check for Follow Friday
			if (stripos($tweet['tweet_text'], "#followfriday") !== false) 
				$tweet_is_follow_friday = 1;
			else
				$tweet_is_follow_friday = 0;
				
			# Check for Retweet
			if (stripos($tweet['tweet_text'], "RT @$owner->username") !== false || stripos($tweet['tweet_text'], "via @$owner->username") !== false ) 
				$tweet_is_retweet = 1;
			else
				$tweet_is_retweet = 0;

	
			$sql_query['Add_Tweet'] = "
				INSERT INTO tweets
					(status_id,
					author_username,author_fullname,author_avatar,author_user_id,
					tweet_text,tweet_html,pub_date,in_reply_to_user_id,in_reply_to_status_id,
					is_follow_friday, is_retweet)
				VALUES (
					{$tweet['status_id']}, '{$tweet['user_name']}', 
					'{$tweet['full_name']}', '{$tweet['avatar']}', '{$tweet['user_id']}',
					'$tweet_sql','$tweet_html_sql',
					'{$tweet['pub_date']}', $tweet_in_reply_to_user_id, $tweet_in_reply_to_status_id,
					$tweet_is_follow_friday, $tweet_is_retweet)
			";
			$foo = mysql_query($sql_query['Add_Tweet'])  or die('Error, insert query failed: ' . $sql_query['Add_Tweet']);


			if ( $tweet['in_reply_to_status_id'] != ''  && $this->isTweetInDB($tweet['in_reply_to_status_id']) ) {
				$this->incrementReplyCountCache($tweet['in_reply_to_status_id']);
				$status_message =  "Reply found for ".$tweet['in_reply_to_status_id'].", ID: ".$tweet["status_id"]."; updating reply cache count";									
				$logger->logStatus($status_message, get_class($this) );
				$status_message = "";
			}

			return mysql_affected_rows();
		} else {
			return 0;
		}

	}



	function isTweetInDB($status_id) {
		$q = "
			SELECT 
				status_id 
			FROM 
				tweets 
			WHERE status_id = ".$status_id;
		$sql_result = mysql_query($q) or die('Error: selection query failed:' .$q );
		if ( mysql_num_rows($sql_result) > 0 )
			return true;
		else
			return false;
	}

	function isReplyInDB($status_id) {
		$q = "
			SELECT 
				status_id 
			FROM 
				tweets 
			WHERE 
				status_id = ".$status_id;
		$sql_result = mysql_query($q) or die('Error: selection query failed:' .$q );
		if ( mysql_num_rows($sql_result) > 0 )
			return true;
		else
			return false;
	}	
	
	function incrementReplyCountCache($status_id) {
		$sql_query = "
			UPDATE 
				tweets
			SET 
				reply_count_cache = reply_count_cache + 1
			WHERE 
				status_id = ". $status_id."
		"; 
		//echo $sql_query;
		$foo = mysql_query($sql_query) or die('Error, update query failed: '.$sql_query);
		return mysql_affected_rows();
	}
	
	function getAllTweets($author_id, $count) {
		$sql_query		= "
			SELECT 
				*, pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets
			WHERE 
				author_user_id = ".$author_id."
			ORDER BY 
				pub_date DESC 
			LIMIT ".$count.";";
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$all_tweets = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $all_tweets[] = $row; }		
		mysql_free_result($sql_result);			
		return $all_tweets;
	}


	
	function getAllReplies($author_username, $count) {
		$sql_query		= "
			SELECT 
				*, pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets t
			INNER JOIN
				users u
			ON
				t.author_user_id = u.user_id
			WHERE 
				tweet_text 
			LIKE
				'%".$author_username."%'
			ORDER BY 
				pub_date DESC 
			LIMIT ".$count.";";
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$all_tweets = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $all_tweets[] = $row; }		
		mysql_free_result($sql_result);			
		return $all_tweets;
	}
		
	function getMostRepliedToTweets($user_id, $count) {
		
		$sql_query		= "
			SELECT 
				* , pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets
			WHERE
				author_user_id = ".$user_id."
			ORDER BY
				reply_count_cache DESC 
			LIMIT ".$count.";";
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$most_replied_to_tweets 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $most_replied_to_tweets[] = $row; } 
		mysql_free_result($sql_result);	
		return $most_replied_to_tweets;
		
	}
	
	function getOrphanReplies($user_name, $count) {
		
		$sql_query		= "
			SELECT 
				* , pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets t 
			INNER JOIN 
				users u 
			ON 
				u.user_id = t.author_user_id 
			WHERE 
				tweet_text LIKE '%".$user_name."%' AND
				in_reply_to_status_id is null AND
				is_retweet=0 AND 
				is_follow_friday=0 
			ORDER BY 
				pub_date DESC 
			LIMIT ".$count.";";
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$orphan_replies 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $orphan_replies[] = $row; }		
		mysql_free_result($sql_result);	
		return $orphan_replies;
		
	}
	
	function getRetweets($user_id, $count){
		$sql_query		= "
			SELECT 
				* , pub_date - interval 8 hour as adj_pub_date 
			FROM
				tweets t 
			INNER JOIN
				users u on u.user_id = t.author_user_id 
			WHERE
			 	is_retweet=1 and t.author_user_id = ".$user_id."
			LIMIT ".$count.";";
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$retweets 	= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $retweets[] = $row; } 
		mysql_free_result($sql_result);	
		return $retweets;
	}
	
	
	function getFollowFridays($user_id, $count) {
		$sql_query		= "
			SELECT 
				* , pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets t
			INNER JOIN
				users u 
			ON 
				u.user_id = t.author_user_id 
			WHERE 
				is_follow_friday=1 and author_user_id = ".$user_id."
			LIMIT ".$count.";";
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$followfridays 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $followfridays[] = $row; }
		mysql_free_result($sql_result);	
		return $followfridays;	
	}
	
	function getLikelyOrphansForParent($parent_pub_date, $owner_user_id, $count) {
		$sql_query		= "
			SELECT 
				* , pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets t
			INNER JOIN
				users u
			ON
				t.author_user_id = u.user_id
			WHERE 
				pub_date > '". $parent_pub_date ."' 
			AND
				in_reply_to_status_id IS NULL
			AND
				t.author_user_id != ". $owner_user_id ."
			ORDER BY 
				pub_date 
			ASC 
			LIMIT ". $count;
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$likely_orphans = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $likely_orphans[] = $row; }
		mysql_free_result($sql_result);	
		return $likely_orphans;
		
	}

	function assignParent($parent_id, $orphan_id) {
		$sql_query		= "
			UPDATE 
				tweets
			SET 
				in_reply_to_status_id = ".$parent_id."
			WHERE
				status_id = ".$orphan_id;
		mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$this->incrementReplyCountCache($parent_id);
		return mysql_affected_rows();
	}
	
	
}

?>