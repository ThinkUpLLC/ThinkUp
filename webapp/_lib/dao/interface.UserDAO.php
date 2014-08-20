<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.UserDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * User Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface UserDAO {

    /**
     * Check if a user is in the database given a user ID
     * @param int $user_id
     * @param str $network
     * @return bool True if yes, false if not
     */
    public function isUserInDB($user_id, $network);
    /**
     * Check if a user is in the database given a username
     * @param str $username
     * @param str $network
     * @return bool True if yes, false if not
     */
    public function isUserInDBByName($username, $network);

    /**
     * Update existing or insert new user
     * @param User $user
     * @return int Total number of affected rows
     */
    public function updateUser($user);

    /**
     * Get user given an ID
     * @param int $user_id
     * @param str $network
     * @return User User
     */
    public function getDetails($user_id, $network);

    /**
     * Get user given a user key (id from the tu_users table)
     * @param int $user_key
     * @return User User
     */
    public function getDetailsByUserKey($user_key);

    /**
     * Update an array of users
     * @param array $users_to_update Array of User objects
     * @return int Total users affected
     */
    public function updateUsers($users_to_update);

    /**
     * Get user given a username
     * @param str $user_name
     * @param str $network
     * @return User User object
     */
    public function getUserByName($user_name, $network);

    /**
     * Delete users given a hashtag ID.
     * @param str $hashtag_id
     * @return int Total number of affected rows
     */
    public function deleteUsersByHashtagId($hashtag_id);
}
