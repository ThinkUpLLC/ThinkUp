<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.OwnerDAO.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * @copyright 2009-2011 Gina Trapani
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
    public function activateOwner($email);

    /**
     * Dectivate an owner
     * @param str $email
     * @return int Affected rows
     */
    public function deactivateOwner($email);

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
     * @param str $salt
     * @return int Affected rows
     */
    public function create($email, $pass, $salt, $acode, $full_name);

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
     * @param str $salt
     * @return int Update count
     */
    public function createAdmin($email, $pwd, $salt, $activation_code, $full_name);

    /**
     * Promote an owner to admin status.
     *
     * @param str $email Owner email address.
     * @return int Update count
     */
    public function promoteToAdmin($email);

    /**
     * Gets a list of the admin users
     *
     * @return array An array of Owners
     */
    public function getAdmins();

    /**
     * Increment the number of failed logins for a given owner.
     * @param str $email
     */
    public function incrementFailedLogins($email);

    /**
     * Reset the number of failed login attempts to 0 (called on a successful login).
     * @param str $email
     * @return bool True on success
     */
    public function resetFailedLogins($email);

    /**
     * Set the contents of the acount status field for an owner.
     * @param str $email
     * @param str $status
     * @return bool True on success
     */
    public function setAccountStatus($email, $status);

    /**
     * Sets the account status to an empty string.
     * @param str $email
     * @return bool True on success
     */
    public function clearAccountStatus($email);

    /**
     * Activates an owner account.
     *
     * @param str $owner_id
     * @param int $is_activated Active = 1, Inactive=0.
     * @return int number of updated rows.
     */
    public function setOwnerActive($id, $is_activated);

    /**
     * Generates and sets a new API key.
     *
     * @param str $owner_id
     * @return str A new API Key
     */
    public function resetAPIKey($owner_id);

    /**
     * Generates a salt for secure password storage
     * @param str $email
     * @return str Salt
     */
    public function generateSalt($email);
   
    /**
     * Combines the users password with the salt and hashes them
     *  @param str $password
     *  @param str $salt
     *  @return str Password
     */
    public function generatePassword($password, $salt);
    
    /**
     * Retrives the salt for a given user
     * @param str $email
     * @return str Salt
     */
    public function getSaltByEmail($email);
       
    /**
     * Updates the password salt for a given user
     * @param str $email
     * @param str $salt
     * @return int Number of rows updated
     */
    public function updateSalt($email, $salt);
    
    /**
     * Checks if a unique salted password given by a user is correct
     * @param str $email
     * @param str $password
     * @return boolean True if the password supplied matches the one in the database
     */    
    public function checkSaltedPassword($email, $password);
}