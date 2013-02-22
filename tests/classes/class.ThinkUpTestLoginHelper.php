<?php
/**
 *
 * ThinkUp/tests/classes/class.ThinkUpTestLoginHelper.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * ThinkUp Test Login Helper
 *
 * Helper methods for dealing with logging into ThinkUp in tests.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class ThinkUpTestLoginHelper {
    /**
     * For testing purposes only, to populate the pwd field in tu_owners
     * @param str $password
     * @return str Hashed password used beta 14 and prior
     */
    public static function hashPasswordUsingDeprecatedMethod($password) {
        //the static password salt ThinkUp used to use
        return sha1(sha1($password.OwnerMySQLDAO::$default_salt).OwnerMySQLDAO::$default_salt);
    }

    /**
     * For testing purposes only, to populate the pwd field in tu_owners
     * @param str $password
     * @param str $salt
     * @return Hashed password used from beta 15 on
     */
    public static function hashPasswordUsingCurrentMethod($password, $salt) {
        return hash('sha256', $password.$salt);
    }
}