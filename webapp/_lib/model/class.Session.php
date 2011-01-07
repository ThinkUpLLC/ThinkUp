<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Session.php
 *
 * Copyright (c) 2009-2010 Christoffer Viken, Gina Trapani
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
 * Session
 *
 * The object that manages logged-in ThinkUp users' sessions via the web and API calls.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Christoffer Viken, Gina Trapani
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Piyush Mishra <me[at]piyushmishra[dot]com>
 *
 */
class Session {
    /**
     *
     * @var str
     */
    private $salt = "ab194d42da0dff4a5c01ad33cb4f650a7069178b";
    /**
     * Salt used to create API secret tokens.
     * @var str
     */
    private static $api_salt = "a3cb4f27bdda09a01adb19df892c3650a7001b6fb";
    /**
     * Constructs Session
     *
     *  Loads config to check for keep_logged_in and logs in the admin_email automatically
     *  
     */
     public function __construct() {
        $config = Config::getInstance();
        $owner_dao= DAOFactory::getDAO('OwnerDAO');
        $admin_email= $config->getValue('admin_email');
        if ($config->getValue('keep_logged_in') && isset($admin_email) && $owner_dao->doesAdminExist($admin_email)) {
            $owner = $owner_dao->getByEmail($admin_email);
            $this->completeLogin($owner);
            $owner_dao->updateLastLogin($admin_email);
            $owner_dao->resetFailedLogins($admin_email);
            $owner_dao->clearAccountStatus('');
        }
    }
    

    /**
     * @return bool Is user logged into ThinkUp
     */
    public static function isLoggedIn() {
        $config = Config::getInstance();
        if (!isset($_SESSION[$config->getValue('source_root_path')]['user'])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return bool Is user logged into ThinkUp an admin
     */
    public static function isAdmin() {
        $config = Config::getInstance();
        if (isset($_SESSION[$config->getValue('source_root_path')]['user_is_admin'])) {
            return $_SESSION[$config->getValue('source_root_path')]['user_is_admin'];
        } else {
            return false;
        }
    }

    /**
     * @return str Currently logged-in ThinkUp username (email address)
     */
    public static function getLoggedInUser() {
        $config = Config::getInstance();
        if (self::isLoggedIn()) {
            return $_SESSION[$config->getValue('source_root_path')]['user'];
        } else {
            return null;
        }
    }

    /**
     *
     * @param str $pwd Password
     * @return str MD5-hashed password
     */
    private function md5pwd($pwd) {
        return md5($pwd);
    }

    /**
     *
     * @param str $pwd Password
     * @return str SHA1-hashed password
     */
    private function sha1pwd($pwd) {
        return sha1($pwd);
    }
    /**
     *
     * @param str $pwd
     * @return str Salted SHA1 password
     */
    private function saltedsha1($pwd) {
        return sha1(sha1($pwd.$this->salt).$this->salt);
    }

    /**
     * Encrypt password
     * @param str $pwd password
     * @return str Encrypted password
     */
    public function pwdCrypt($pwd) {
        return $this->saltedsha1($pwd);
    }

    /**
     * Check password
     * @param str $pwd Password
     * @param str $result Result
     * @return bool Whether or submitted password matches check
     */
    public function pwdCheck($pwd, $result) {
        if ($this->saltedsha1($pwd) == $result || $this->sha1pwd($pwd) == $result || $this->md5pwd($pwd) == $result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Complete login action
     * @param Owner $owner
     */
    public static function completeLogin($owner) {
        $config = Config::getInstance();
        $_SESSION[$config->getValue('source_root_path')]['user'] = $owner->email;
        $_SESSION[$config->getValue('source_root_path')]['user_is_admin'] = $owner->is_admin;
    }

    /**
     * Log out
     */
    public static function logout() {
        $config = Config::getInstance();
        unset($_SESSION[$config->getValue('source_root_path')]['user']);
        unset($_SESSION[$config->getValue('source_root_path')]['user_is_admin']);
        unset($_SESSION[$config->getValue('source_root_path')]);
    }

    /**
     * Checks the username and API secret from the request, and returns true if they match, and are both valid.
     * @return boolean Are the provided username and API secret parameters valid?
     */
    public static function isAPICallAuthorized($username, $api_secret) {
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $pwd_from_db = $owner_dao->getPass($username);
        if ($pwd_from_db !== false && $api_secret == self::getAPISecretFromPassword($pwd_from_db)) {
            return true;
        }
        return false;
    }

    /**
     * Returns a secret API token that should be used when doing API calls.
     * @param str $pwd_from_db (hash)
     * @return str Secret API token
     */
    public static function getAPISecretFromPassword($pwd_from_db) {
        return sha1(sha1($pwd_from_db.self::$api_salt).self::$api_salt);
    }
}
