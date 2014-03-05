<?php
/**
 *
 * ThinkUp/webapp/_lib/class.Session.php
 *
 * Copyright (c) 2009-2013 Christoffer Viken, Gina Trapani
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
 * Session
 *
 * The object that manages logged-in ThinkUp users' sessions via the web and API calls.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Christoffer Viken, Gina Trapani
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Session {
    /**
     * Name for Long-Session Cookie
     * @var str
     */
    const COOKIENAME = 'thinkup_session';

    /**
     * Check if we have an active session.
     * If not, check if we have a long term sessions cookie and activate a session.
     * @return bool Is user logged into ThinkUp
     */
    public static function isLoggedIn() {
        if (SessionCache::isKeySet('user')) {
            return true;
        }
        if (!empty($_COOKIE[self::COOKIENAME])) {
            $cookie_dao = DAOFactory::getDAO('CookieDAO');
            $email = $cookie_dao->getEmailByCookie($_COOKIE[self::COOKIENAME]);
            if ($email) {
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $owner = $owner_dao->getByEmail($email);
                if ($owner) {
                    self::completeLogin($owner);
                    return true;
                }
            }
        }
        return false;
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
     * Complete login action
     * @param Owner $owner
     */
    public static function completeLogin($owner) {
        SessionCache::put('user', $owner->email);
        SessionCache::put('user_is_admin', $owner->is_admin);
        // set a CSRF token
        SessionCache::put('csrf_token', uniqid(mt_rand(), true));
        if (Utils::isTest()) {
            SessionCache::put('csrf_token', 'TEST_CSRF_TOKEN');
        }

        // check for and validate an existing long-term cookie before creating one
        $cookie_dao = DAOFactory::getDAO('CookieDAO');
        $set_long_term = true;

        if (!empty($_COOKIE[self::COOKIENAME])) {
            $email = $cookie_dao->getEmailByCookie($_COOKIE[self::COOKIENAME]);
            $set_long_term = $email != $owner->email;
        }

        if ($set_long_term) {
            $cookie = $cookie_dao->generateForEmail($owner->email);
            if (!headers_sent()) {
                setcookie(self::COOKIENAME, $cookie, time()+(60*60*24*365*10), '/', self::getCookieDomain());
            }
        }
    }

    /**
     * Log out and ensure that long-term cookie is killed since the user explicitly logged out.
     */
    public static function logout() {
        SessionCache::unsetKey('user');
        SessionCache::unsetKey('user_is_admin');

        if (!empty($_COOKIE[self::COOKIENAME])) {
            if (!headers_sent()) {
                setcookie(self::COOKIENAME, '', time() - 60*60*24, '/', self::getCookieDomain());
            }
            $cookie_dao = DAOFactory::getDAO('CookieDAO');
            $cookie_dao->deleteByCookie($_COOKIE[self::COOKIENAME]);
        }

    }

    /**
     * Generate a domain for setting cookies
     * @return str domain to use
     */
    public static function getCookieDomain() {
        if (empty($_SERVER['HTTP_HOST'])) {
            return false;
        }
        $parts = explode('.', $_SERVER['HTTP_HOST']);
        if (count($parts) == 1) {
            return $parts[0];
        }

        return '.'.$parts[count($parts)-2].'.'.$parts[count($parts)-1];
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
