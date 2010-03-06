<?php 
class Tweet {
    var $id;
    var $status_id;
    var $author_user_id;
    var $author_fullname;
    var $author_username;
    var $author_avatar;
    var $tweet_text;
    var $tweet_html;
    var $source;
    var $pub_date;
    var $adj_pub_date;
    var $in_reply_to_user_id;
    var $in_reply_to_status_id;
    var $mention_count_cache;
    var $in_retweet_of_status_id;
    var $retweet_count_cache;
    
    var $author; //optional user object
    
    function Tweet($val) {
        $this->id = $val["id"];
        $this->status_id = $val["status_id"];
        $this->author_user_id = $val["author_user_id"];
        $this->author_username = $val["author_username"];
        $this->author_avatar = $val["author_avatar"];
        $this->tweet_text = $val["tweet_text"];
        $this->tweet_html = $val["tweet_html"];
        $this->source = $val["source"];
        $this->pub_date = $val["pub_date"];
        $this->adj_pub_date = $val["adj_pub_date"];
        $this->in_reply_to_user_id = $val["in_reply_to_user_id"];
        $this->in_reply_to_status_id = $val["in_reply_to_status_id"];
        $this->mention_count_cache = $val["mention_count_cache"];
        $this->in_retweet_of_status_id = $val["in_retweet_of_status_id"];
        $this->retweet_count_cache = $val["retweet_count_cache"];
    }
    
    public static function extractURLs($tweet_text) {
        preg_match_all('!https?://[\S]+!', $tweet_text, $matches);
        return $matches[0];
    }

    
}


class TweetDAO extends MySQLDAO {
    //Construct is located in parent
    
    function getTweet($status_id) {
        $q = "
			SELECT 
				t.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
			FROM 
				#prefix#tweets t
			WHERE
			 	status_id=".$status_id.";";
        $sql_result = $this->executeSQL($q);
        $tweet = new Tweet(mysql_fetch_assoc($sql_result));
        mysql_free_result($sql_result); # Free up memory
        return $tweet;
    }
    
    private function setTweetWithAuthor($row) {
        $u = new User($row, '');
        $t = new Tweet($row);
        $t->author = $u;
        return $t;
    }
    
    private function setTweetWithAuthorAndLink($row) {
        $u = new User($row, '');
        $l = new Link($row);
        $t = new Tweet($row);
        $t->author = $u;
        $t->link = $l;
        return $t;
    }
    
    private function setTweetWithLink($row) {
        $l = new Link($row);
        $t = new Tweet($row);
        $t->link = $l;
        return $t;
    }

    
    function getStandaloneReplies($username, $limit) {
        $q = " SELECT t.*, u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#tweets AS t ";
        $q .= " INNER JOIN #prefix#users AS u ON t.author_user_id = u.user_id ";
        $q .= " WHERE  MATCH(`tweet_text`) AGAINST('%".$username."%') ";
        $q .= " AND in_reply_to_status_id=0 ";
        $q .= " ORDER BY adj_pub_date DESC ";
        $q .= " LIMIT ".$limit;
        $sql_result = $this->executeSQL($q);
        $strays = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $strays[] = $this->setTweetWithAuthor($row);
        }
        mysql_free_result($sql_result);
        return $strays;
    }
    
    function getRepliesToTweet($status_id, $public = false) {
        $condition = "";
        if ($public)
            $condition = "AND u.is_protected = 0";
        $q = " SELECT t.*, u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#tweets t ";
        $q .= " INNER JOIN #prefix#users AS u ON t.author_user_id = u.user_id ";
        $q .= " WHERE in_reply_to_status_id=".$status_id." ".$condition;
        $q .= " ORDER BY follower_count desc;";
        $sql_result = $this->executeSQL($q);
        $tweets_stored = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $tweets_stored[] = $this->setTweetWithAuthor($row);
        }
        mysql_free_result($sql_result); # Free up memory
        return $tweets_stored;
    }
    
    function getRetweetsOfTweet($status_id, $public = false) {
        $condition = "";
        if ($public)
            $condition = "AND u.is_protected = 0";
            
        $q = "
			select 
				t.*, u.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
			from 
				#prefix#tweets t
			inner join 
				#prefix#users u 
			on 
				t.author_user_id = u.user_id 
			where 
				in_retweet_of_status_id=".$status_id." 
				".$condition."	
			order by 
				follower_count desc;";
        $sql_result = $this->executeSQL($q);
        $tweets_stored = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $tweets_stored[] = $this->setTweetWithAuthor($row);
        }
        mysql_free_result($sql_result); # Free up memory
        return $tweets_stored;
    }
    
    function getTweetReachViaRetweets($status_id) {
        $q = "
			select 
				SUM(u.follower_count) as total
			from 
				#prefix#tweets t
			inner join 
				#prefix#users u 
			on 
				t.author_user_id = u.user_id 
			where 
				in_retweet_of_status_id=".$status_id." 
			order by 
				follower_count desc;";
        $sql_result = $this->executeSQL($q);
        $row = mysql_fetch_assoc($sql_result);
        mysql_free_result($sql_result); # Free up memory
        return $row['total'];
    }
    
    function getTweetsAuthorHasRepliedTo($author_id, $count) {
        //TODO: Figure out a better way to do this, only returns 1-1 exchanges, not back-and-forth threads
        
        $q = "
			SELECT
				t1.author_username as questioner, t1.author_avatar as questioner_avatar, t1.status_id, t1.tweet_html as question, t1.pub_date - interval #gmt_offset# hour as question_adj_pub_date, t.author_username as answerer, t.author_avatar as answerer_avatar, t.tweet_html as answer, t.pub_date - interval 8 hour as answer_adj_pub_date
			FROM 
				#prefix#tweets t 
			INNER JOIN 
				#prefix#tweets t1 on t1.status_id = t.in_reply_to_status_id 
			WHERE 
				t.author_user_id = ".$author_id." AND t.in_reply_to_status_id is not null 
			ORDER BY
				t.pub_date desc 
			LIMIT ".$count.";";
			
        $sql_result = $this->executeSQL($q);
        $tweets_replied_to = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $tweets_replied_to[] = $row;
        }
        mysql_free_result($sql_result); # Free up memory
        return $tweets_replied_to;
        
    }
    
    function getExchangesBetweenUsers($author_id, $other_user_id) {
    
        $q = "
		
			SELECT
				t1.author_username as questioner, t1.author_avatar as questioner_avatar, t1.status_id, t1.tweet_html as question, t1.pub_date - interval #gmt_offset# hour as question_adj_pub_date, t.author_username as answerer, t.author_avatar as answerer_avatar, t.tweet_html as answer, t.pub_date - interval 8 hour as answer_adj_pub_date
			FROM 
				#prefix#tweets t 
			INNER JOIN 
				#prefix#tweets t1 on t1.status_id = t.in_reply_to_status_id 
			WHERE 
				t.in_reply_to_status_id is not null AND
				(t.author_user_id = ".$author_id." AND t1.author_user_id = ".$other_user_id.")
				OR
				(t1.author_user_id = ".$author_id." AND t.author_user_id = ".$other_user_id.")
			ORDER BY
				t.pub_date desc";
				
        $sql_result = $this->executeSQL($q);
        $tweets_replied_to = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $tweets_replied_to[] = $row;
        }
        mysql_free_result($sql_result); # Free up memory
        return $tweets_replied_to;
        
    }

    
    function getPublicRepliesToTweet($status_id) {
        return $this->getRepliesToTweet($status_id, true);
    }
    
    function addTweet($vals, $owner, $logger) {
        if (!$this->isTweetInDB($vals['status_id'])) {
        
            foreach ($vals as $key=>$value) {
                $vals[$key] = mysql_real_escape_string($value);
            }
            $tweet_sql = $vals['tweet_text'];
            $tweet_html_sql = $vals['tweet_html'];
            if ($vals['in_reply_to_user_id'] == '') {
                $tweet_in_reply_to_user_id = 'NULL';
            } else {
                $tweet_in_reply_to_user_id = $vals['in_reply_to_user_id'];
            }
            
            if ($vals['in_reply_to_status_id'] == '') {
                $tweet_in_reply_to_status_id = 'NULL';
            } else {
                $tweet_in_reply_to_status_id = $vals['in_reply_to_status_id'];
            }
            if (isset($vals['in_retweet_of_status_id'])) {
                if ($vals['in_retweet_of_status_id'] == '') {
                    $tweet_in_retweet_of_status_id = 'NULL';
                } else {
                    $tweet_in_retweet_of_status_id = $vals['in_retweet_of_status_id'];
                }
            } else
                $tweet_in_retweet_of_status_id = 'NULL';

                
            $q = "
				INSERT INTO #prefix#tweets
					(status_id,
					author_username,author_fullname,author_avatar,author_user_id,
					tweet_text,tweet_html,pub_date,in_reply_to_user_id,in_reply_to_status_id,in_retweet_of_status_id,source)
				VALUES (
					{$vals['status_id']}, '{$vals['user_name']}', 
					'{$vals['full_name']}', '{$vals['avatar']}', '{$vals['user_id']}',
					'$tweet_sql','$tweet_html_sql',
					'{$vals['pub_date']}', $tweet_in_reply_to_user_id, $tweet_in_reply_to_status_id,$tweet_in_retweet_of_status_id,'{$vals['source']}')
			";
            $foo = $this->executeSQL($q);

            
            if ($vals['in_reply_to_status_id'] != '' && $this->isTweetInDB($vals['in_reply_to_status_id'])) {
                $this->incrementReplyCountCache($vals['in_reply_to_status_id']);
                $status_message = "Reply found for ".$vals['in_reply_to_status_id'].", ID: ".$vals["status_id"]."; updating reply cache count";
                $logger->logStatus($status_message, get_class($this));
                $status_message = "";
            }
            
            if (isset($vals['in_retweet_of_status_id']) && $vals['in_retweet_of_status_id'] != '' && $this->isTweetInDB($vals['in_retweet_of_status_id'])) {
                $this->incrementRetweetCountCache($vals['in_retweet_of_status_id']);
                $status_message = "Retweet of ".$vals['in_retweet_of_status_id'].", ID: ".$vals["status_id"]."; updating retweet cache count";
                $logger->logStatus($status_message, get_class($this));
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
				#prefix#tweets 
			WHERE status_id = ".$status_id;
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) > 0)
            return true;
        else
            return false;
    }
    
    function isReplyInDB($status_id) {
        $q = "
			SELECT 
				status_id 
			FROM 
				#prefix#tweets 
			WHERE 
				status_id = ".$status_id;
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) > 0)
            return true;
        else
            return false;
    }
    
    function incrementReplyCountCache($status_id) {
        return $this->incrementCacheCount($status_id, "mention");
    }
    
    function incrementRetweetCountCache($status_id) {
        return $this->incrementCacheCount($status_id, "retweet");
    }
    
    private function incrementCacheCount($status_id, $fieldname) {
        $q = "
			UPDATE 
				#prefix#tweets
			SET 
				".$fieldname."_count_cache = ".$fieldname."_count_cache + 1
			WHERE 
				status_id = ".$status_id."
		";
        $foo = $this->executeSQL($q);
        return mysql_affected_rows();
    }

    
    function decrementReplyCountCache($status_id) {
        $q = "
			UPDATE 
				#prefix#tweets
			SET 
				mention_count_cache = mention_count_cache - 1
			WHERE 
				status_id = ".$status_id."
		";
        $foo = $this->executeSQL($q);
        return mysql_affected_rows();
    }
    
    function getAllTweets($author_id, $count) {
        $q = "
			SELECT 
				l.*, t.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
			FROM 
				#prefix#tweets t
			LEFT JOIN
				#prefix#links l
			ON t.status_id = l.status_id
			WHERE 
				author_user_id = ".$author_id."
			ORDER BY 
				pub_date DESC 
			LIMIT ".$count.";";
			
        $sql_result = $this->executeSQL($q);
        $all_tweets = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $all_tweets[] = $this->setTweetWithLink($row);
        }
        mysql_free_result($sql_result);
        return $all_tweets;
    }
    
    function getAllTweetsByUsername($username) {
    
        $q = "
			SELECT 
				t.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
			FROM 
				#prefix#tweets t
			WHERE 
				author_username = '".$username."'
			ORDER BY 
				pub_date ASC";
        $sql_result = $this->executeSQL($q);
        $all_tweets = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $all_tweets[] = new Tweet($row);
        }
        mysql_free_result($sql_result);
        return $all_tweets;
    }

    
    function getStatusSources($author_id) {
        $q = "
			SELECT 
				source, count(source) as total 
			FROM 
				#prefix#tweets
			WHERE 
				author_user_id = ".$author_id."			
			GROUP BY source
			ORDER BY total DESC;";
        $sql_result = $this->executeSQL($q);
        $all_sources = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $all_sources[] = $row;
        }
        mysql_free_result($sql_result);
        return $all_sources;
    }

    
    function getAllMentions($author_username, $count) {
    
        $q = " SELECT l.*, t.*, u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#tweets AS t ";
        $q .= " INNER JOIN #prefix#users AS u ON t.author_user_id = u.user_id ";
        $q .= " LEFT JOIN #prefix#links AS l ON t.status_id = l.status_id ";
        $q .= " WHERE MATCH (`tweet_text`) AGAINST('%".$author_username."%') ";
        $q .= " ORDER BY pub_date DESC ";
        $q .= " LIMIT ".$count.";";
        $sql_result = $this->executeSQL($q);
        $all_tweets = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $all_tweets[] = $this->setTweetWithAuthorAndLink($row);
        }
        mysql_free_result($sql_result);
        return $all_tweets;
    }
    
    function getAllReplies($user_id, $count) {
    
        $q = "
			SELECT 
				l.*, t.*, u.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
			FROM 
				#prefix#tweets t
			LEFT JOIN
				#prefix#links l
			ON t.status_id = l.status_id				
			INNER JOIN
				#prefix#users u
			ON
				t.author_user_id = u.user_id
			WHERE 
				 in_reply_to_user_id = ".$user_id."
			ORDER BY 
				pub_date DESC 
			LIMIT ".$count.";";
        $sql_result = $this->executeSQL($q);
        $all_tweets = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $all_tweets[] = $this->setTweetWithAuthorAndLink($row);
        }
        mysql_free_result($sql_result);
        return $all_tweets;
    }

    
    function getMostRepliedToTweets($user_id, $count) {
        $q = "
			SELECT 
				l.*, t.* , pub_date - interval #gmt_offset# hour as adj_pub_date 
			FROM 
				#prefix#tweets t
			LEFT JOIN
				#prefix#links l
			ON t.status_id = l.status_id				
			WHERE
				author_user_id = ".$user_id."
			ORDER BY
				mention_count_cache DESC 
			LIMIT ".$count.";";
        $sql_result = $this->executeSQL($q);
        $most_replied_to_tweets = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $most_replied_to_tweets[] = $this->setTweetWithLink($row);
        }
        mysql_free_result($sql_result);
        return $most_replied_to_tweets;
        
    }
    
    function getMostRetweetedTweets($user_id, $count, $public = false) {
    
        $q = "
			SELECT 
				l.*, t.* , pub_date - interval #gmt_offset# hour as adj_pub_date 
			FROM 
				#prefix#tweets t
			LEFT JOIN
				#prefix#links l
			ON t.status_id = l.status_id				
			WHERE
				author_user_id = ".$user_id."
			ORDER BY
				retweet_count_cache DESC 
			LIMIT ".$count.";";
        $sql_result = $this->executeSQL($q);
        $most_retweeted_tweets = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $most_retweeted_tweets[] = $this->setTweetWithLink($row);
        }
        mysql_free_result($sql_result);
        return $most_retweeted_tweets;
        
    }
    
    function getOrphanReplies($username, $count) {
    
        $q = " SELECT t.* , u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#tweets AS t ";
        $q .= " INNER JOIN #prefix#users AS u ON u.user_id = t.author_user_id ";
        $q .= " WHERE ";
        $q .= " MATCH (`tweet_text`) AGAINST('%".$username."%') ";
        $q .= " AND in_reply_to_status_id is null ";
        $q .= " AND in_retweet_of_status_id is null ";
        $q .= " ORDER BY pub_date DESC ";
        $q .= " LIMIT ".$count.";";
        $sql_result = $this->executeSQL($q);
        $orphan_replies = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $orphan_replies[] = $this->setTweetWithAuthor($row);
        }
        mysql_free_result($sql_result);
        return $orphan_replies;
    }
    
    function getLikelyOrphansForParent($parent_pub_date, $author_user_id, $author_username, $count) {
    
        $q = " SELECT t.* , u.*, pub_date - interval #gmt_offset# hour as adj_pub_date ";
        $q .= " FROM #prefix#tweets AS t ";
        $q .= " INNER JOIN #prefix#users AS u ON t.author_user_id = u.user_id ";
        $q .= " WHERE ";
        $q .= " MATCH (`tweet_text`) AGAINST('%".$author_username."%') ";
        $q .= " AND pub_date > '".$parent_pub_date."' ";
        $q .= " AND in_reply_to_status_id IS NULL ";
        $q .= " AND in_retweet_of_status_id IS NULL ";
        $q .= " AND t.author_user_id != ".$author_user_id;
        $q .= " ORDER BY pub_date ASC ";
        $q .= " LIMIT ".$count;
        $sql_result = $this->executeSQL($q);
        $likely_orphans = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $likely_orphans[] = $this->setTweetWithAuthor($row);
        }
        mysql_free_result($sql_result);
        return $likely_orphans;
        
    }
    
    function assignParent($parent_id, $orphan_id, $former_parent_id = -1) {
        $q = "
			UPDATE 
				#prefix#tweets
			SET 
				in_reply_to_status_id = ".$parent_id."
			WHERE
				status_id = ".$orphan_id;
        $this->executeSQL($q);
        if ($parent_id > 0)
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
				#prefix#tweets t 
			WHERE 
				t.author_user_id=".$author_id."
				AND t.in_reply_to_status_id NOT IN (select status_id from #prefix#tweets) 
			 	AND t.in_reply_to_status_id NOT IN (select status_id from #prefix#tweet_errors);";
        $sql_result = $this->executeSQL($q);
        $strays = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $strays[] = $row;
        }
        mysql_free_result($sql_result);
        return $strays;
    }
    
    private function getTweetsByPublicInstancesOrderedBy($count = 15, $orderby = "pub_date") {
        $q = "
			SELECT 
				l.*, t.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
			FROM 
				#prefix#tweets t
			INNER JOIN
				#prefix#instances i
			ON
				t.author_user_id = i.twitter_user_id
			LEFT JOIN
				#prefix#links l
			ON t.status_id = l.status_id

			WHERE
				i.is_public = 1 and (t.mention_count_cache > 0 or t.retweet_count_cache > 0) and in_reply_to_status_id is NULL
			ORDER BY
				t.".$orderby." DESC
			LIMIT ".$count;
        $sql_result = $this->executeSQL($q);
        $tweets = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $tweets[] = $this->setTweetWithLink($row);
        }
        mysql_free_result($sql_result);
        return $tweets;
    }
    
    function getTweetsByPublicInstances($count = 15) {
        return $this->getTweetsByPublicInstancesOrderedBy($count, "pub_date");
    }
    
    function getPhotoTweetsByPublicInstances($count = 15) {
        $q = "
			SELECT 
				l.*, t.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
			FROM 
				#prefix#tweets t
			INNER JOIN
				#prefix#instances i
			ON
				t.author_user_id = i.twitter_user_id
			LEFT JOIN
				#prefix#links l
			ON t.status_id = l.status_id

			WHERE
				i.is_public = 1 and l.is_image = 1 
			ORDER BY
				t.pub_date DESC
			LIMIT ".$count;
        $sql_result = $this->executeSQL($q);
        $tweets = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $tweets[] = $this->setTweetWithLink($row);
        }
        mysql_free_result($sql_result);
        return $tweets;
    }
    
    function getLinkTweetsByPublicInstances($count = 15) {
        $q = "
			SELECT 
				l.*, t.*, pub_date - interval #gmt_offset# hour as adj_pub_date 
			FROM 
				#prefix#tweets t
			INNER JOIN
				#prefix#instances i
			ON
				t.author_user_id = i.twitter_user_id
			LEFT JOIN
				#prefix#links l
			ON t.status_id = l.status_id

			WHERE
				i.is_public = 1 and l.expanded_url != '' and l.is_image = 0 
			ORDER BY
				t.pub_date DESC
			LIMIT ".$count;
        $sql_result = $this->executeSQL($q);
        $tweets = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $tweets[] = $this->setTweetWithLink($row);
        }
        mysql_free_result($sql_result);
        return $tweets;
    }

    
    function getMostRepliedToTweetsByPublicInstances($count = 15) {
        return $this->getTweetsByPublicInstancesOrderedBy($count, "mention_count_cache");
    }
    
    function getMostRetweetedTweetsByPublicInstances($count = 15) {
        return $this->getTweetsByPublicInstancesOrderedBy($count, "retweet_count_cache");
    }

    
    function isTweetByPublicInstance($id) {
        $q = "
			SELECT 
				*, pub_date - interval #gmt_offset# hour as adj_pub_date 
			FROM 
				#prefix#tweets t
			INNER JOIN
				#prefix#instances i
			ON
				t.author_user_id = i.twitter_user_id
			WHERE
				i.is_public = 1 and t.status_id = ".$id.";";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) > 0)
            $r = true;
        else
            $r = false;
            
        mysql_free_result($sql_result);
        return $r;
    }
    
}

class TweetErrorDAO extends MySQLDAO {
    //Construct is located in parent
    
    function insertError($id, $error_code, $error_text, $issued_to) {
        $q = "
			INSERT INTO
			 	#prefix#tweet_errors (status_id, error_code, error_text, error_issued_to_user_id)
			VALUES 
				(".$id.", ".$error_code.", '".$error_text."', ".$issued_to.") ";
        $sql_result = $this->executeSQL($q);
        if (mysql_affected_rows() > 0)
            return true;
        else
            return false;
    }
}

class RetweetDetector {

    public function __construct() {
    }
    
    public static function isRetweet($tweet, $ownerName) {
        if (strpos(strtolower($tweet), strtolower("RT @".$ownerName)) === false)
            return false;
        else
            return true;
    }

    
    public static function detectOriginalTweet($retweet_text, $recentTweets) {
        $originalTweetId = false;
        foreach ($recentTweets as $t) {
            $snip = substr($t->tweet_text, 0, 12);
            if (strpos($retweet_text, $snip) != false)
                $originalTweetId = $t->status_id;
        }
        
        return $originalTweetId;
    }
}
?>
