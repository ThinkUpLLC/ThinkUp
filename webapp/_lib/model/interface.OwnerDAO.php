<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.OwnerDAO.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * Owner Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface OwnerDAO {
    /**
     * Gets owner by email address
     * @param str $email
     * @return Owner
     */
    public function getByEmail($email);

    /**
     * Gets all ThinkUp owners
     * @return array Of Owner objects
     */
    public function getAllOwners();
    /**
     * Checks whether or not owner is in storage
     * @param str $email
     * @return bool
     */
    public function doesOwnerExist($email);

    /**
     * Get password for activated owner by email
     * @param str $email
     * @return str|bool Password string or false if none
     */
    public function getPass($email);

    /**
     * Get activation code for an owner
     * @param str $email
     * @return str Activation code
     */
    public function getActivationCode($email);

    /**
     * Activate an owner
     * @param str $email
     * @return int Affected rows
     */
    public function updateActivate($email);

    /**
     * Set owner password
     * @param str $email
     * @param str $pwd
     * @return int Affected rows
     */
    public function updatePassword($email, $pwd);

    /**
     * Insert owner
     * @param str $email
     * @param str $pass
     * @param str $acode
     * @param str $full_name
     * @return int Affected rows
     */
    public function create($email, $pass, $acode, $full_name);

    /**
     * Update last_login field for given owner
     * @param str $email Owner's email
     * @return int Affected rows
     */
    public function updateLastLogin($email);

    /**
     * Update an owner's token for recovering their password
     * @param str $email The email address of the owner to set it for
     * @param str $token The MD5 token and timestamp, separated by an underscore
     * @return int Affected rows
     */
    public function updatePasswordToken($email, $token);

    /**
     * Load an owner by their recovery token
     * @param str $token The token to load, minus the timestamp
     * @return int The full Owner object
     */
    public function getByPasswordToken($token);

    /**
     * Check if admin owner exists
     *
     * @return bool Whether or not admin user exists in the store.
     */
    public function doesAdminExist();

    /**
     * Insert an activated admin owner
     *
     * @param str $email
     * @param str $pwd
     * @param str $activation_code
     * @param str $full_name
     * @return int Update count
     */
    public function createAdmin($email, $pwd, $activation_code, $full_name);

    /**
     * Promote an owner to admin status.
     *
     * @param str $email Owner email address.
     * @return int Update count
     */
    public function promoteToAdmin($email);
}