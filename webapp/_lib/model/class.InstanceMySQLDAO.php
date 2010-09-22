<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.InstanceMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Guillaume Boudreau, Mark Wilkie
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * Instance MySQL Data Access Object Implementation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Guillaume Boudreau, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class InstanceMySQLDAO extends PDODAO implements InstanceDAO {

    public function getInstanceStalestOne() {
        return $this->getInstanceOneByLastRun("ASC");
    }

    public function getInstanceFreshestOne() {
        return $this->getInstanceOneByLastRun("DESC");
    }

    public function getInstanceFreshestPublicOne() {
        return $this->getInstanceOneByLastRun("DESC", true);
    }
    /**
     * Alias for a average reply-count calculating portion of a query
     *
     * @return str query
     */
    protected function getAverageReplyCount() {
        return "round(total_replies_in_system/(datediff(curdate(), earliest_reply_in_system)), 2) AS
        avg_replies_per_day";
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
        $q  = " SELECT i.* , ".$this->getAverageReplyCount();
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

    /**
     * Get instance based on sort order
     * @param str $order "ASC" or "DESC"
     * @param bool $only_public Only public instances, defaults to false
     * @return array Instance objects
     */
    private function getInstanceOneByLastRun($order, $only_public=false) {
        $q  = "SELECT *, ".$this->getAverageReplyCount() . " ";
        $q .= "FROM #prefix#instances ";
        if ($only_public) {
            $q .= "WHERE is_public = 1 ";
        }
        $q .= "ORDER BY crawler_last_run ";
        $q .= "$order LIMIT 1";
        $ps = $this->execute($q);

        return $this->getDataRowAsObject($ps, "Instance");
    }

    public function getByUsername($username, $network = "twitter") {
        $q  = " SELECT * , ".$this->getAverageReplyCount();
        $q .= " FROM #prefix#instances ";
        $q .= " WHERE network_username = :username AND network = :network";
        $q .= " LIMIT 1 ";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowAsObject($ps, "Instance");
    }

    public function getByUsernameOnNetwork($username, $network) {
        $q  = " SELECT * , ".$this->getAverageReplyCount();
        $q .= " FROM #prefix#instances ";
        $q .= " WHERE network_username = :username AND network = :network";
        $q .= " LIMIT 1 ";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowAsObject($ps, "Instance");
    }

    public function getByUserIdOnNetwork($network_user_id, $network) {
        $q  = " SELECT * , ".$this->getAverageReplyCount();
        $q .= " FROM #prefix#instances ";
        $q .= " WHERE network_user_id = :uid AND network = :network";
        $vars = array(
            ':uid'=>$network_user_id,
            ':network'=>$network
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

    public function getPublicInstances() {
        $q  = "SELECT *, ".$this->getAverageReplyCount()." ";
        $q .= "FROM #prefix#instances AS i ";
        $q .= "WHERE is_public = 1 and is_active=1 ORDER BY crawler_last_run DESC;";
        $ps = $this->execute($q);
        return $this->getDataRowsAsObjects($ps, "Instance");
    }

    public function getByOwnerAndNetwork($owner, $network, $disregard_admin_status = false) {
        $adminstatus = (!$disregard_admin_status && $owner->is_admin ? true : false);
        $q  = "SELECT i.*, ".$this->getAverageReplyCount();
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

    public function setPublic($instance_id, $public) {
        $public = $this->convertBoolToDB($public);
        $q  = " UPDATE #prefix#instances ";
        $q .= " SET is_public = :public";
        $q .= " WHERE id = :instance_id ;";
        $vars = array(
            ':instance_id'=>$instance_id,
            ':public'=>$public
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function setActive($instance_id, $active) {
        $active = $this->convertBoolToDB($active);
        $q  = " UPDATE #prefix#instances ";
        $q .= " SET is_active = :active ";
        $q .= " WHERE id = :instance_id ;";
        $vars = array(
            ':instance_id'=>$instance_id,
            ':active'=>$active
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    private function getInstanceUserStats($network_user_id, $network) {
        $num_posts_max = 25;

        $q  = "SELECT pub_date, all_posts.total AS num_posts";
        $q .= "  FROM (";
        $q .= "        SELECT *";
        $q .= "          FROM #prefix#posts";
        $q .= "         WHERE author_user_id=:uid AND network=:network";
        $q .= "         ORDER BY pub_date DESC";
        $q .= "         LIMIT :num_posts) AS p,";
        $q .= "       (";
        $q .= "        SELECT COUNT(*) AS total";
        $q .= "          FROM #prefix#posts";
        $q .= "         WHERE author_user_id=:uid AND network=:network) AS all_posts";
        $q .= " ORDER BY pub_date ASC";
        $q .= " LIMIT 1;";
        $vars = array(
        	':uid' => $network_user_id,
            ':network' => $network,
            ':num_posts' => $num_posts_max
        );
        $result = $this->getDataRowAsArray($this->execute($q, $vars));

        if ($result['num_posts'] > $num_posts_max) {
            $result['num_posts'] = $num_posts_max;
        }

        $num_days = (time() - strtotime($result['pub_date'])) / (24*60*60);
        if ($num_days < 1) {
            $num_days = 1;
        }
        $posts_per_day = $result['num_posts'] / $num_days;

        $num_weeks = $num_days / 7;
        if ($num_weeks < 1) {
            $num_weeks = 1;
        }
        $posts_per_week = $result['num_posts'] / $num_weeks;

        $q  = "SELECT num_replies.total AS num_replies,";
        $q .= "       num_links.total   AS num_links,";
        $q .= "       all_posts.total   AS num_posts";
        $q .= "  FROM (";
        $q .= "        SELECT COUNT(*) AS total";
        $q .= "          FROM #prefix#posts";
        $q .= "         WHERE author_user_id=:uid AND network=:network";
        $q .= "           AND in_reply_to_user_id > 0) AS num_replies,";
        $q .= "       (";
        $q .= "        SELECT COUNT(*) AS total";
        $q .= "          FROM #prefix#posts AS p";
        $q .= "     LEFT JOIN #prefix#links AS l";
        $q .= "               ON (p.post_id = l.post_id AND p.network = l.network)";
        $q .= "         WHERE author_user_id=:uid AND p.network=:network ";
        $q .= "           AND l.post_id IS NOT NULL) AS num_links,";
        $q .= "       (";
        $q .= "        SELECT COUNT(*) AS total";
        $q .= "          FROM #prefix#posts";
        $q .= "         WHERE author_user_id=:uid AND network=:network) AS all_posts;";
        $vars = array(
        	':uid' => $network_user_id,
            ':network' => $network,
        );
        $result = $this->getDataRowAsArray($this->execute($q, $vars));

        $percent_replies = 0;
        $percent_links = 0;
        if ($result['num_posts'] > 0) {
            $percent_replies = $result['num_replies'] / $result['num_posts'] * 100.0;
            $percent_links = $result['num_links'] / $result['num_posts'] * 100.0;
        }
        
        return array($posts_per_day, $posts_per_week, $percent_replies, $percent_links);
    }

    public function save($instance_object, $user_xml_total_posts_by_owner, $logger = false) {
        $i = $instance_object;
        list($posts_per_day, $posts_per_week, $percent_replies, $percent_links) =
        $this->getInstanceUserStats($i->network_user_id, $i->network);
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
        $q .= "     ORDER BY pub_date ASC LIMIT 1), ";
        $q .= " posts_per_day = :ppd, ";
        $q .= " posts_per_week = :ppw, ";
        $q .= " percentage_replies = :perc_r, ";
        $q .= " percentage_links = :perc_l ";
        $q .= " WHERE network_user_id = :uid;";

        $vars = array(
            ':laststatusid' => $i->last_status_id,
            ':lpfr'         => $i->last_page_fetched_replies,
            ':lpft'         => $i->last_page_fetched_tweets,
            ':uid'          => $i->network_user_id,
            ':tpbo'         => $user_xml_total_posts_by_owner,
            ':username'     => "%".$i->network_username."%",
            ':ialf'         => $is_archive_loaded_follows,
            ':ialr'         => $is_archive_loaded_replies,
            ':ppd'          => $posts_per_day,
            ':ppw'          => $posts_per_week,
            ':perc_r'       => $percent_replies,
            ':perc_l'       => $percent_links
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

    public function isUserConfigured($username, $network) {
        $q  = " SELECT network_username ";
        $q .= " FROM #prefix#instances ";
        $q .= " WHERE network_username = :username AND network = :network ";
        $q .= " LIMIT 1 ";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataIsReturned($ps);
    }

    public function getByUserAndViewerId($network_user_id, $viewer_id, $network = 'facebook') {
        $q = "SELECT * , ".$this->getAverageReplyCount()." ";
        $q .= "FROM #prefix#instances ";
        $q .= "WHERE network_user_id = :network_user_id AND network_viewer_id = :viewer_id ";
        $q .= "AND network = :network";
        $vars = array(
            ':network_user_id'=>$network_user_id,
            ':viewer_id'=>$viewer_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, "Instance");
    }

    public function getByViewerId($viewer_id, $network = 'facebook') {
        $q = "SELECT * , ".$this->getAverageReplyCount()." ";
        $q .= "FROM #prefix#instances ";
        $q .= "WHERE network_viewer_id = :viewer_id AND network = :network ";
        $vars = array(
            ':viewer_id'=>$viewer_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($ps, "Instance");
    }

    public function getHoursSinceLastCrawlerRun() {
        $q = "SELECT (unix_timestamp( NOW() ) - unix_timestamp(crawler_last_run )) / 3600 as hours_since_last_run ";
        $q .= "FROM #prefix#instances ORDER BY crawler_last_run DESC LIMIT 1";
        $ps = $this->execute($q);
        $result = $this->getDataRowsAsArrays($ps);
        if ($result && isset($result[0]) ) {
            return $result[0]['hours_since_last_run'];
        } else  {
            return null;
        }
    }
}
