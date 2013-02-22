<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Owner.php
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
 * ThinkUp User, i.e., owner of social network user accounts
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Owner {
    /**
     * @var int
     */
    var $id;
    /**
     *
     * @var str
     */
    var $full_name;
    /**
     *
     * @var str
     */
    var $email;
    /**
     *
     * @var bool Default false
     */
    var $is_admin = false;
    /**
     *
     * @var bool Default false
     */
    var $is_activated = false;
    /**
     *
     * @var str Date
     */
    var $last_login;
    /**
     *
     * @var array Of instances
     */
    var $instances = null;
    /**
     * Token to email to user for resetting password
     * @var str
     */
    var $password_token;

    /**
     * Count of failed login attempts
     * @var int
     */
    var $failed_logins;

    /**
     * String describing acount status, like "Too many failed logins" or "Never activated"
     * @var str
     */
    var $account_status;

    /**
     * String api_key for API auth
     * @var str
     */
    var $api_key;

    /**
     * Constructor
     * @param array $val Key/value pairs to construct Owner
     * @return Owner
     */
    public function __construct($val=false) {
        if ($val) {
            $this->id = $val["id"];
            $this->full_name = $val["full_name"];
            $this->email = $val['email'];
            $this->last_login = $val['last_login'];
            $this->is_admin = PDODAO::convertDBToBool($val["is_admin"]);
            $this->is_activated = PDODAO::convertDBToBool($val["is_activated"]);
            $this->account_status = $val["account_status"];
            $this->failed_logins = $val["failed_logins"];
        }
    }

    /**
     * Setter
     * @param array $instances
     */
    public function setInstances($instances) {
        $this->instances = $instances;
    }

    /**
     * Generates a new password recovery token and returns it.
     *
     * The internal format of the token is a Unix timestamp of when it was set (for checking if it's stale), an
     * underscore, and then the token itself.
     *
     * @return string A new password token for embedding in a link and emailing a user.
     */
    public function setPasswordRecoveryToken() {
        $token = md5(uniqid(rand()));
        $dao = DAOFactory::getDAO('OwnerDAO');
        $dao->updatePasswordToken($this->email, $token . '_' . time());
        return $token;
    }

    /**
     * Returns whether a given password recovery token is valid or not.
     *
     * This requires that the token not be stale (older than a day), and that  token itself matches what's in the
     * database.
     *
     * @param string $token The token to validate against the database.
     * @return bool Whether the token is valid or not.
     */
    public function validateRecoveryToken($token) {
        $data = explode('_', $this->password_token);
        return ((time() - $data[1] <= 86400) && ($token == $data[0]));
    }
}
