<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.UserVersionsMySQLDAO.php
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
 * UserVersions Data Access Object
 * The data access object for creating and deleting UserVersions records from the database
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer
 *
 */
class UserVersionsMySQLDAO extends PDODAO implements UserVersionsDAO {
    public function addVersionOfField($user_key, $field_name, $field_value) {
        $q = "INSERT INTO #prefix#user_versions (user_key, field_name, field_value) ";
        $q .= " VALUES (:user_key, :field_name, :field_value)";
        $vars = array(
            ':user_key' => $user_key,
            ':field_name' => $field_name,
            ':field_value' => $field_value
        );

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
    }

    public function getRecentFriendsVersions($user_key, $past_x_days, $fields = array()) {
        $q = "SELECT user_id, network FROM #prefix#users WHERE id=:user_key";
        $ps = $this->execute($q, array(':user_key' => $user_key));
        $user = $this->getDataRowsAsArrays($ps);
        if (count($user) < 1) {
            return null;
        }

        $vars = array(
            ':user_id' =>$user[0]['user_id'],
            ':network' =>$user[0]['network'],
            ':days_ago' =>$past_x_days
        );

        $q  = "SELECT user_key, field_name, field_value, crawl_time FROM #prefix#user_versions uv ";
        $q .= "WHERE crawl_time > date_sub(NOW(), INTERVAL :days_ago day) ";
        $q .= "AND user_key IN (SELECT id FROM #prefix#users u LEFT JOIN #prefix#follows f ON ";
        $q .= "   (f.network=u.network AND u.user_id=f.user_id) ";
        $q .= "   WHERE f.follower_id=:user_id AND f.network=:network AND  f.active=1) ";
        if (count($fields)) {
            $q .= " AND field_name IN (";
            $tojoin = array();
            foreach ($fields as $field) {
                $tojoin[] = ':field_'.$field;
                $vars[':field_'.$field] = $field;
            }
            $q .= join(',', $tojoin).")";
        }
        $q .= " ORDER BY crawl_time DESC";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }

    public function getRecentVersions($user_key, $past_x_days, $fields = array()) {
        $vars = array(
            ':user_key' =>$user_key,
            ':days_ago' =>$past_x_days
        );

        $q  = "SELECT user_key, field_name, field_value, crawl_time FROM #prefix#user_versions as uv ";
        $q .= "WHERE uv.user_key = :user_key AND crawl_time > date_sub(NOW(), INTERVAL :days_ago day) ";
        if (count($fields)) {
            $q .= " AND field_name IN (";
            $tojoin = array();
            foreach ($fields as $field) {
                $tojoin[] = ':field_'.$field;
                $vars[':field_'.$field] = $field;
            }
            $q .= join(',', $tojoin).")";
        }
        $q .= " ORDER BY crawl_time DESC";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }
}
