<?php

class Instance {
	var $id;
	var $twitter_username;
	var $twitter_user_id;
	var $twitter_password;
	var $last_status_id;
	var $last_page_fetched_followers;
	var $last_page_fetched_friends;
	var $last_page_fetched_replies;
	var $last_page_fetched_tweets;
	var $total_tweets_in_system;
	var $total_replies_in_system;
	var $total_follows_in_system;
	var $total_friends_in_system;
	var $total_users_in_system;
	var $is_archive_loaded_replies;
	var $is_archive_loaded_follows;
	var $is_archive_loaded_friends;
	var $crawler_last_run;
	var $earliest_reply_in_system;	
	var $api_calls_to_leave_unmade;
	var $avg_replies_per_day;
		
	function Instance($r) {
		$this->id = $r["id"];
		$this->twitter_username = $r['twitter_username'];
		$this->twitter_user_id = $r['twitter_user_id'];
		//TODO encrypt/decrypt here
		$this->twitter_password=InstanceDAO::unscramblePassword($r['twitter_password']);
		$this->last_status_id=$r['last_status_id'];
		$this->last_page_fetched_followers=$r['last_page_fetched_followers'];
		$this->last_page_fetched_replies=$r['last_page_fetched_replies'];
		$this->last_page_fetched_tweets=$r['last_page_fetched_tweets'];
		$this->total_tweets_in_system=$r['total_tweets_in_system'];
		$this->total_replies_in_system=$r['total_replies_in_system'];
		$this->total_follows_in_system=$r['total_follows_in_system'];
		$this->total_users_in_system=$r['total_users_in_system'];
		if ( $r['is_archive_loaded_replies'] == 1)
			$this->is_archive_loaded_replies = true;
		else
			$this->is_archive_loaded_replies = false;

		if ( $r['is_archive_loaded_follows'] == 1)
			$this->is_archive_loaded_follows = true;
		else
			$this->is_archive_loaded_follows = false;


		$this->crawler_last_run=$r['crawler_last_run'];
		$this->earliest_reply_in_system=$r['earliest_reply_in_system'];	
		$this->api_calls_to_leave_unmade=$r['api_calls_to_leave_unmade'];
		$this->avg_replies_per_day = $r['avg_replies_per_day'];
	}

}

class InstanceDAO {
	
	function getInstanceStalestOne() {
		return $this->getInstanceOneByLastRun("ASC");
	}
	
	function getInstanceFreshestOne() {
		return $this->getInstanceOneByLastRun("DESC");
	}
	
	function insert($id, $user, $pass) {
		$sql_query = "
			INSERT INTO 
				instances (`twitter_user_id`, `twitter_username`, `twitter_password`)
			 VALUES
				(".$id." , '".$user."', '". $this->scramblePassword($pass)."')";
		$sql_result = mysql_query($sql_query)  or die('Error, insert query failed:' .$sql_query );
		
		
	}
	
	private function getAverageReplyCount() {
		return "round(total_replies_in_system/(datediff(curdate(), earliest_reply_in_system)), 2) as avg_replies_per_day";
	}
	
	
	function getFreshestByOwnerId($owner_id) {
		$sql_query = "
			SELECT 
				* , ". $this->getAverageReplyCount() ."
			FROM 
				instances i
			INNER JOIN
				owner_instances oi
			ON 
				i.id = oi.instance_id
			WHERE 
				oi.owner_id = ".$owner_id."
			ORDER BY 
				crawler_last_run DESC";
		$sql_result = mysql_query($sql_query)  or die('Error, selection query failed:' .$sql_query );
		if (mysql_num_rows  ( $sql_result  ) == 0 ) {
			$i = null;
		} else {
			$row = mysql_fetch_assoc($sql_result);
			$i = new Instance($row);
		}
		mysql_free_result($sql_result);				
		return $i;
	}

	
	function getInstanceOneByLastRun($order) {
		$sql_query = "
			SELECT , ". $this->getAverageReplyCount() ."
				* 
			FROM 
				instances 
			ORDER BY 
				crawler_last_run
			".$order." LIMIT 1";
		$sql_result = mysql_query($sql_query)  or die('Error, selection query failed:' .$sql_query );
		$row = mysql_fetch_assoc($sql_result);
		$i = new Instance($row);
		mysql_free_result($sql_result);				
		return $i;
	}
	
	function getByUsername($username) {
		$sql_query = "
			SELECT 
				* , ". $this->getAverageReplyCount() ."
			FROM 
				instances 
			WHERE 
				twitter_username = '".$username."'";
		$sql_result = mysql_query($sql_query)  or die('Error, selection query failed:' .$sql_query );

		if (mysql_num_rows  ( $sql_result  ) == 0 ) {
			$i = null;
		} else {
			$row = mysql_fetch_assoc($sql_result);
			$i = new Instance($row);
		}
		mysql_free_result($sql_result);				
		return $i;
	}
	
	function save($i, $user_xml_total_tweets_by_owner, $logger, $api ) {
		$sql_query = array();
		if ($user_xml_total_tweets_by_owner != '')
			$owner_tweets =  "total_tweets_by_owner = ".$user_xml_total_tweets_by_owner.",";
		else
			$owner_tweets = '';
	
		if ( $i->is_archive_loaded_follows )
			$is_archive_loaded_follows = 1;
		else
			$is_archive_loaded_follows = 0;

		if ( $i->is_archive_loaded_replies )
			$is_archive_loaded_replies = 1;
		else
			$is_archive_loaded_replies = 0;
			
		$lsi = "";
		if ( $i->last_status_id != "" )
			$lsi = "last_status_id = ". $i->last_status_id .",";
			
		$sql_query['Save_Crawler_State'] = "
			UPDATE 
				instances
			SET
				".$lsi."
				last_page_fetched_followers = ".$i->last_page_fetched_followers.",
				last_page_fetched_replies = ".$i->last_page_fetched_replies.",
				last_page_fetched_tweets = ".$i->last_page_fetched_tweets.",
				crawler_last_run = NOW(),
				total_tweets_in_system = (select count(*) from tweets where author_user_id=".$i->twitter_user_id."),
				".$owner_tweets."
				total_replies_in_system = (select count(*) from tweets where tweet_text like '%@".$i->twitter_username."%'),
				total_follows_in_system = (select count(*) from follows where user_id=".$i->twitter_user_id."),
				total_users_in_system = (select count(*) from users),
				is_archive_loaded_follows = ". $is_archive_loaded_follows .",
				is_archive_loaded_replies = ". $is_archive_loaded_replies .",
				earliest_reply_in_system = (select
					pub_date
				from 
					tweets
				where tweet_text like '%@".$i->twitter_username."%'
				order by
					pub_date asc
				limit 1),
				earliest_tweet_in_system = (select
					pub_date
				from 
					tweets
				where author_user_id = ".$i->twitter_user_id."
				order by
					pub_date asc
				limit 1)
			WHERE
				twitter_user_id = ".$i->twitter_user_id.";";
		$foo = mysql_query($sql_query['Save_Crawler_State']) or die('Error, update query failed: '. $sql_query['Save_Crawler_State'] );

		$status_message="Updated ".$i->twitter_username."'s system status.";
		$logger->logStatus($status_message, get_class($this) );
		$status_message = "";
		
	}

	function isUserConfigured($un) {
		$q = "
			SELECT 
				twitter_username 
			FROM 
				instances
			WHERE 
				twitter_username = '".$un."'";
		$sql_result = mysql_query($q) or die('Error: selection query failed:' .$q );
		if ( mysql_num_rows($sql_result) > 0 )
			return true;
		else
			return false;
	}

	function getAllInstancesStalestFirst() {
		return $this->getAllInstances("ASC");
	}
	
	
	function getAllInstances($last_run="DESC") {
		$q = "
			SELECT 
				*, ". $this->getAverageReplyCount() ."
			FROM
				instances
			ORDER BY
				crawler_last_run
			".$last_run."";
		$sql_result = mysql_query($q)  or die('Error, selection query failed:'. $q);
		$instances 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $instances[] = new Instance($row); } 
		mysql_free_result($sql_result);					# Free up memory
		return $instances;
	}

	function getByOwnerId($id) {
		$q = "
			SELECT 
				*, ". $this->getAverageReplyCount() ."
			FROM
				owner_instances oi
			INNER JOIN
				instances i
			ON
				i.id = oi.instance_id
			WHERE
				oi.owner_id = ".$id."
			ORDER BY
				crawler_last_run 
			DESC;";
		$sql_result = mysql_query($q)  or die('Error, selection query failed:'. $q);
		$instances 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $instances[] = new Instance($row); } 
		mysql_free_result($sql_result);					# Free up memory
		return $instances;
	}

	function updatePassword($username, $password) {
		$q = "
			UPDATE 
				instances
			SET 
				twitter_password = '". $this->scramblePassword($password)."'
			WHERE 
				twitter_username = '".$username."';";
		$sql_result = mysql_query($q)  or die('Error, update query failed:'. $q);
		//echo $q;
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
	
	
	public static function scramblePassword ($password) {
		$salt = substr(str_pad(dechex(mt_rand()),8,'0',STR_PAD_LEFT),-8);
		$modified = $password.$salt;
		$secured = $salt . base64_encode(bin2hex(strrev(str_rot13($modified))));
	    return $secured;
	}

	public static function unscramblePassword ($stored_password) {
	    $salt = substr($stored_password,0,8);
	    $modified = substr($stored_password,8,strlen($stored_password)-8);
		$modified = str_rot13(strrev(pack("H*",base64_decode($modified))));
	    $password = substr($modified,0,strlen($modified)-8);
	    return $password;
	}
}

?>