<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.GroupDAO.php
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
 *
 * Group Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 SwellPath, Inc.
 * @author Christian G. Warden <cwarden[at]xerus[dot]org>
 */
interface GroupDAO {
    /**
     * Checks whether a given group exists in storage.
     * @param int $group_id
     * @param str $network
     * @param bool $is_active Whether or not group should be active only
     * @return bool true if group exists
     */
    public function isGroupInStorage($group_id, $network, $is_active=false);
    /**
     * Update existing or insert new group
     * @param Group $group
     * @return bool Whether or not a group was updated or inserted
     */
    public function updateOrInsertGroup(Group $group);
    /**
     * Updates 'last seen' in storage.
     * @param str $group_id
     * @param str $group_name
     * @param str $network
     * @return int update count
     */
    public function update($group_id, $group_name, $network);
    /**
     * Deactivates a group in storage.
     * @param str $group_id
     * @param str $network
     * @return int update count
     */
    public function deactivate($group_id, $network);
    /**
     * Adds a group to storage
     * @param str $group_id
     * @param str $group_name
     * @param str $network
     * @return int Inserted ID
     */
    public function insert($group_id, $group_name, $network);
}
