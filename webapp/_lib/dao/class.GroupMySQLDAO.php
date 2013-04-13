<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.GroupMySQLDAO.php
 *
 * Copyright (c) 2011-2013 SwellPath, Inc.
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
 * Group MySQL Data Access Object Implementation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 SwellPath, Inc.
 * @author Christian G. Warden <cwarden[at]xerus[dot]org>
 *
 */
class GroupMySQLDAO extends PDODAO implements GroupDAO {
    public function isGroupInStorage($group_id, $network, $is_active=false) {
        $q = "SELECT 1 FROM #prefix#groups ";
        $q .= "WHERE group_id = :group_id AND network = :network ";
        if ($is_active) {
            $q .= "AND is_active=1";
        }
        $q .= ";";
        $vars = array(
            ':group_id'=>(string)$group_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataIsReturned($ps);
    }

    public function updateOrInsertGroup(Group $group) {
        if (!isset($group->group_id) || !isset($group->group_name) || !isset($group->network)) {
            return false;
        }
        $group_id = $group->group_id;
        $group_name = $group->group_name;
        $network = $group->network;
        if ($this->isGroupInStorage($group_id, $network)) {
            $result = $this->update($group_id, $group_name, $network);
        } else {
            $result = $this->insert($group_id, $group_name, $network);
        }
        return (isset($result) && $result > 0);
    }

    public function update($group_id, $group_name, $network) {
        $q = "UPDATE #prefix#groups ";
        $q .= "SET group_name = :group_name, ";
        $q .= "last_seen = NOW() ";
        $q .= "WHERE group_id = :group_id AND network = :network;";
        $vars = array(
            ':group_id'=>(string)$group_id,
            ':network'=>$network,
            ':group_name'=>$group_name,
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getUpdateCount($ps);
    }

    public function deactivate($group_id, $network) {
        $q = "UPDATE #prefix#groups ";
        $q .= "SET is_active = 0 ";
        $q .= "WHERE group_id = :group_id AND network = :network;";
        $vars = array(
            ':group_id'=>(string)$group_id,
            ':network'=>$network,
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getUpdateCount($ps);
    }

    public function insert($group_id, $group_name, $network) {
        $q = "INSERT INTO #prefix#groups ";
        $q .= "(group_id, group_name, first_seen, last_seen, network) ";
        $q .= "VALUES ( :group_id, :group_name, NOW(), NOW(), :network );";
        $vars = array(
            ':group_id'=>(string)$group_id,
            ':group_name'=>$group_name,
            ':network'=>$network,
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getInsertId($ps);
    }
}
