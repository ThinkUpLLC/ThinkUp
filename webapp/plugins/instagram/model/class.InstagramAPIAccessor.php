<?php
/**
 *
 * ThinkUp/webapp/plugins/instagram/model/PHP5.3/class.InstagramAPIAccessor.php
 *
 * Copyright (c) 2013 Dimosthenis Nikoudis
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
 * Instagram API Accessor
 *
 * @author Dimosthenis Nikoudis <dnna[at]dnna[dot]gr>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Dimosthenis Nikoudis
 */
class InstagramAPIAccessor {
    /**
     * Currently-authenticated Instagram user
     * @var Instagram/User
     */
    var $current_user;
    /**
     * Instagram object
     * @var Instagram/Instagram
     */
    var $instagram;
    /**
     * Constructor
     * @param str $access_token
     * @return InstagramAPIAccessor
     */
    public function __construct($access_token) {
        $this->instagram = new Instagram\Instagram($access_token);
        $this->current_user = $this->instagram->getCurrentUser();
    }
    /**
     * Return the currently-authenticated Instagram user.
     * @return Instagram/User
     */
    public function getCurrentUser() {
        return $this->current_user;
    }
    /**
     * Make an API request.
     * @param str $type String representing the endpoint, 'user', 'followers', 'media', 'relationship'
     * @param arr $params API call params
     * @return Object
     */
    public function apiRequest($type, $params = array()) {
        if ($type == 'user') {
            return $this->instagram->getUser($params['user_id']);
        } else if ($type == 'followers') {
            return $this->current_user->getFollowers($params);
        } else if ($type == 'media') {
            return $this->current_user->getMedia($params);
        } else if ($type == 'relationship') {
            $user = $this->instagram->getUser($params['user_id']);
            return $this->current_user->getRelationship($user);
        }
    }
}
