<?php
/**
 * Owner Data Access Object interface
 *
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
     * Gets all ThinkTank owners
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
     * Get activated owner by email (for login purposes)
     * @param str $email
     * @return array Array of owner values
     */
    public function getForLogin($email);

    /**
     * Get password for activated owner by email
     * @param str $email
     * @return array|bool $row['pwd'] or false if none
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
     * @param str $country
     * @param str $acode
     * @param str $full_name
     * @return int Affected rows
     */
    public function create($email, $pass, $country, $acode, $full_name);

    /**
     * Update last_login field for given owner
     * @param str $email Owner's email
     * @return int Affected rows
     */
    public function updateLastLogin($email);
}
