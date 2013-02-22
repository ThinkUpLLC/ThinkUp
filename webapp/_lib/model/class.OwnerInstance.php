<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.OwnerInstance.php
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
 * OwnerInstance class
 *
 * This class represents an owner instance
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class OwnerInstance {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var int Owner ID.
     */
    var $owner_id;
    /**
     * @var int Instance ID.
     */
    var $instance_id;
    /**
     * @var str OAuth access token (optional).
     */
    var $oauth_access_token;
    /**
     * @var str OAuth secret access token (optional).
     */
    var $oauth_access_token_secret;
    /**
     * @var str Last authorization error, if there was one.
     */
    var $auth_error;
    public function __construct($row = false) {
        if ($row) {
            $this->id = $row['id'];
            $this->owner_id = $row['owner_id'];
            $this->instance_id = $row['instance_id'];
            $this->oauth_access_token = $row['oauth_access_token'];
            $this->oauth_access_token_secret = $row['oauth_access_token_secret'];
            $this->auth_error = $row['auth_error'];
        }
    }
}