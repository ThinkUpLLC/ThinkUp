<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.GroupMemberDAO.php
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
 * Group Member Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 SwellPath, Inc.
 * @author Christian G. Warden <cwarden[at]xerus[dot]org>
 */
interface GroupMemberDAO {
    /**
     * Checks whether a given group member exists in storage.
     * @param int $user_id
     * @param int $group_id
     * @param str $network
     * @param bool $is_active Whether or not member should be active only
     * @return bool true if group member is in storage
     */
    public function isGroupMemberInStorage($user_id, $group_id, $network, $is_active=false);
    /**
     * Updates 'last seen' in storage.
     * @param int $user_id
     * @param int $group_id
     * @param str $network
     * @return int update count
     */
    public function update($user_id, $group_id, $network);

    /**
     * Deactivates a group membership in storage.
     * @param int $user_id
     * @param int $group_id
     * @param str $network
     * @return int update count
     */
    public function deactivate($user_id, $group_id, $network);
    /**
     * Adds a group membership to storage.
     * @param int $user_id
     * @param int $group_id
     * @param str $network
     * @return int insert count
     */
    public function insert($user_id, $group_id, $network);
    /**
     * Get the total number of group membership in storage for a given user.
     * @param int $user_id
     * @param str $network
     * @param bool $active
     * @return int the number
     */
    public function getTotalGroups($user_id, $network, $active=true);
    /**
     * Gets a list of inactive group memberships.
     * @param int $user_id
     * @param str $network
     * @param int $count
     * @param int $page defaults to 1
     * @return array - numbered keys, with arrays - named keys
     */
    public function getFormerGroups($user_id, $network, $count = 20, $page = 1);

    /**
     * Get a list of group memberships first seen on a given date.
     * @param str $network
     * @param str $user_id
     * @param str $from_date Defaults to null (today)
     * @return arr Group objects
     */
    public function getNewMembershipsByDate($network, $user_id, $from_date=null);
}
