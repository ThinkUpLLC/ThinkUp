<?php
/**
 * Instance MySQL Data Access Object Implementation
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once 'model/class.PDODAO.php';
require_once 'model/interface.InstanceDAO.php';

class InstanceMySQLDAO extends PDODAO implements InstanceDAO {

    public function getInstanceStalestOne() {
        return $this->getInstanceOneByLastRun("ASC");
    }

    public function getInstanceFreshestOne() {
        return $this->getInstanceOneByLastRun("DESC");
    }

    protected function getAverageReplyCount() {
        return "round(total_replies_in_system/(datediff(curdate(), earliest_reply_in_system)), 2) as avg_replies_per_day";
    }

    public function getAllInstancesStalestFirst() {
        return $this->getAllInstances("ASC");
    }

    public function getAllActiveInstancesStalestFirstByNetwork($network = "twitter") {
        return $this->getAllInstances("ASC", true, $network);
    }

    public function insert($network_user_id, $network_username, $network = "twitter", $viewer_id = false) {
        $q  = " INSERT INTO #prefix#instances ";
        $q .= " (`network_user_id`, `network_username`, `network`, `network_viewer_id`) ";
        $q .= " VALUES (:uid , :username, :network, :viewerid) ";
        $vars = array(
            ':uid'=>$network_user_id,
            ':username'=>$network_username,
            ':network'=>$network,
            ':viewerid'=>($viewer_id ? $viewer_id : $network_user_id)
        );
        $ps = $this->execute($q, $vars);

        return $this->getInsertId($ps);
    }

    public function getFreshestByOwnerId($owner_id) {
        $q  = " SELECT * , ".$this->getAverageReplyCount();
        $q .= " FROM #prefix#instances AS i ";
        $q .= " INNER JOIN #prefix#owner_instances AS oi ";
        $q .= " ON i.id = oi.instance_id ";
        $q .= " WHERE oi.owner_id = :owner ";
        $q .= " ORDER BY crawler_last_run DESC";
        $q .= " LIMIT 1";
        $vars = array(
            ':owner'=>$owner_id
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowAsObject($ps, "Instance");
    }

    public function getInstanceOneByLastRun($order) {
        $q  = " SELECT *, ".$this->getAverageReplyCount();
        $q .= " FROM #prefix#instances ";
        $q .= " ORDER BY crawler_last_run ";
        $q .= " $order LIMIT 1";
        $ps = $this->execute($q);

        return $this->getDataRowAsObject($ps, "Instance");
    }

    public function getByUsername($username) {
        $q  = " SELECT * , ".$this->getAverageReplyCount();
        $q .= " FROM #prefix#instances ";
        $q .= " WHERE network_username = :username ";
        $q .= " LIMIT 1 ";
        $vars = array(
            ':username'=>$username
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowAsObject($ps, "Instance");
    }

    public function getByUserId($network_user_id) {
        $q  = " SELECT * , ".$this->getAverageReplyCount();
        $q .= " FROM #prefix#instances ";
        $q .= " WHERE network_user_id = :uid ";
        $vars = array(
            ':uid'=>$network_user_id
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowAsObject($ps, "Instance");
    }

    public function getAllInstances($order = "DESC", $only_active = false, $network = "twitter") {
        $q  = " SELECT *, ".$this->getAverageReplyCount();
        $q .= " FROM #prefix#instances ";
        $q .= " WHERE network=:network";
        if ($only_active){
            $q .= " AND is_active = 1 ";
        }
        $q .= " ORDER BY crawler_last_run ".$order;
        $vars = array(
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsObjects($ps, "Instance");
    }

    public function getByOwner($owner, $force_not_admin = false) {
        $adminstatus = (!$force_not_admin && $owner->is_admin ? true : false);
        $q  = "SELECT *, ".$this->getAverageReplyCount();
        $q .= " FROM #prefix#instances AS i ";
        if(!$adminstatus){
            $q .= " INNER JOIN #prefix#owner_instances AS oi ";
            $q .= " ON i.id = oi.instance_id ";
            $q .= " WHERE oi.owner_id = :ownerid ";
        }
        $q .= " ORDER BY crawler_last_run DESC;";
        $vars = array(
            ':ownerid'=>$owner->id
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsObjects($ps, "Instance");
    }

    public function getByOwnerAndNetwork($owner, $network, $force_not_admin = false) {
        $adminstatus = (!$force_not_admin && $owner->is_admin ? true : false);
        $q  = "SELECT *, ".$this->getAverageReplyCount();
        $q .= " FROM #prefix#instances AS i ";
        if(!$adminstatus){
            $q .= " INNER JOIN #prefix#owner_instances AS oi ";
            $q .= " ON i.id = oi.instance_id ";
        }
        $q .= " WHERE network=:network ";
        if(!$adminstatus){
            $q .= " AND oi.owner_id = :ownerid ";
        }
        $q .= " ORDER BY crawler_last_run DESC;";
        $vars = array(
            ':ownerid'=>$owner->id,
            ':network'=>$network
        );

        //Workaround for a PHP bug
        if($adminstatus){
            unset ($vars[':ownerid']);
        }

        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsObjects($ps, "Instance");
    }

    public function setPublic($username, $public) {
        $public = $this->convertBoolToDB($public);
        $q  = " UPDATE #prefix#instances ";
        $q .= " SET is_public = :public";
        $q .= " WHERE network_username = :username ;";
        $vars = array(
            ':username'=>$username,
            ':public'=>$public
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function setActive($username, $active) {
        $active = $this->convertBoolToDB($active);
        $q  = " UPDATE #prefix#instances ";
        $q .= " SET is_active = :active ";
        $q .= " WHERE network_username = :username ;";
        $vars = array(
            ':username'=>$username,
            ':active'=>$active
        );
        $ps = $this->execute($q, $vars);

        return $this->getUpdateCount($ps);
    }

    public function save($instance_object, $user_xml_total_posts_by_owner, $logger = false, $api = false) {
        $i = $instance_object;
        $ot = ($user_xml_total_posts_by_owner != '' ? true : false);
        $lsi = ($i->last_status_id != "" ? true : false);
        $is_archive_loaded_follows = $this->convertBoolToDB($i->is_archive_loaded_follows);
        $is_archive_loaded_replies = $this->convertBoolToDB($i->is_archive_loaded_replies);
        $q  = " UPDATE #prefix#instances ";
        $q .= " SET ";
        if ($lsi){
            $q .= " last_status_id = :laststatusid, ";
        }
        $q .= " last_page_fetched_replies = :lpfr, ";
        $q .= " last_page_fetched_tweets = :lpft , ";
        $q .= " crawler_last_run = NOW(), ";
        $q .= " total_posts_in_system = (select count(*) from #prefix#posts where author_user_id=:uid), ";
        if ($ot){
            $q .= " total_posts_by_owner = :tpbo,";
        }
        $q .= " total_replies_in_system = (SELECT count(id) FROM #prefix#posts WHERE MATCH(`post_text`) AGAINST(:username)), ";
        $q .= " total_follows_in_system = (SELECT count(*) FROM #prefix#follows WHERE user_id=:uid AND active=1), ";
        $q .= " total_users_in_system = (SELECT count(*) FROM #prefix#users), ";
        $q .= " is_archive_loaded_follows = :ialf, ";
        $q .= " is_archive_loaded_replies = :ialr, ";
        $q .= " earliest_reply_in_system = (SELECT pub_date ";
        $q .= "     FROM #prefix#posts ";
        $q .= "     WHERE match (`post_text`) AGAINST(:username) ";
        $q .= "     ORDER BY pub_date ASC LIMIT 1), ";
        $q .= " earliest_post_in_system = (SELECT pub_date ";
        $q .= "     FROM #prefix#posts ";
        $q .= "     WHERE author_user_id = :uid ";
        $q .= "     ORDER BY pub_date ASC LIMIT 1) ";
        $q .= " WHERE network_user_id = :uid;";

        $vars = array(
            ':laststatusid' => $i->last_status_id,
            ':lpfr'         => $i->last_page_fetched_replies,
            ':lpft'         => $i->last_page_fetched_tweets,
            ':uid'          => $i->network_user_id,
            ':tpbo'         => $user_xml_total_posts_by_owner,
            ':username'     => "%".$i->network_username."%",
            ':ialf'         => $is_archive_loaded_follows,
            ':ialr'         => $is_archive_loaded_replies
        );
        $ps = $this->execute($q, $vars);

        $status_message = "Updated ".$i->network_username."'s system status.";
        if($logger){
            $logger->logStatus($status_message, get_class($this));
        }
        return $this->getUpdateCount($ps);
    }

    public function updateLastRun($id) {
        $q  = " UPDATE #prefix#instances ";
        $q .= " SET crawler_last_run = NOW() ";
        $q .= " WHERE id = :id ";
        $q .= " LIMIT 1 ";
        $vars = array(
            ':id'=>$id
        );
        $ps = $this->execute($q, $vars);

        return $this->getUpdateCount($ps);
    }

    public function isUserConfigured($username) {
        $q  = " SELECT network_username ";
        $q .= " FROM #prefix#instances ";
        $q .= " WHERE network_username = :username ";
        $q .= " LIMIT 1 ";
        $vars = array(
            ':username'=>$username,
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataIsReturned($ps);
    }

    public function getByUserAndViewerId($network_user_id, $viewer_id) {
        $q = "SELECT * , ".$this->getAverageReplyCount()." ";
        $q .= "FROM #prefix#instances ";
        $q .= "WHERE network_user_id = :network_user_id AND network_viewer_id = :viewer_id";
        $vars = array(
            ':network_user_id'=>$network_user_id,
            ':viewer_id'=>$viewer_id,
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, "Instance");
    }

    public function getByViewerId($viewer_id) {
        $q = "SELECT * , ".$this->getAverageReplyCount()." ";
        $q .= "FROM #prefix#instances ";
        $q .= "WHERE network_viewer_id = :viewer_id";
        $vars = array(
            ':viewer_id'=>$viewer_id,
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($ps, "Instance");
    }
}