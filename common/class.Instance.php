<?php 
class Instance {
    var $id;
    var $twitter_username;
    var $twitter_user_id;
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
    var $api_calls_to_leave_unmade_per_minute;
    var $avg_replies_per_day;
    var $is_public = false;
    
    function Instance($r) {
        $this->id = $r["id"];
        $this->twitter_username = $r['twitter_username'];
        $this->twitter_user_id = $r['twitter_user_id'];
        $this->last_status_id = $r['last_status_id'];
        $this->last_page_fetched_followers = $r['last_page_fetched_followers'];
        $this->last_page_fetched_replies = $r['last_page_fetched_replies'];
        $this->last_page_fetched_tweets = $r['last_page_fetched_tweets'];
        $this->total_tweets_in_system = $r['total_tweets_in_system'];
        $this->total_replies_in_system = $r['total_replies_in_system'];
        $this->total_follows_in_system = $r['total_follows_in_system'];
        $this->total_users_in_system = $r['total_users_in_system'];
        if ($r['is_archive_loaded_replies'] == 1)
            $this->is_archive_loaded_replies = true;
        else
            $this->is_archive_loaded_replies = false;
            
        if ($r['is_archive_loaded_follows'] == 1)
            $this->is_archive_loaded_follows = true;
        else
            $this->is_archive_loaded_follows = false;

            
        $this->crawler_last_run = $r['crawler_last_run'];
        $this->earliest_reply_in_system = $r['earliest_reply_in_system'];
        $this->api_calls_to_leave_unmade_per_minute = $r['api_calls_to_leave_unmade_per_minute'];
        $this->avg_replies_per_day = $r['avg_replies_per_day'];
        if ($r['is_public'] == 1)
            $this->is_public = true;
            
    }
    
}

class InstanceDAO {

    function getInstanceStalestOne() {
        return $this->getInstanceOneByLastRun("ASC");
    }
    
    function getInstanceFreshestOne() {
        return $this->getInstanceOneByLastRun("DESC");
    }
    
    function insert($id, $user) {
        $q = "
			INSERT INTO 
				instances (`twitter_user_id`, `twitter_username`)
			 VALUES
				(".$id." , '".$user."')";
        $sql_result = Database::exec($q);
    }

    
    private function getAverageReplyCount() {
        return "round(total_replies_in_system/(datediff(curdate(), earliest_reply_in_system)), 2) as avg_replies_per_day";
    }

    
    function getFreshestByOwnerId($owner_id) {
        $q = "
			SELECT 
				* , ".$this->getAverageReplyCount()."
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
        $sql_result = Database::exec($q);
        if (mysql_num_rows($sql_result) == 0) {
            $i = null;
        } else {
            $row = mysql_fetch_assoc($sql_result);
            $i = new Instance($row);
        }
        mysql_free_result($sql_result);
        return $i;
    }

    
    function getInstanceOneByLastRun($order) {
        $q = "
			SELECT , ".$this->getAverageReplyCount()."
				* 
			FROM 
				instances 
			ORDER BY 
				crawler_last_run
			".$order." LIMIT 1";
        $sql_result = Database::exec($q);
        $row = mysql_fetch_assoc($sql_result);
        $i = new Instance($row);
        mysql_free_result($sql_result);
        return $i;
    }
    
    function getByUsername($username) {
        $q = "
			SELECT 
				* , ".$this->getAverageReplyCount()."
			FROM 
				instances 
			WHERE 
				twitter_username = '".$username."'";
        $sql_result = Database::exec($q);
        
        if (mysql_num_rows($sql_result) == 0) {
            $i = null;
        } else {
            $row = mysql_fetch_assoc($sql_result);
            $i = new Instance($row);
        }
        mysql_free_result($sql_result);
        return $i;
    }

    
    function updateLastRun($id) {
        $q = "
			UPDATE 
				instances
			 SET 
				crawler_last_run = NOW()
			WHERE
				id = ".$id.";";
        $sql_result = Database::exec($q);
        
    }
    
    function setPublic($u, $p) {
        $q = "
			UPDATE 
				instances
			 SET 
				is_public = ".$p."
			WHERE
				twitter_username = '".$u."';";
        $sql_result = Database::exec($q);
        
    }

    
    function save($i, $user_xml_total_tweets_by_owner, $logger, $api) {
        if ($user_xml_total_tweets_by_owner != '')
            $owner_tweets = "total_tweets_by_owner = ".$user_xml_total_tweets_by_owner.",";
        else
            $owner_tweets = '';
            
        if ($i->is_archive_loaded_follows)
            $is_archive_loaded_follows = 1;
        else
            $is_archive_loaded_follows = 0;
            
        if ($i->is_archive_loaded_replies)
            $is_archive_loaded_replies = 1;
        else
            $is_archive_loaded_replies = 0;
            
        $lsi = "";
        if ($i->last_status_id != "")
            $lsi = "last_status_id = ".$i->last_status_id.",";
            
        $q = "
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
				total_follows_in_system = (select count(*) from follows where user_id=".$i->twitter_user_id." and active=1),
				total_users_in_system = (select count(*) from users),
				is_archive_loaded_follows = ".$is_archive_loaded_follows.",
				is_archive_loaded_replies = ".$is_archive_loaded_replies.",
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
        $foo = Database::exec($q);
        
        $status_message = "Updated ".$i->twitter_username."'s system status.";
        $logger->logStatus($status_message, get_class($this));
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
        $sql_result = Database::exec($q);
        if (mysql_num_rows($sql_result) > 0)
            return true;
        else
            return false;
    }
    
    function getAllInstancesStalestFirst() {
        return $this->getAllInstances("ASC");
    }

    
    function getAllInstances($last_run = "DESC") {
        $q = "
			SELECT 
				*, ".$this->getAverageReplyCount()."
			FROM
				instances
			ORDER BY
				crawler_last_run
			".$last_run."";
        $sql_result = Database::exec($q);
        $instances = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $instances[] = new Instance($row);
        }
        mysql_free_result($sql_result); # Free up memory
        return $instances;
    }
    
    function getByOwner($o) {
        if ($o->is_admin) {
            $q = "
				SELECT 
					*, ".$this->getAverageReplyCount()."
				FROM
					instances i
				ORDER BY
					crawler_last_run 
				DESC;";
        } else {
            $q = "
				SELECT 
					*, ".$this->getAverageReplyCount()."
				FROM
					owner_instances oi
				INNER JOIN
					instances i
				ON
					i.id = oi.instance_id
				WHERE
					oi.owner_id = ".$o->id."
				ORDER BY
					crawler_last_run 
				DESC;";
        }
        $sql_result = Database::exec($q);
        $instances = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $instances[] = new Instance($row);
        }
        mysql_free_result($sql_result); # Free up memory
        return $instances;
    }

    
}

?>
