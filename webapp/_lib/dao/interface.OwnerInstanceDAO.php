<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.OwnerInstanceDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie
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
 * OwnerInstance Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
interface OwnerInstanceDAO {
    /**
     * Check if an Owner has access to an instance
     * @throws BadArgumentException If we do not pass a valid owner object
     * @param Owner
     * @param Instance
     * @return bool true if yes, false if not
     */
    public function doesOwnerHaveAccessToInstance(Owner $owner, Instance $instance);
    /**
     * Check if an Owner has access to a an individual post
     * @throws BadArgumentException If we do not pass a valid owner object
     * @param Owner
     * @param Post
     * @return bool true if yes, false if not
     */
    public function doesOwnerHaveAccessToPost(Owner $owner, Post $post);
    /**
     * Get an owner instance by owner_id and instance_id
     * @param int owner_id
     * @param int instance_id
     * @return OwnerInstance
     */
    public function get($owner_id, $instance_id);
    /**
     * Get owner instances by an instance ID
     * @param int instance_id
     * @return array OwnerInstance objects
     */
    public function getByInstance($instance_id);
    /**
     * Get owner instances by an owner ID
     * @param int owner_id
     * @return array OwnerInstance objects
     */
    public function getByOwner($owner_id);
    /**
     * Inserts an owner instance record
     * @param int owner_id
     * @param int instance_id
     * @param str auth_token
     * @param str oauth_token_secret
     * @return bool - if insert was successful
     */
    public function insert($owner_id, $instance_id, $oauth_token = '', $oauth_token_secret = '');
    /**
     * Delete an owner instance record
     * @param int owner_id
     * @param int instance_id
     * @return int Number of rows affected
     */
    public function delete($owner_id, $instance_id);
    /**
     * Delete all owner instances by instance ID.
     * @param int instance_id
     * @return int Number of rows affected
     */
    public function deleteByInstance($instance_id);
    /**
     * Updates tokens based on user and instance ids, return true|false  update status
     * @param int owner_id
     * @param int instance_id
     * @param str oauth_token
     * @param str oauth_token_secret
     * @return bool
     */
    public function updateTokens($owner_id, $instance_id, $oauth_token, $oauth_token_secret);
    /**
     * Updates auth error for instance/auth tokens, return true|false for update status.
     * @param int $instance_id
     * @param str $oauth_access_token
     * @param str $oauth_access_token_secret
     * @param str auth_error Optional, leave blank or null when there's no error during successful auth
     * @return bool
     */
    public function setAuthErrorByTokens($instance_id, $oauth_access_token, $oauth_access_token_secret,
    $auth_error="");
    /**
     * Gets auth tokens by instance_id
     * @param int instance_id
     * @return array $token_assoc_array
     */
    public function getOAuthTokens($id);
    /**
     * Get owner email address by tokens
     * @param str $instance_id
     * @param str $access_token
     * @param str $oauth_access_token_secret Defaults to empty string
     * @return str Email address of the owner associated with auth tokens
     */
    public function getOwnerEmailByInstanceTokens($instance_id, $access_token, $oauth_access_token_secret='');
}
