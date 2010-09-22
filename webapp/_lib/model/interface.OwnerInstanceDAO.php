<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.OwnerInstanceDAO.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Mark Wilkie
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
 * OwnerInstance Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Mark Wilkie
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
    public function doesOwnerHaveAccess($owner, $instance);

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
