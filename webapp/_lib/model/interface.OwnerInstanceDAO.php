<?php
/**
 * OwnerInstance Data Access Object interface
 *
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
interface OwnerInstanceDAO {

    /**
     * Check if an Owner has access to an instance 
     * @throws BadArgumentException If we do not pass a valid owner object
     * @param Owner
     * @param str username
     * @return bool true if yes, false if not
     */
    public function doesOwnerHaveAccess($owner, $username);

    /*
     * Get an instance by $owner_id and instance_id
     * @param int owner_id
     * @param int instance_id
     * @return OwnerInstance
     */
    public function get($owner_id, $instance_id);

    /**
     * Inserts an owner instance record
     *
     * @param int owner_id
     * @param int instance_id
     * @param str auth_token
     * @param str oauth_token_secret
     * @return boolean - if insert was successful
     */
    public function insert($owner_id, $instance_id, $oauth_token = '', $oauth_token_secret = '');

    /*
     * Updates tokens based on user and instance ids, return true|false  update status
     * @param int owner_id
     * @param int instance_id
     * @param str oauth_token
     * @param str oauth_token_secret
     * @return boolean
     */
    public function updateTokens($owner_id, $instance_id, $oauth_token, $oauth_token_secret);

    /**
     * Gets auth tokens by instance_id
     *
     * @param int instance_id
     * @return array $token_assoc_array
     */
    public function getOAuthTokens($id);

}
