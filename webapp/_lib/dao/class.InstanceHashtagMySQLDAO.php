<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.InstanceHashtagMySQLDAO.php
 *
 * Copyright (c) 2012 Eduard Cucurella
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * InstanceHashtag Data Access Object MySQL Implementationn
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Eduard Cucurella
 * @author Eduard Cucurella <eduard[dot]cucu[dot]cat[at]gmail[dot]com>
 *
 */
class InstanceHashtagMySQLDAO extends PDODAO implements InstanceHashtagDAO {

    public function getByInstance($instance_id) {
        $q = "SELECT id, instance_id, hashtag_id, last_post_id, earliest_post_id
            FROM
                #prefix#instances_hashtags
            WHERE  instance_id = :instance_id";
        $vars = array(
            ':instance_id' => $instance_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod("select"); }
        $stmt = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($stmt, 'InstanceHashtag');
    }

    public function insert($instance_id, $hashtag_id) {
        $q = "INSERT IGNORE INTO #prefix#instances_hashtags (instance_id, hashtag_id) ";
        $q .= "VALUES (:instance_id,:hashtag_id)";
        $vars = array(
            ':instance_id' => $instance_id,
            ':hashtag_id' => $hashtag_id,
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $vars);
        $insert_count = $this->getInsertCount($stmt);
        return ($insert_count > 0);
    }

    public function delete($instance_id, $hashtag_id) {
        $q  = "DELETE FROM #prefix#instances_hashtags ";
        $q .= "WHERE instance_id=:instance_id AND hashtag_id=:hashtag_id;";
        $vars = array(
            ':instance_id'=>$instance_id,
            ':hashtag_id'=>$hashtag_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $delete_count = $this->getDeleteCount($ps);
        return ($delete_count > 0);
    }

    public function deleteByInstance($instance_id) {
        $q  = "DELETE FROM #prefix#instances_hashtags ";
        $q .= "WHERE instance_id=:instance_id;";
        $vars = array(
            ':instance_id'=>$instance_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod("delete"); }
        $ps = $this->execute($q, $vars);
        $delete_count = $this->getDeleteCount($ps);
        return ($delete_count > 0);
    }

    public function updateLastPostId($instance_id, $hashtag_id, $last_post_id) {
        $q = "UPDATE #prefix#instances_hashtags SET last_post_id=:last_post_id
        WHERE instance_id=:instance_id AND hashtag_id=:hashtag_id;";
        $vars = array(
                ':instance_id' => $instance_id,
                ':hashtag_id' => $hashtag_id,
                ':last_post_id' => $last_post_id,
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod("update"); }
        $ps = $this->execute($q, $vars);
        $update_count = $this->getUpdateCount($ps);
        return ($update_count > 0);
    }

    public function updateEarliestPostId($instance_id, $hashtag_id, $earliest_post_id) {
        $q = "UPDATE #prefix#instances_hashtags SET earliest_post_id=:earliest_post_id
                WHERE instance_id=:instance_id AND hashtag_id=:hashtag_id;";
        $vars = array(
                ':instance_id' => $instance_id,
                ':hashtag_id' => $hashtag_id,
                ':earliest_post_id' => $earliest_post_id,
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod("update"); }
        $ps = $this->execute($q, $vars);
        $update_count = $this->getUpdateCount($ps);
        return ($update_count > 0);
    }

    public function deleteInstanceHashtagsByHashtagId($hashtag_id){
        $q = "DELETE FROM #prefix#instances_hashtags WHERE hashtag_id=:hashtag_id;";
        $vars = array(':hashtag_id'=>$hashtag_id);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDeleteCount($ps);
    }

    public function getByUsername($username, $network) {
        $q = "SELECT t.id, t.hashtag, t.network, t.count_cache " .
             "FROM #prefix#hashtags t " .
             "INNER JOIN #prefix#instances_hashtags ih ON t.id=ih.hashtag_id " .
             "INNER JOIN #prefix#instances i ON ih.instance_id = i.id " .
             "WHERE i.network_username = :username AND i.network = :network;";
        $vars = array(
            ':username' => $username,
            ':network' => $network
        );
        $stmt = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($stmt, 'Hashtag');
    }
}
