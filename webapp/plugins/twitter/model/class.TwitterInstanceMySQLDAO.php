<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterInstanceMySQLDAO.php
 *
 * Copyright (c) 2011-2016 Gina Trapani
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
 * @copyright 2011-2016 Gina Trapani
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
        $has_last_fav_id = ($instance_object->last_favorite_id != "" ? true : false);
        if ($has_last_fav_id){
            $q .= "last_favorite_id, ";
        }
        $q .= "last_reply_id, last_follower_id_cursor) ";
        $q .= "VALUES (:instance_id, ";
        if ($has_last_fav_id){
            $q .= ":last_favorite_id, ";
        }
        $q .= ":last_reply_id, :last_follower_id_cursor) ";
        $vars = array(
            ':instance_id'                  => $instance_object->id,
            ':last_favorite_id'             => $instance_object->last_favorite_id,
            ':last_reply_id'                => isset($instance_object->last_reply_id)?
                $instance_object->last_reply_id:'',
            ':last_follower_id_cursor'      => $instance_object->last_follower_id_cursor,
        );
        if (!$has_last_fav_id){
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
        $has_last_fav_id = ($instance_object->last_favorite_id != "" ? true : false);
        isset($instance_object->last_reply_id)?$instance_object->last_reply_id:1;
        $q  = "UPDATE ".$this->getMetaTableName()." SET ";
        if ($has_last_fav_id){
            $q .= "last_favorite_id = :last_fav_id, ";
        }
        $q .= "last_reply_id = :last_reply_id,  last_follower_id_cursor = :last_follower_id_cursor ";
        $q .= "WHERE id=:id;";

        $vars = array(
            ':last_fav_id'      => $instance_object->last_favorite_id,
            ':last_reply_id'    => $instance_object->last_reply_id,
            ':last_follower_id_cursor'    => $instance_object->last_follower_id_cursor,
            ':id'               => $instance_object->id
        );
        if (!$has_last_fav_id){
            unset ($vars[':last_fav_id']);;
        }
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }
}