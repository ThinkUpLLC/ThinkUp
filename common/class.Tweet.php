<?php

class Tweet {

	//TODO: start using object instead of assoc. array

	function Tweet($val) {
		
		
	}
	
}


class TweetDAO {

	function getTweet($status_id) {
		# get tweet
		$q	= "
			SELECT 
				tweet_text, pub_date, status_id, author_username, author_user_id, tweet_html 
			FROM 
				tweets 
			WHERE
			 	status_id=".$status_id.";";	
		$sql_result = Database::exec($q);
		$tweet 		= mysql_fetch_assoc($sql_result);
		mysql_free_result($sql_result);					# Free up memory
		return $tweet;
	}

	function getStandaloneReplies($username, $limit) {
		$q = "
			SELECT
				tweet_html, author_username, author_avatar, follower_count, status_id, is_protected, pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets t 
			inner join 
				users u 
			on 
				t.author_user_id = u.user_id 
			where 
				tweet_text 
			LIKE
				'%".$username."%'
				and
				in_reply_to_status_id=0
			order by 
				adj_pub_date desc
			LIMIT ".$limit;		
		$sql_result = Database::exec($q);
		$strays = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $strays[] = $row; }
		mysql_free_result($sql_result);	
		return $strays;	
	}
	
	function getRepliesToTweet($status_id, $public=false) {
		# get replies to tweet
		$condition = "";
		if ($public)
			$condition = "AND u.is_protected = 0";
			
		//TODO Fix hardcoded adjusted pub_date
		$q	= "
			select 
				tweet_html, author_username, author_avatar, location, follower_count, status_id, is_protected, pub_date - interval 8 hour as adj_pub_date 
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
		$sql_result = Database::exec($q);
		$tweets_stored 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $tweets_stored[] = $row; } 
		mysql_free_result($sql_result);					# Free up memory
		return $tweets_stored;
	}
	
	function getTweetsAuthorHasRepliedTo($author_id, $count) {
		//TODO Fix hardcoded adjusted pub_date

		$q = "
			SELECT
				t1.author_username as questioner, t1.author_avatar as questioner_avatar, t1.status_id, t1.tweet_html as question, t1.pub_date - interval 8 hour as question_adj_pub_date, t.author_username as answerer, t.author_avatar as answerer_avatar, t.tweet_html as answer, t.pub_date - interval 8 hour as answer_adj_pub_date
			FROM 
				tweets t 
			INNER JOIN 
				tweets t1 on t1.status_id = t.in_reply_to_status_id 
			WHERE 
				t.author_user_id = ". $author_id   ." AND t.in_reply_to_status_id is not null 
			ORDER BY
				t.pub_date desc 
			LIMIT ".$count.";";				

		$sql_result = Database::exec($q);
		$tweets_replied_to 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $tweets_replied_to[] = $row; } 
		mysql_free_result($sql_result);					# Free up memory
		return $tweets_replied_to;
		
	}

	function getExchangesBetweenUsers($author_id, $other_user_id) {
		//TODO Fix hardcoded adjusted pub_date

		$q = "
		
			SELECT
				t1.author_username as questioner, t1.author_avatar as questioner_avatar, t1.status_id, t1.tweet_html as question, t1.pub_date - interval 8 hour as question_adj_pub_date, t.author_username as answerer, t.author_avatar as answerer_avatar, t.tweet_html as answer, t.pub_date - interval 8 hour as answer_adj_pub_date
			FROM 
				tweets t 
			INNER JOIN 
				tweets t1 on t1.status_id = t.in_reply_to_status_id 
			WHERE 
				t.in_reply_to_status_id is not null AND
				(t.author_user_id = ". $author_id   ." AND t1.author_user_id = ". $other_user_id. ")
				OR
				(t1.author_user_id = ". $author_id   ." AND t.author_user_id = ". $other_user_id. ")
			ORDER BY
				t.pub_date desc";				

		$sql_result = Database::exec($q);
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


			$q = "
				INSERT INTO tweets
					(status_id,
					author_username,author_fullname,author_avatar,author_user_id,
					tweet_text,tweet_html,pub_date,in_reply_to_user_id,in_reply_to_status_id,source)
				VALUES (
					{$tweet['status_id']}, '{$tweet['user_name']}', 
					'{$tweet['full_name']}', '{$tweet['avatar']}', '{$tweet['user_id']}',
					'$tweet_sql','$tweet_html_sql',
					'{$tweet['pub_date']}', $tweet_in_reply_to_user_id, $tweet_in_reply_to_status_id,'{$tweet['source']}')
			";
			$foo = Database::exec($q);


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
		$sql_result = Database::exec($q);
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
		$sql_result = Database::exec($q);
		if ( mysql_num_rows($sql_result) > 0 )
			return true;
		else
			return false;
	}	
	
	function incrementReplyCountCache($status_id) {
		$q = "
			UPDATE 
				tweets
			SET 
				reply_count_cache = reply_count_cache + 1
			WHERE 
				status_id = ". $status_id."
		"; 
		//echo $sql_query;
		$foo = Database::exec($q);
		return mysql_affected_rows();
	}
	
	function decrementReplyCountCache($status_id) {
		$q = "
			UPDATE 
				tweets
			SET 
				reply_count_cache = reply_count_cache - 1
			WHERE 
				status_id = ". $status_id."
		"; 
		$foo = Database::exec($q);
		return mysql_affected_rows();
	}	
	
	function getAllTweets($author_id, $count) {
		//TODO Fix hardcoded adjusted pub_date
		
		$q 	= "
			SELECT 
				*, pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets
			WHERE 
				author_user_id = ".$author_id."
			ORDER BY 
				pub_date DESC 
			LIMIT ".$count.";";
		$sql_result = Database::exec($q);
		$all_tweets = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $all_tweets[] = $row; }		
		mysql_free_result($sql_result);			
		return $all_tweets;
	}

	function getAllTweetsByUsername($username) {
		//TODO Fix hardcoded adjusted pub_date
		
		$q		= "
			SELECT 
				*, pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets t
			WHERE 
				author_username = '".$username."'
			ORDER BY 
				pub_date ASC";
		$sql_result = Database::exec($q);
		$all_tweets = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $all_tweets[] = $row; }		
		mysql_free_result($sql_result);			
		return $all_tweets;
	}


	function getStatusSources($author_id) {
		$q	= "
			SELECT 
				source, count(source) as total 
			FROM 
				tweets
			WHERE 
				author_user_id = ".$author_id."			
			GROUP BY source
			ORDER BY total DESC;";
		$sql_result = Database::exec($q);
		$all_sources = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $all_sources[] = $row; }		
		mysql_free_result($sql_result);			
		return $all_sources;
	}


	
	function getAllMentions($author_username, $count) {
		//TODO Fix hardcoded adjusted pub_date
		
		$q		= "
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
		$sql_result = Database::exec($q);
		$all_tweets = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $all_tweets[] = $row; }		
		mysql_free_result($sql_result);			
		return $all_tweets;
	}
	
	function getAllReplies($user_id, $count) {
		//TODO Fix hardcoded adjusted pub_date
		
		$q	= "
			SELECT 
				*, pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets t
			INNER JOIN
				users u
			ON
				t.author_user_id = u.user_id
			WHERE 
				 in_reply_to_user_id = ".$user_id."
			ORDER BY 
				pub_date DESC 
			LIMIT ".$count.";";
		$sql_result = Database::exec($q);
		$all_tweets = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $all_tweets[] = $row; }		
		mysql_free_result($sql_result);			
		return $all_tweets;
	}	


	function getMostRepliedToTweets($user_id, $count) {
		//TODO Fix hardcoded adjusted pub_date
		
		$q		= "
			SELECT 
				* , pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets
			WHERE
				author_user_id = ".$user_id."
			ORDER BY
				reply_count_cache DESC 
			LIMIT ".$count.";";
		$sql_result = Database::exec($q);
		$most_replied_to_tweets 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $most_replied_to_tweets[] = $row; } 
		mysql_free_result($sql_result);	
		return $most_replied_to_tweets;
		
	}
	
	function getOrphanReplies($user_name, $count) {
		//TODO Fix hardcoded adjusted pub_date
		
		$q	= "
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
		$sql_result = Database::exec($q);
		$orphan_replies 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $orphan_replies[] = $row; }		
		mysql_free_result($sql_result);	
		return $orphan_replies;
		
	}
	
	
	function getLikelyOrphansForParent($parent_pub_date, $author_user_id, $author_username, $count) {
		//TODO Fix hardcoded adjusted pub_date
		
		$q		= "
			SELECT 
				* , pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets t
			INNER JOIN
				users u
			ON
				t.author_user_id = u.user_id
			WHERE 
				tweet_text 	LIKE '%".$author_username."%'				
			AND
				pub_date > '". $parent_pub_date ."' 
			AND
				in_reply_to_status_id IS NULL
			AND
				t.author_user_id != ". $author_user_id ."
			ORDER BY 
				pub_date 
			ASC 
			LIMIT ". $count;
		$sql_result = Database::exec($q);
		$likely_orphans = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $likely_orphans[] = $row; }
		mysql_free_result($sql_result);	
		return $likely_orphans;
		
	}

	function assignParent($parent_id, $orphan_id, $former_parent_id=-1) {
		$q		= "
			UPDATE 
				tweets
			SET 
				in_reply_to_status_id = ".$parent_id."
			WHERE
				status_id = ".$orphan_id;
		Database::exec($q);
		if ( $parent_id > 0 )
			$this->incrementReplyCountCache($parent_id);
		elseif ($former_parent_id > 0)
			$this->decrementReplyCountCache($former_parent_id);		
		return mysql_affected_rows();
	}
	
	function getStrayRepliedToTweets($author_id) {
		$q = "
			SELECT
				in_reply_to_status_id
			FROM 
				tweets t 
			WHERE 
				t.author_user_id=".$author_id."
				AND t.in_reply_to_status_id NOT IN (select status_id from tweets) 
			 	AND t.in_reply_to_status_id NOT IN (select status_id from tweet_errors);";
		$sql_result = Database::exec($q);
		$strays = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $strays[] = $row; }
		mysql_free_result($sql_result);	
		return $strays;
	}
	
	function getTweetsByPublicInstances($count=15) {
		$q = "
			SELECT 
				*, pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets t
			INNER JOIN
				instances i
			ON
				t.author_user_id = i.twitter_user_id
			WHERE
				i.is_public = 1 and t.reply_count_cache > 0 and in_reply_to_status_id is NULL
			ORDER BY
				t.pub_date DESC
			LIMIT " . $count;
		$sql_result = Database::exec($q);
		$tweets = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $tweets[] = $row; }
		mysql_free_result($sql_result);	
		return $tweets;
	}

	function isTweetByPublicInstance($id) {
		$q = "
			SELECT 
				*, pub_date - interval 8 hour as adj_pub_date 
			FROM 
				tweets t
			INNER JOIN
				instances i
			ON
				t.author_user_id = i.twitter_user_id
			WHERE
				i.is_public = 1 and t.status_id = ".$id.";";
		$sql_result = Database::exec($q);
		if (mysql_num_rows($sql_result) > 0)
			$r = true;
		else
			$r = false;

		mysql_free_result($sql_result);
		return $r;
	}	

}

class TweetErrorDAO {
	function insertError($id, $error_code, $error_text, $issued_to) {
		$q = "
			INSERT INTO
			 	tweet_errors (status_id, error_code, error_text, error_issued_to_user_id)
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