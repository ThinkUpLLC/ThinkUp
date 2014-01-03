<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.InstanceMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie, Guillaume Boudreau
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * Instance MySQL Data Access Object Implementation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie, Guillaume Boudreau
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class InstanceMySQLDAO extends PDOCorePluginDAO implements InstanceDAO {
    public function __construct() {
        parent::__construct("Instance", "instances");
    }
    /**
     * Get string listing all the fields to select from both core and plugin table.
     * Overriding parent implementation b/c we're returning a custom field, average reply count.
     * @return str
     */
    protected function getFieldList() {
        return parent::getFieldList().", ".$this->getAverageReplyCount()." ";
    }

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
     * Alias for a average reply-count calculating portion of a query.
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

    public function getActiveInstancesStalestFirstForOwnerByNetworkNoAuthError( Owner $owner, $network ) {
        $q  = "SELECT ".$this->getFieldList();
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        $q .= "INNER JOIN #prefix#owner_instances oi ON oi.instance_id = ".$this->getTableName().".id ";
        $q .= "WHERE network=:network AND (oi.auth_error = '' OR oi.auth_error IS NULL) ";
        if (!$owner->is_admin) {
            $q .= "AND oi.owner_id = :owner_id ";
        }
        $q .= "AND is_active = 1 ";
        $q .= "ORDER BY crawler_last_run";
        $vars = array(
            ':network'=>$network
        );
        if (!$owner->is_admin) {
            $vars[':owner_id'] = $owner->id;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($ps, $this->object_name);
    }

    public function insert($network_user_id, $network_username, $network = "twitter", $viewer_id = false) {
        $q  = "INSERT INTO ".$this->getTableName()." ";
        $q .= "(network_user_id, network_username, network, network_viewer_id, last_post_id) ";
        $q .= "VALUES (:user_id , :username, :network, :viewer_id, '') ";
        $vars = array(
            ':user_id'=>(string)$network_user_id,
            ':username'=>$network_username,
            ':network'=>$network,
            ':viewer_id'=>(string)($viewer_id ? $viewer_id : $network_user_id)
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getInsertId($ps);
    }

    public function delete($network_username, $network) {
        $q  = "DELETE FROM ".$this->getTableName()." ";
        $q .= "WHERE network_username = :username AND network = :network;";
        $vars = array(
            ':username'=>$network_username,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function getFreshestByOwnerId($owner_id) {
        $q  = "SELECT ".$this->getFieldList();
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        $q .= "INNER JOIN #prefix#owner_instances oi ";
        $q .= "ON ".$this->getTableName().".id = oi.instance_id ";
        $q .= "WHERE oi.owner_id = :owner AND ".$this->getTableName().".is_active = 1 ";
        $q .= "ORDER BY crawler_last_run DESC LIMIT 1";
        $vars = array(
            ':owner'=>$owner_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, $this->object_name);
    }

    /**
     * Get instance based on sort order
     * @param str $order "ASC" or "DESC"
     * @param bool $only_public Only public instances, defaults to false
     * @return array Instance objects
     */
    private function getInstanceOneByLastRun($order, $only_public=false) {
        $order = ($order=="ASC")?"ASC":"DESC";
        $q  = "SELECT ".$this->getFieldList();
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        if ($only_public) {
            $q .= "WHERE is_public = 1 ";
        }
        $q .= "ORDER BY crawler_last_run ";
        $q .= $order." LIMIT 1";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q);
        return $this->getDataRowAsObject($ps, $this->object_name);
    }

    public function getByUsername($username, $network = "twitter") {
        $q  = "SELECT ".$this->getFieldList();
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        $q .= "WHERE network_username = :username AND network = :network ";
        $q .= "LIMIT 1 ";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, $this->object_name);
    }

    public function get($instance_id) {
        $q  = "SELECT ".$this->getFieldList();
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        $q .= "WHERE ".$this->getTableName().".id=:id ";
        $q .= "LIMIT 1 ";
        $vars = array(
            ':id'=>$instance_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, $this->object_name);
    }

    public function getByUsernameOnNetwork($username, $network) {
        $q  = "SELECT ".$this->getFieldList();
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        $q .= "WHERE network_username = :username AND network = :network ";
        $q .= "LIMIT 1 ";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, $this->object_name);
    }

    public function getByUserIdOnNetwork($network_user_id, $network) {
        $q  = "SELECT ".$this->getFieldList();
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        $q .= "WHERE network_user_id = :user_id AND network = :network ";
        $vars = array(
            ':user_id'=>(string)$network_user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, $this->object_name);
    }

    public function getAllInstances($order = "DESC", $only_active = false, $network = "twitter") {
        $q  = "SELECT ".$this->getFieldList();
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        $q .= "WHERE network=:network ";
        if ($only_active) {
            $q .= "AND is_active = 1 ";
        }
        $q .= "ORDER BY crawler_last_run ".$order;
        $vars = array(
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($ps, $this->object_name);
    }

    public function getByOwner(Owner $owner, $force_not_admin = false, $only_active=false) {
        if ($owner == null) {
            return null;
        }
        $admin_status = (!$force_not_admin && $owner->is_admin ? true : false);
        $vars = array(
            ':owner_id'=>$owner->id
        );
        $q  = "SELECT ".$this->getFieldList();
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        if (!$admin_status) {
            $q .= "INNER JOIN #prefix#owner_instances AS oi ";
            $q .= "ON ".$this->getTableName().".id = oi.instance_id ";
            $q .= "WHERE oi.owner_id = :owner_id ";
        }
        if ($only_active) {
            if (!$admin_status) {
                $q .= "AND ";
            } else {
                $q .= "WHERE ";
            }
            $q .= "is_active = 1 ";
        }
        $q .= "ORDER BY crawler_last_run DESC;";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($ps, $this->object_name);
    }

    public function getByOwnerWithStatus(Owner $owner) {
        if ($owner == null) {
            return null;
        }
        $vars = array(
            ':owner_id'=>$owner->id
        );
        $q  = "SELECT ".$this->getFieldList();
        $q .= ", oi.auth_error FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        $q .= "INNER JOIN #prefix#owner_instances AS oi ";
        $q .= "ON ".$this->getTableName().".id = oi.instance_id ";
        $q .= "WHERE oi.owner_id = :owner_id  AND is_active = 1 ORDER BY id ASC;";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($ps, $this->object_name);
    }

    public function getPublicInstances() {
        $q  = "SELECT ".$this->getFieldList();
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        $q .= "WHERE is_public = 1 and is_active=1 ORDER BY crawler_last_run DESC;";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q);
        return $this->getDataRowsAsObjects($ps, $this->object_name);
    }


    public function getByOwnerAndNetwork($owner, $network, $disregard_admin_status = false, $active_only = false) {
        $admin_status = (!$disregard_admin_status && $owner->is_admin ? true : false);
        $q  = "SELECT ".$this->getFieldList();
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        if (!$admin_status) {
            $q .= "INNER JOIN #prefix#owner_instances AS oi ";
            $q .= "ON ".$this->getTableName().".id = oi.instance_id ";
        }
        $q .= "WHERE network=:network ";
        if (!$admin_status) {
            $q .= "AND oi.owner_id = :ownerid ";
        }
        if ($active_only) {
            $q .= "AND is_active = 1 ";
        }
        $q .= "ORDER BY crawler_last_run DESC; ";
        $vars = array(
            ':ownerid'=>$owner->id,
            ':network'=>$network
        );
        //Workaround for a PHP bug
        if ($admin_status) {
            unset ($vars[':ownerid']);
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($ps, $this->object_name);
    }

    public function setPublic($instance_id, $public) {
        $public = $this->convertBoolToDB($public);
        $q  = "UPDATE ".$this->getTableName()." ";
        $q .= "SET is_public = :public ";
        $q .= "WHERE id = :instance_id ;";
        $vars = array(
            ':instance_id'=>$instance_id,
            ':public'=>$public
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function setActive($instance_id, $active) {
        $active = $this->convertBoolToDB($active);
        $q  = "UPDATE ".$this->getTableName()." ";
        $q .= "SET is_active = :active ";
        $q .= "WHERE id = :instance_id ;";
        $vars = array(
            ':instance_id'=>$instance_id,
            ':active'=>$active
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    private function getInstanceUserStats($network_user_id, $network) {
        $num_posts_max = 25;

        $q  = "SELECT pub_date, all_posts.total AS num_posts";
        $q .= "  FROM (";
        $q .= "        SELECT *";
        $q .= "          FROM #prefix#posts";
        $q .= "         WHERE author_user_id=:user_id AND network=:network";
        $q .= "         ORDER BY pub_date DESC";
        $q .= "         LIMIT :num_posts) AS p,";
        $q .= "       (";
        $q .= "        SELECT COUNT(*) AS total";
        $q .= "          FROM #prefix#posts";
        $q .= "         WHERE author_user_id=:user_id AND network=:network) AS all_posts ";
        $q .= "ORDER BY pub_date ASC ";
        $q .= "LIMIT 1;";
        $vars = array(
            ':user_id' => (string)$network_user_id,
            ':network' => $network,
            ':num_posts' => $num_posts_max
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
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
        $q .= "         WHERE author_user_id=:user_id AND network=:network";
        $q .= "           AND in_reply_to_user_id > 0) AS num_replies,";
        $q .= "       (";
        $q .= "        SELECT COUNT(*) AS total";
        $q .= "          FROM #prefix#posts AS p";
        $q .= "     LEFT JOIN #prefix#links AS l";
        $q .= "               ON (p.id = l.post_key)";
        $q .= "         WHERE author_user_id=:user_id AND p.network=:network ";
        $q .= "           AND l.id IS NOT NULL) AS num_links,";
        $q .= "       (";
        $q .= "        SELECT COUNT(*) AS total";
        $q .= "          FROM #prefix#posts";
        $q .= "         WHERE author_user_id=:user_id AND network=:network) AS all_posts;";
        $vars = array(
            ':user_id' => (string)$network_user_id,
            ':network' => $network,
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
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
        $lsi = ($i->last_post_id != "" ? true : false);

        $is_archive_loaded_follows = $this->convertBoolToDB($i->is_archive_loaded_follows);
        $is_archive_loaded_replies = $this->convertBoolToDB($i->is_archive_loaded_replies);
        $is_archive_loaded_posts = $this->convertBoolToDB($i->is_archive_loaded_posts);

        //former subquery 1 for owner_favs_in_system
        $q = "SELECT COUNT(*) AS owner_favs_in_system FROM #prefix#favorites ";
        $q .= "WHERE fav_of_user_id= :user_id AND network=:network";
        $vars = array(
            ':user_id'      => (string)$i->network_user_id,
            ':network'      => $i->network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowAsArray($ps);
        $owner_favs_in_system = $result['owner_favs_in_system'];

        //former subquery 2 for total_posts_in_system
        $q = "SELECT COUNT(*) AS total_posts_in_system FROM #prefix#posts ";
        $q .= "WHERE author_user_id=:user_id AND network = :network";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowAsArray($ps);
        $total_posts_in_system = $result['total_posts_in_system'];

        //former subquery 3 for total_follows_in_system
        $q = "SELECT COUNT(*) AS total_follows_in_system FROM #prefix#follows ";
        $q .= "WHERE user_id=:user_id AND active=1 AND network = :network";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowAsArray($ps);
        $total_follows_in_system = $result['total_follows_in_system'];

        //former subquery 4 for earliest_post_in_system
        // NOTE: Commented out because this query is a performance hog, and the field is not in use.
        //        $q = "SELECT pub_date FROM #prefix#posts ";
        //        $q .= "WHERE author_user_id = :user_id AND network = :network ";
        //        $q .= "ORDER BY pub_date ASC LIMIT 1";
        //        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        //        $ps = $this->execute($q, $vars);
        //        $result = $this->getDataRowAsArray($ps);
        //        $earliest_post_in_system = $result['earliest_post_in_system'];

        $q  = "UPDATE ".$this->getTableName()." SET ";
        if ($lsi) {
            $q .= "last_post_id = :last_post_id, ";
        }
        $q .= "favorites_profile = :fp, ";
        $q .= "owner_favs_in_system = :owner_favs_in_system, ";
        $q .= "crawler_last_run = NOW(), ";
        $q .= "total_posts_in_system = :total_posts_in_system, ";
        if ($ot) {
            $q .= "total_posts_by_owner = :tpbo, ";
        }
        // For performance reasons, set this to null for now.
        $q .= "total_replies_in_system=null, ";
        // The former subquery is a performance hog, and the field is not in use.
        //@TODO Remove the field from the table entirely.
        //        $q .= "total_replies_in_system = (SELECT count(id) FROM #prefix#posts ";
        //        $q .= "WHERE network = :network AND MATCH(post_text) AGAINST(:username)), ";
        $q .= "total_follows_in_system = :total_follows_in_system, ";
        $q .= "is_archive_loaded_follows = :ialf, ";
        $q .= "is_archive_loaded_replies = :ialr, ";
        $q .= "is_archive_loaded_posts = :ialp, ";
        // For performance reasons, set this to null for now.
        $q .= "earliest_reply_in_system = null, ";
        // The former subquery is a performance hog, and the field is not in use.
        //@TODO Remove the field from the table entirely.
        //        $q .= "earliest_reply_in_system = (SELECT pub_date ";
        //        $q .= "     FROM #prefix#posts ";
        //        $q .= "     WHERE network = :network AND match (post_text) AGAINST(:username) ";
        //        $q .= "     ORDER BY pub_date ASC LIMIT 1), ";

        // For performance reasons, set this to null for now.
        $q .= "earliest_post_in_system = null, ";
        //@TODO Remove the field from the table entirely.
        $q .= "posts_per_day = :ppd, ";
        $q .= "posts_per_week = :ppw, ";
        $q .= "percentage_replies = :perc_r, ";
        $q .= "percentage_links = :perc_l ";
        $q .= "WHERE id = :id;";

        $vars = array(
            ':fp'           => $i->favorites_profile,
            ':owner_favs_in_system' => (int) $owner_favs_in_system,
            ':total_posts_in_system' => (int) $total_posts_in_system,
            ':total_follows_in_system' => (int) $total_follows_in_system,
            ':ialf'         => $is_archive_loaded_follows,
            ':ialr'         => $is_archive_loaded_replies,
            ':ialp'         => $is_archive_loaded_posts,
        //':earliest_post_in_system' => $earliest_post_in_system,
        //':username'     => "%".$i->network_username."%",
            ':ppd'          => $posts_per_day,
            ':ppw'          => $posts_per_week,
            ':perc_r'       => $percent_replies,
            ':perc_l'       => $percent_links,
            ':id'           => $i->id
        );
        if ($lsi) {
            $vars[':last_post_id'] = (string)$i->last_post_id;
        }
        if ($ot) {
            $vars[':tpbo'] = $user_xml_total_posts_by_owner;
        }

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        $status_message = "Updated ".$i->network_username."'s system status.";
        if ($logger) {
            $logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
        }
        return $this->getUpdateCount($ps);
    }

    public function updateLastRun($id) {
        $q  = "UPDATE ".$this->getTableName()." ";
        $q .= "SET crawler_last_run = NOW() ";
        $q .= "WHERE id = :id ";
        $q .= "LIMIT 1 ";
        $vars = array(
            ':id'=>$id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function isUserConfigured($username, $network) {
        $q  = "SELECT network_username ";
        $q .= "FROM ".$this->getTableName()." ";
        $q .= "WHERE network_username = :username AND network = :network ";
        $q .= "LIMIT 1 ";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps);
    }

    public function isInstancePublic($username, $network) {
        $q  = "SELECT is_public ";
        $q .= "FROM ".$this->getTableName()." ";
        $q .= "WHERE network_username = :username AND network = :network ORDER BY is_public ASC LIMIT 1";
        $vars = array(
            ':username'=>$username,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowAsArray($ps);
        if (isset($result['is_public'])) {
            return ($result['is_public'] == 1);
        } else {
            return false;
        }
    }

    public function getByUserAndViewerId($network_user_id, $viewer_id, $network = 'facebook') {
        $q  = "SELECT ".$this->getFieldList();
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        $q .= "WHERE network_user_id = :network_user_id AND network_viewer_id = :viewer_id ";
        $q .= "AND network = :network";
        $vars = array(
            ':network_user_id'=>(string)$network_user_id,
            ':viewer_id'=>(string)$viewer_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, $this->object_name);
    }

    public function getByViewerId($viewer_id, $network = 'facebook') {
        $q  = "SELECT ".$this->getFieldList();
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        $q .= "WHERE network_viewer_id = :viewer_id AND network = :network ";
        $vars = array(
            ':viewer_id'=>(string)$viewer_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($ps, $this->object_name);
    }

    public function getHoursSinceLastCrawlerRun() {
        $q = "SELECT round((unix_timestamp( NOW() ) - unix_timestamp(crawler_last_run )) / 3600, 0) as hours_since_last_run ";
        $q .= "FROM ".$this->getTableName()." WHERE is_active=1 ORDER BY crawler_last_run ASC LIMIT 1";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q);
        $result = $this->getDataRowsAsArrays($ps);
        if ($result && isset($result[0]) ) {
            return $result[0]['hours_since_last_run'];
        } else  {
            return null;
        }
    }

    public function updateUsername($id, $network_username) {
        $q = "UPDATE ".$this->getTableName()." SET network_username = :network_username WHERE id = :id LIMIT 1";
        $vars = array(
            ':id'=>$id,
            ':network_username'=>$network_username
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function setPostArchiveLoaded($network_user_id, $network) {
        $q = "UPDATE ".$this->getTableName()." SET is_archive_loaded_posts = 1 WHERE network_user_id = :network_id AND";
        $q .= " network=:network";
        $vars[':network_id'] = $network_user_id;
        $vars[':network'] = $network;
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }
}
