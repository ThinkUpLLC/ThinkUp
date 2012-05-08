<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.GroupMemberMySQLDAO.php
 *
 * Copyright (c) 2011-2012 SwellPath, Inc.
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
 * Group Member MySQL Data Access Object Implementation
 * (based on class.FollowMySQLDAO.php)
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2012 SwellPath, Inc.
 * @author Christian G. Warden <cwarden[at]xerus[dot]org>
 *
 */
class GroupMemberMySQLDAO extends PDODAO implements GroupMemberDAO {
    public function isGroupMemberInStorage($user_id, $group_id, $network, $is_active=false) {
        $q = "SELECT 1 ";
        $q .= "FROM #prefix#group_members ";
        $q .= "WHERE member_user_id = :user_id AND group_id = :group_id AND network = :network ";
        if ($is_active) {
            $q .= "AND is_active=1";
        }
        $q .= ";";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':group_id'=>(string)$group_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);

        return $this->getDataIsReturned($ps);
    }

    public function update($user_id, $group_id, $network) {
        $q = " UPDATE #prefix#group_members ";
        $q .= "SET last_seen=NOW(), ";
        $q .= "is_active = 1 ";
        $q .= "WHERE member_user_id = :user_id AND group_id = :group_id AND network = :network;";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':group_id'=>(string)$group_id,
            ':network'=>$network,
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);

        return $this->getUpdateCount($ps);
    }

    public function deactivate($user_id, $group_id, $network) {
        $q = "UPDATE #prefix#group_members ";
        $q .= "SET is_active = 0 ";
        $q .= "WHERE member_user_id = :user_id AND group_id = :group_id AND network = :network;";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':group_id'=>(string)$group_id,
            ':network'=>$network,
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);

        return $this->getUpdateCount($ps);
    }

    public function insert($user_id, $group_id, $network) {
        $q  = "INSERT INTO #prefix#group_members ";
        $q .= "(member_user_id, group_id, first_seen, last_seen, network) ";
        $q .= "VALUES ( :user_id, :group_id, NOW(), NOW(), :network );";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':group_id'=>(string)$group_id,
            ':network'=>$network,
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);

        return $this->getInsertCount($ps);
    }

    public function getTotalGroups($user_id, $network, $active = true) {
        $q = "SELECT count(g.group_id) AS count FROM #prefix#group_members AS g ";
        $q .= "WHERE g.member_user_id = :user_id AND g.network=:network ";
        if ($active) {
            $q .= 'AND g.is_active = 1';
        }
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);

        return $this->getDataCountResult($ps);
    }

    public function getFormerGroups($user_id, $network, $count = 20, $page = 1) {
        $start_on_record = ($page - 1) * $count;

        $q  = "SELECT g.group_id, g.group_name, g.network FROM #prefix#groups AS g ";
        $q .= "INNER JOIN #prefix#group_members AS m ON m.group_id = g.group_id AND g.network = m.network ";
        $q .= "WHERE m.member_user_id = :user_id and m.network = :network AND m.is_active=0 ";
        $q .= "ORDER BY g.id LIMIT :start_on_record, :count";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network,
            ':count'=>(int)$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function findStalestMemberships($user_id, $network) {
        $q  = "SELECT g.id, g.group_id, g.group_name, g.network, DATEDIFF(NOW(), m.last_seen) AS days_old ";
        $q .= "FROM #prefix#groups AS g ";
        $q .= "INNER JOIN #prefix#group_members AS m ON m.group_id = g.group_id AND g.network = m.network ";
        $q .= "WHERE m.member_user_id = :user_id and m.network = :network AND m.is_active=1 ";
        $q .= "AND m.last_seen < DATE_SUB(NOW(), INTERVAL 1 DAY) ";
        $q .= "ORDER BY days_old DESC LIMIT 1";
        $vars = array(
            ':user_id'=>(string)$user_id,
            ':network'=>$network
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);

        return $this->getDataRowAsObject($ps, "Group");
    }

    public function getNewMembershipsByDate($network, $member_user_id, $from_date=null) {
        $vars = array(
            ':member_user_id'=> $member_user_id,
            ':network'=>$network
        );
        if (!isset($from_date)) {
            $from_date = 'CURRENT_DATE()';
        } else {
            $vars[':from_date'] = $from_date;
            $from_date = ':from_date';
        }

        $q = "SELECT g.* FROM #prefix#group_members gm INNER JOIN #prefix#groups g on g.group_id = gm.group_id ";
        $q .= "WHERE gm.member_user_id=:member_user_id AND g.network=:network AND gm.is_active=1 ";
        $q .= "AND  (YEAR(gm.first_seen)=YEAR($from_date)) ";
        $q .= "AND (DAYOFMONTH(gm.first_seen)=DAYOFMONTH($from_date)) AND (MONTH(gm.first_seen)=MONTH($from_date)) ";
        $q .= "ORDER BY gm.first_seen DESC;";

        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsObjects($ps, "Group");
    }
}
