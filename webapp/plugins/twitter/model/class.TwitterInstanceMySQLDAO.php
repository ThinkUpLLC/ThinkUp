<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterInstanceMySQLDAO.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 */
class TwitterInstanceMySQLDAO extends InstanceMySQLDAO implements InstanceDAO {

    public function __construct() {
        parent::__construct();
        $this->setObjectName('TwitterInstance');
        $this->setMetaTableName('instances_twitter');
    }

    public function insert($network_user_id, $network_username, $network = "twitter", $viewer_id = false) {
        $id = parent::insert($network_user_id, $network_username, $network, $viewer_id);
        $q  = "INSERT INTO ".$this->getMetaTableName()." ";
        $q .= "(id, last_reply_id) ";
        $q .= "VALUES (:instance_id, '') ";
        $vars = array(
            ':instance_id'=>$id
        );
        $ps = $this->execute($q, $vars);
        return $this->getInsertId($ps);
    }

    public function delete($network_username, $network) {
        $instance = $this->getByUsername($network_username, $network);
        $result = parent::delete($network_username, $network);
        if (isset($instance)) {
            $q  = "DELETE FROM ".$this->getMetaTableName()." ";
            $q .= "WHERE id = :id;";
            $vars = array(
            ':id'=>$instance->id
            );
            $ps = $this->execute($q, $vars);
            return $this->getUpdateCount($ps);
        }
        return $result;
    }

    public function save($instance_object, $user_xml_total_posts_by_owner, $logger = false) {
        parent::save($instance_object, $user_xml_total_posts_by_owner, $logger);
        if ($this->doesMetaDataExist($instance_object->id)) {
            if ($logger){
                $status_message = "Updated ".$instance_object->network_username."'s Twitter instance status.";
                $logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
            }
            return $this->updateMetaData($instance_object);
        } else {
            if ($logger){
                $status_message = "Updated ".$instance_object->network_username.
                "'s Twitter instance status (meta inserted).";
                $logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
            }
            $this->insertMetaData($instance_object);
            return 1;
        }
    }

    /**
     * Insert row into metatable given a populated instance.
     * @param TwitterInstance $instance_object
     * @return int Insert id
     */
    private function insertMetaData($instance_object) {
        $q  = "INSERT INTO ".$this->getMetaTableName()." ";
        $q .= "(id, ";
        $lfi = ($instance_object->last_favorite_id != "" ? true : false);
        if ($lfi){
            $q .= "last_favorite_id, ";
        }
        $q .= "last_reply_id) ";
        $q .= "VALUES (:instance_id, ";
        if ($lfi){
            $q .= ":last_favorite_id, ";
        }
        $q .= ":last_reply_id) ";
        $vars = array(
            ':instance_id'                  => $instance_object->id,
            ':last_favorite_id'             => $instance_object->last_favorite_id,
            ':last_reply_id'                => isset($instance_object->last_reply_id)?
            $instance_object->last_reply_id:'',
        );
        if (!$lfi){
            unset ($vars[':last_favorite_id']);;
        }
        $ps = $this->execute($q, $vars);
        return $this->getInsertId($ps);
    }

    /**
     * Update row into metatable given a populated instance.
     * @param TwitterInstance $instance_object
     * @return int Number of affected rows
     */
    private function updateMetaData($instance_object) {
        $lfi = ($instance_object->last_favorite_id != "" ? true : false);
        isset($instance_object->last_reply_id)?$instance_object->last_reply_id:1;
        $q  = "UPDATE ".$this->getMetaTableName()." SET ";
        if ($lfi){
            $q .= "last_favorite_id = :lastfavid, ";
        }
        $q .= "last_reply_id = :lpfr ";
        $q .= "WHERE id=:id;";

        $vars = array(
            ':lastfavid'     => $instance_object->last_favorite_id,
            ':lpfr'         => $instance_object->last_reply_id,
            ':id'           => $instance_object->id
        );
        if (!$lfi){
            unset ($vars[':lastfavid']);;
        }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function getByOwnerAndNetwork($owner, $network, $disregard_admin_status = false, $active_only = false) {
        $admin_status = (!$disregard_admin_status && $owner->is_admin ? true : false);
        //add DISTINCT to no repit instances than have more than one owner
        //add column is_twitter_referenced_instance from tu_owner_instances
        //only select instances that have relation with an owner
        $q  = "SELECT DISTINCT ".$this->getFieldList(). ", oi.is_twitter_referenced_instance ";
        $q .= "FROM ".$this->getTableName()." ";
        $q .= $this->getMetaTableJoin();
        if (!$admin_status) {
            $q .= "INNER JOIN #prefix#owner_instances AS oi ";
            $q .= "ON ".$this->getTableName().".id = oi.instance_id ";
        } else { 
            //add table tu_owner_instances if admin_status
            $q .= ", #prefix#owner_instances oi "; 
        }
        $q .= "WHERE network=:network ";
        if (!$admin_status) {
            $q .= "AND oi.owner_id = :ownerid ";
        } else {
            $q .= "AND #prefix#instances.id = oi.instance_id ";
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
}