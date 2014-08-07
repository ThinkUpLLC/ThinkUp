<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.UserVersionsDAO.php
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
 * UserVersions Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer
 *
 */
interface UserVersionsDAO {
    /**
     * Add version history for a give user and field
     * @param int $user_key The id from the users table
     * @param str $field_name A field name from the users table (ex. description)
     * @param str $field_value Latest value for the field
     */
    public function addVersionOfField($user_key, $field_name, $field_value);

    /**
     * Fetch recent version changes for a user's friends
     * @param int $user_key The id from the users table
     * @param int $past_x_days How many days of history to fetch
     * @param arr $fields What fields do we care about (default: all)
     * @return arr An array of field versions.
     */
    public function getRecentFriendsVersions($user_key, $past_x_days, $fields = array());

    /**
     * Fetch recent version changes for a user
     * @param int $user_key The id from the users table
     * @param int $past_x_days How many days of history to fetch
     * @param arr $fields What fields do we care about (default: all)
     * @return arr An array of field versions.
     */
    public function getRecentVersions($user_key, $past_x_days, $fields = array());
}
