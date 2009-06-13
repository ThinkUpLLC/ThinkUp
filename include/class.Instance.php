<?php

class Instance {
	var $owner_username;
	var $owner_user_id;
	var $owner_password;
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
		
	function Instance($r) {
		$this->owner_username = $r['twitter_username'];
		$this->owner_user_id = $r['twitter_user_id'];
		//TODO encrypt/decrypt here
		$this->owner_password=$r['twitter_password'];
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
	}

}

class InstanceDAO {
	
	function getStalest() {
		return $this->getOneByLastRun("ASC");
	}
	
	function getFreshest() {
		return $this->getOneByLastRun("DESC");
	}

	
	function getOneByLastRun($order) {
		$sql_query = "
			SELECT 
				* 
			FROM 
				instances 
			ORDER BY 
				crawler_last_run
			".$order;
		$sql_result = mysql_query($sql_query)  or die('Error, selection query failed:' .$sql_query );
		$row = mysql_fetch_assoc($sql_result);
		$i = new Instance($row);
		mysql_free_result($sql_result);				
		return $i;
	}
	
	function getByUsername($username) {
		$sql_query = "
			SELECT 
				* 
			FROM 
				instances 
			WHERE 
				twitter_username = '".$username."';";
		$sql_result = mysql_query($sql_query)  or die('Error, selection query failed:' .$sql_query );
		$row = mysql_fetch_assoc($sql_result);
		$i = new Instance($row);
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
			
	
		$sql_query['Save_Crawler_State'] = "
			UPDATE 
				instances
			SET
				last_status_id = ". $i->last_status_id .",
				last_page_fetched_followers = ".$i->last_page_fetched_followers.",
				last_page_fetched_replies = ".$i->last_page_fetched_replies.",
				last_page_fetched_tweets = ".$i->last_page_fetched_tweets.",
				crawler_last_run = NOW(),
				total_tweets_in_system = (select count(*) from tweets where author_user_id=".$i->owner_user_id."),
				".$owner_tweets."
				total_replies_in_system = (select count(*) from tweets where tweet_text like '%@".$i->owner_username."%'),
				total_follows_in_system = (select count(*) from follows where user_id=".$i->owner_user_id."),
				total_users_in_system = (select count(*) from users),
				is_archive_loaded_follows = ". $is_archive_loaded_follows .",
				is_archive_loaded_replies = ". $is_archive_loaded_replies .",
				earliest_reply_in_system = (select
					pub_date
				from 
					tweets
				where tweet_text like '%@".$i->owner_username."%'
				order by
					pub_date asc
				limit 1),
				earliest_tweet_in_system = (select
					pub_date
				from 
					tweets
				where author_user_id = ".$i->owner_user_id."
				order by
					pub_date asc
				limit 1)
			WHERE
				twitter_user_id = ".$i->owner_user_id.";";
		$foo = mysql_query($sql_query['Save_Crawler_State']) or die('Error, update query failed: '. $sql_query['Save_Crawler_State'] );

		$status_message="Updated ".$i->owner_username."'s system status.\n\n";
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
	
	
	function getAllInstances() {
		$q = "
			SELECT 
				*
			FROM
				instances
			ORDER BY
				crawler_last_run
			DESC;";
		$sql_result = mysql_query($q)  or die('Error, selection query failed:'. $q);
		$instances 		= array();
		while ($row = mysql_fetch_assoc($sql_result)) { $instances[] = new Instance($row); } 
		mysql_free_result($sql_result);					# Free up memory
		return $instances;
	}
}

?>