<?php
class Instance {
    var $id;
    var $network_username;
    var $network_user_id;
    var $network_viewer_id;
    var $last_status_id;
    var $last_page_fetched_replies;
    var $last_page_fetched_tweets;
    var $total_posts_in_system;
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
    var $is_active = true;
    var $network;

    function Instance($r) {
        $this->id = $r["id"];
        $this->network_username = $r['network_username'];
        $this->network_user_id = $r['network_user_id'];
        $this->network_viewer_id = $r['network_viewer_id'];
        $this->last_status_id = $r['last_status_id'];
        $this->last_page_fetched_replies = $r['last_page_fetched_replies'];
        $this->last_page_fetched_tweets = $r['last_page_fetched_tweets'];
        $this->total_posts_in_system = $r['total_posts_in_system'];
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
        $this->network = $r['network'];

        if ($r['is_public'] == 1)
        $this->is_public = true;
        if ($r['is_active'] == 0)
        $this->is_active = false;

    }

}

class InstanceDAO extends MySQLDAO {
    //Construct is located in parent

    function getInstanceStalestOne() {
        return $this->getInstanceOneByLastRun("ASC");
    }

    function getInstanceFreshestOne() {
        return $this->getInstanceOneByLastRun("DESC");
    }

    function insert($id, $user, $network = "twitter", $viewer_id = null) {
        if ($viewer_id == null) {
            $viewer_id = $id;
        }
        $q = "
            INSERT INTO 
                #prefix#instances (`network_user_id`, `network_username`, `network`, `network_viewer_id`)
             VALUES
                (".$id." , '".$user."', '".$network."', ".$viewer_id.")";
        $sql_result = $this->executeSQL($q);
        if (mysql_affected_rows() > 0 and mysql_insert_id() > 0) {
            return mysql_insert_id();
        } else {
            return false;
        }
    }


    private function getAverageReplyCount() {
        return "round(total_replies_in_system/(datediff(curdate(), earliest_reply_in_system)), 2) as avg_replies_per_day";
    }


    function getFreshestByOwnerId($owner_id) {
        $q = "
            SELECT 
                * , ".$this->getAverageReplyCount()."
            FROM 
                #prefix#instances i
            INNER JOIN
                #prefix#owner_instances oi
            ON 
                i.id = oi.instance_id
            WHERE 
                oi.owner_id = ".$owner_id."
            ORDER BY 
                crawler_last_run DESC";
        $sql_result = $this->executeSQL($q);
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
            SELECT *, ".$this->getAverageReplyCount()."
            FROM 
                #prefix#instances 
            ORDER BY 
                crawler_last_run
            ".$order." LIMIT 1";
        $sql_result = $this->executeSQL($q);
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
                #prefix#instances 
            WHERE 
                network_username = '".$username."'";
        $sql_result = $this->executeSQL($q);

        if (mysql_num_rows($sql_result) == 0) {
            $i = null;
        } else {
            $row = mysql_fetch_assoc($sql_result);
            $i = new Instance($row);
        }
        mysql_free_result($sql_result);
        return $i;
    }

    function getByUserId($id) {
        $q = "
            SELECT 
                * , ".$this->getAverageReplyCount()."
            FROM 
                #prefix#instances 
            WHERE 
                network_user_id = '".$id."'";
        $sql_result = $this->executeSQL($q);

        if (mysql_num_rows($sql_result) == 0) {
            $i = null;
        } else {
            $row = mysql_fetch_assoc($sql_result);
            $i = new Instance($row);
        }
        mysql_free_result($sql_result);
        return $i;
    }

    function getByUserAndViewerId($network_user_id, $viewer_id) {
        $q = "
            SELECT 
                * , ".$this->getAverageReplyCount()."
            FROM 
                #prefix#instances 
            WHERE 
                network_user_id = '".$network_user_id."' AND network_viewer_id = '".$viewer_id."'";
        $sql_result = $this->executeSQL($q);

        if (mysql_num_rows($sql_result) == 0) {
            $i = null;
        } else {
            $row = mysql_fetch_assoc($sql_result);
            $i = new Instance($row);
        }
        mysql_free_result($sql_result);
        return $i;
    }

    function getByViewerId($viewer_id) {
        $q = "
            SELECT 
                * , ".$this->getAverageReplyCount()."
            FROM 
                #prefix#instances 
            WHERE 
                network_viewer_id = '".$viewer_id."'";
        $sql_result = $this->executeSQL($q);

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
                #prefix#instances
             SET 
                crawler_last_run = NOW()
            WHERE
                id = ".$id.";";
        $sql_result = $this->executeSQL($q);

    }

    function setPublic($id, $p) {
        $q = "
            UPDATE 
                #prefix#instances
             SET 
                is_public = ".$p."
            WHERE
                network_user_id = '".$id."';";
        $sql_result = $this->executeSQL($q);

    }

    function setActive($id, $p) {
        $q = "
            UPDATE 
                #prefix#instances
             SET 
                is_active = ".$p."
            WHERE
                network_user_id = '".$id."';";
        echo $q;
        $sql_result = $this->executeSQL($q);

    }

    function save($i, $user_xml_total_posts_by_owner, $logger, $api) {
        if ($user_xml_total_posts_by_owner != '')
        $owner_tweets = "total_posts_by_owner = ".$user_xml_total_posts_by_owner.",";
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
                #prefix#instances
            SET
                ".$lsi."
                last_page_fetched_replies = ".$i->last_page_fetched_replies.",
                last_page_fetched_tweets = ".$i->last_page_fetched_tweets.",
                crawler_last_run = NOW(),
                total_posts_in_system = (select count(*) from #prefix#posts where author_user_id=".$i->network_user_id."),
                ".$owner_tweets."
                total_replies_in_system = (select count(*) from #prefix#posts WHERE MATCH (`post_text`) AGAINST('%".$i->network_username."%')),
                total_follows_in_system = (select count(*) from #prefix#follows where user_id=".$i->network_user_id." and active=1),
                total_users_in_system = (select count(*) from #prefix#users),
                is_archive_loaded_follows = ".$is_archive_loaded_follows.",
                is_archive_loaded_replies = ".$is_archive_loaded_replies.",
                earliest_reply_in_system = (select
                    pub_date
                from 
                    #prefix#posts
                where match (`post_text`) AGAINST('%".$i->network_username."%') 
                order by
                    pub_date asc
                limit 1),
                earliest_post_in_system = (select
                    pub_date
                from 
                    #prefix#posts
                where author_user_id = ".$i->network_user_id."
                order by
                    pub_date asc
                limit 1)
            WHERE
                network_user_id = ".$i->network_user_id.";";
        $foo = $this->executeSQL($q);

        $status_message = "Updated ".$i->network_username."'s system status.";
        $logger->logStatus($status_message, get_class($this));
        $status_message = "";

    }

    function isUserConfigured($un) {
        $q = "
            SELECT 
                network_username 
            FROM 
                #prefix#instances
            WHERE 
                network_username = '".$un."'";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) > 0)
        return true;
        else
        return false;
    }

    function getAllInstancesStalestFirst() {
        return $this->getAllInstances("ASC");
    }

    function getAllActiveInstancesStalestFirstByNetwork($network = "twitter") {
        return $this->getAllInstances("ASC", true, $network);
    }


    function getAllInstances($last_run = "DESC", $only_active = false, $network = "twitter") {
        $condition = "WHERE network='$network'";
        if ($only_active)
        $condition .= " AND is_active = 1 ";
        $q = "
            SELECT 
                *, ".$this->getAverageReplyCount()."
            FROM
                #prefix#instances ".$condition."
            ORDER BY
                crawler_last_run
            ".$last_run."";
        $sql_result = $this->executeSQL($q);
        $instances = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $instances[] = new Instance($row);
        }
        mysql_free_result($sql_result); # Free up memory
        return $instances;
    }

    function getByOwner($o, $disregard_admin_status = false) {
        if (!$disregard_admin_status && $o->is_admin) {
            $q = "
                SELECT 
                    *, ".$this->getAverageReplyCount()."
                FROM
                    #prefix#instances i
                ORDER BY
                    crawler_last_run 
                DESC;";
        } else {
            $q = "
                SELECT 
                    *, ".$this->getAverageReplyCount()."
                FROM
                    #prefix#owner_instances oi
                INNER JOIN
                    #prefix#instances i
                ON
                    i.id = oi.instance_id
                WHERE
                    oi.owner_id = ".$o->id."
                ORDER BY
                    crawler_last_run 
                DESC;";
        }
        $sql_result = $this->executeSQL($q);
        $instances = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $instances[] = new Instance($row);
        }
        mysql_free_result($sql_result); # Free up memory
        return $instances;
    }

    function getByOwnerAndNetwork($o, $network, $disregard_admin_status = false) {
        if (!$disregard_admin_status && $o->is_admin) {
            $q = "
                SELECT 
                    *, ".$this->getAverageReplyCount()."
                FROM
                    #prefix#instances i
                WHERE network='$network'
                ORDER BY
                    crawler_last_run 
                DESC;";
        } else {
            $q = "
                SELECT 
                    *, ".$this->getAverageReplyCount()."
                FROM
                    #prefix#owner_instances oi
                INNER JOIN
                    #prefix#instances i
                ON
                    i.id = oi.instance_id
                WHERE
                    oi.owner_id = ".$o->id." AND network='$network'
                ORDER BY
                    crawler_last_run 
                DESC;";
        }
        $sql_result = $this->executeSQL($q);
        $instances = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $instances[] = new Instance($row);
        }
        mysql_free_result($sql_result); # Free up memory
        return $instances;
    }


}

?>
