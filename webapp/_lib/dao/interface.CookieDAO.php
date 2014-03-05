<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.CookieDAO.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * Cookie Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer
 *
 */
interface CookieDAO {
    /**
     * Generate a Cookie for a give Owner Email
     * @param str $email Email for which to generate cookie
     * @return str Cookie generated
     */
    public function generateForEmail($email);
    /**
     * Delete all cookies for a given email
     * @param str $email Who are we deleting the cookies for?
     * @return bool Did we delete them?
     */
    public function deleteByEmail($email);
    /**
     * Delete a given cookie
     * @param str $cookie What cookie record to delete
     * @return bool Did we delete it?
     */
    public function deleteByCookie($cookie);
    /**
     * Get email associated with a cookie
     * @param str $cookie Cookie we are attempting to find.
     * @return str Associated email or null
     */
    public function getEmailByCookie($cookie);
}
