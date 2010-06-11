<?php
/**
 * User Data Access Object interface
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface UserDAO {

    /**
     * Check if a user is in the database given a user ID
     * @param int $user_id
     * @return bool True if yes, false if not
     */
    public function isUserInDB($user_id);
    /**
     * Check if a user is in the database given a username
     * @param str $username
     * @return bool True if yes, false if not
     */
    public function isUserInDBByName($username);

    /**
     * Update existing or insert new user
     * @param User $user
     * @return int Total number of affected rows
     */
    public function updateUser($user);

    /**
     * Get user given an ID
     * @param int $user_id
     * @return User User
     */
    public function getDetails($user_id);

    /**
     * Update an array of users
     * @param array $users_to_update Array of User objects
     * @return int Total users affected
     */
    public function updateUsers($users_to_update);

    /**
     * Get user given a username
     * @param str $user_name
     * @return User User object
     */
    public function getUserByName($user_name);
}
