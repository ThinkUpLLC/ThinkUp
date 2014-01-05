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
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var str User full name.
     */
    var $full_name;
    /**
     * @var str User email.
     */
    var $email;
    /**
     * @var date Date user registered for an account.
     */
    var $joined;
    /**
     * @var bool If user is activated, 1 for true, 0 for false.
     */
    var $is_activated = false;
    /**
     * @var bool If user is an admin, 1 for true, 0 for false.
     */
    var $is_admin = false;
    /**
     * @var date Last time user logged into ThinkUp.
     */
    var $last_login;
    /**
     * @var int Current number of failed login attempts.
     */
    var $failed_logins;
    /**
     * @var str Description of account status, i.e., "Inactive due to excessive failed login attempts".
     */
    var $account_status;
    /**
     * @var str Key to authorize API calls.
     */
    var $api_key;
    /**
     * @var str Optional non-user-facing API key.
     */
    var $api_key_private;
    /**
     * @var str How often to send email notifications (daily, weekly, both, never).
     */
    var $email_notification_frequency;
    /**
     * @var str Owner timezone.
     */
    var $timezone;
    /**
     * @var str ThinkUp.com membership level.
     */
    var $membership_level;
    /**
     * @var arr Non-persistent, used for UI, array of instances associated with owner.
     */
    var $instances = null;
    /**
     * Valid values for membership level.
     * @var array
     */
    static $valid_membership_values = array('Early Bird', 'Member', 'Late Bird', 'Pro', 'Exec');

    public function __construct($row = false) {
        if ($row) {
            $this->id = $row['id'];
            $this->full_name = $row['full_name'];
            $this->email = $row['email'];
            $this->joined = $row['joined'];
            $this->is_activated = PDODAO::convertDBToBool($row['is_activated']);
            $this->is_admin = PDODAO::convertDBToBool($row['is_admin']);
            $this->last_login = $row['last_login'];
            $this->failed_logins = $row['failed_logins'];
            $this->account_status = $row['account_status'];
            $this->api_key = $row['api_key'];
            $this->api_key_private = $row['api_key_private'];
            $this->email_notification_frequency = $row['email_notification_frequency'];
            $this->timezone = $row['timezone'];
            $this->membership_level = $row['membership_level'];
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

    /**
     * Check if the owner is a ThinkUp.com member of any level.
     * @return bool Whether or not the owner is a member
     */
    public function isMemberAtAnyLevel() {
        return (in_array($this->membership_level, self::$valid_membership_values));
    }

    /**
     * Check if the owner is Member level, i.e., Early Bird, Member, or Late Bird.
     * @return bool Whether or not the owner is a member at member level
     */
    public function isMemberLevel() {
        return ($this->membership_level == 'Member' || $this->membership_level == 'Early Bird'
        || $this->membership_level == 'Late Bird');
    }

    /**
     * Check if the owner is Pro member level.
     * @return bool Whether or not the owner is a Pro level
     */
    public function isProLevel() {
        return ($this->membership_level === 'Pro');
    }
}
