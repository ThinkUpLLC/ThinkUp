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
				tweet_text, pub_date, status_id, author_username, author_user_id 
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
			
		//TODO Fix hardcoded adjusted pub_date
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
	
	function getTweetsAuthorHasRepliedTo($author_id) {
		//TODO Fix hardcoded adjusted pub_date

		$sql_query = "
		
			SELECT
				t1.author_username as questioner, t1.author_avatar as questioner_avatar, t1.status_id, t1.tweet_html as question, t1.pub_date - interval 8 hour as question_adj_pub_date, t.tweet_html as answer, t.pub_date - interval 8 hour as answer_adj_pub_date
			FROM 
				tweets t 
			INNER JOIN 
				tweets t1 on t1.status_id = t.in_reply_to_status_id 
			WHERE 
				t.author_user_id = ". $author_id   ." AND t.in_reply_to_status_id is not null 
			ORDER BY
				t.pub_date desc;";
			$sql_result = mysql_query($sql_query)  or die('Error, selection query failed:'. $sql_query);
			$tweets_replied_to 		= array();
			while ($row = mysql_fetch_assoc($sql_result)) { $tweets_replied_to[] = $row; } 
			mysql_free_result($sql_result);					# Free up memory
			return $tweets_replied_to;
		
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


			$sql_query['Add_Tweet'] = "
				INSERT INTO tweets
					(status_id,
					author_username,author_fullname,author_avatar,author_user_id,
					tweet_text,tweet_html,pub_date,in_reply_to_user_id,in_reply_to_status_id)
				VALUES (
					{$tweet['status_id']}, '{$tweet['user_name']}', 
					'{$tweet['full_name']}', '{$tweet['avatar']}', '{$tweet['user_id']}',
					'$tweet_sql','$tweet_html_sql',
					'{$tweet['pub_date']}', $tweet_in_reply_to_user_id, $tweet_in_reply_to_status_id)
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
//		echo $q;
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
		//TODO Fix hardcoded adjusted pub_date
		
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
		//TODO Fix hardcoded adjusted pub_date
		
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
		//TODO Fix hardcoded adjusted pub_date
		
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
		//TODO Fix hardcoded adjusted pub_date
		
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
				in_reply_to_status_id is null
			ORDER BY 
				pub_date DESC 
			LIMIT ".$count.";";
		$sql_result = mysql_query($sql_query)  or die("Error, selection query failed: $sql_query");
		$orphan_replies 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $orphan_replies[] = $row; }		
		mysql_free_result($sql_result);	
		return $orphan_replies;
		
	}
	
	
	function getLikelyOrphansForParent($parent_pub_date, $owner_user_id, $count) {
		//TODO Fix hardcoded adjusted pub_date
		
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
				in_reply_to_user_id = ". $owner_user_id ."
			AND
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
	
	function getStrayRepliedToTweets($author_id) {
		$q = "
			SELECT
				in_reply_to_status_id
			FROM 
				tweets t 
			WHERE 
				t.in_reply_to_status_id NOT IN (select status_id from tweets) 
			 	AND t.author_user_id=".$author_id.";";
		$sql_result = mysql_query($q)  or die("Error, selection query failed: $sql_query");
		$strays = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $strays[] = $row; }
		mysql_free_result($sql_result);	
		return $strays;
	}
	
	
}

?>