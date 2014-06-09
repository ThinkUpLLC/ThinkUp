<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.FacebookInstanceMySQLDAO.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * @author Chris Moyer <chris[at]inarow[dot]net>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 */
class FacebookInstanceMySQLDAO extends InstanceMySQLDAO implements InstanceDAO {

    public function __construct() {
        parent::__construct();
        $this->setObjectName('FacebookInstance');
        $this->setMetaTableName('instances_facebook');
    }

    public function insert($network_user_id, $network_username, $network = "facebook", $viewer_id = false) {
        $id = parent::insert($network_user_id, $network_username, $network, $viewer_id);
        $q  = "INSERT INTO ".$this->getMetaTableName()." ";
        $q .= "(id, profile_updated) ";
        $q .= "VALUES (:instance_id, null) ";
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
                $status_message = "Updated ".$instance_object->network_username."'s Facebook instance status.";
                $logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
            }
            return $this->updateMetaData($instance_object);
        } else {
            if ($logger){
                $status_message = "Updated ".$instance_object->network_username.
                "'s Facebook instance status (meta inserted).";
                $logger->logUserSuccess($status_message, __METHOD__.','.__LINE__);
            }
            $this->insertMetaData($instance_object);
            return 1;
        }
    }

    /**
     * Insert row into metatable given a populated instance.
     * @param FacebookInstance $instance_object
     * @return int Insert id
     */
    private function insertMetaData($instance_object) {
        $q  = "INSERT INTO ".$this->getMetaTableName()." ";
        $q .= "(id, profile_updated) ";
        $q .= "VALUES (:instance_id, :profile_updated) ";
        $vars = array(
            ':instance_id'                  => $instance_object->id,
            ':profile_updated'              => $instance_object->profile_updated
        );
        $ps = $this->execute($q, $vars);
        return $this->getInsertId($ps);
    }

    /**
     * Update row into metatable given a populated instance.
     * @param FacebookInstance $instance_object
     * @return int Number of affected rows
     */
    private function updateMetaData($instance_object) {
        $q  = "UPDATE ".$this->getMetaTableName()." SET ";
        $q .= "profile_updated = :profile_updated ";
        $q .= "WHERE id=:id;";

        $vars = array(
            ':profile_updated' => $instance_object->profile_updated,
            ':id'              => $instance_object->id
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }
}
