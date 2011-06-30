<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Session.php
 *
 * Copyright (c) 2009-2011 Christoffer Viken, Gina Trapani
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
 * @copyright 2009-2011 Christoffer Viken, Gina Trapani
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
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
     * @return bool Is user logged into ThinkUp
     */
    public static function isLoggedIn() {
        if (!SessionCache::isKeySet('user')) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return bool Is user logged into ThinkUp an admin
     */
    public static function isAdmin() {
        if (SessionCache::isKeySet('user_is_admin')) {
            return SessionCache::get('user_is_admin');
        } else {
            return false;
        }
    }

    /**
     * @return str Currently logged-in ThinkUp username (email address)
     */
    public static function getLoggedInUser() {
        if (self::isLoggedIn()) {
            return SessionCache::get('user');
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
        SessionCache::put('user', $owner->email);
        SessionCache::put('user_is_admin', $owner->is_admin);
        // set a CSRF token
        SessionCache::put('csrf_token', uniqid(mt_rand(), true));
        if (isset($_SESSION["MODE"]) && $_SESSION["MODE"] == 'TESTS') {
            SessionCache::put('csrf_token', 'TEST_CSRF_TOKEN');
        }
    }

    /**
     * Log out
     */
    public static function logout() {
        SessionCache::unsetKey('user');
        SessionCache::unsetKey('user_is_admin');
    }

    /**
     * Returns a CSRF token that should be used whith _GETs and _POSTs requests.
     * @return str CSRF token
     */
    public static function getCSRFToken() {
        if (self::isLoggedIn()) {
            return SessionCache::get('csrf_token');
        } else {
            return null;
        }
    }
}
