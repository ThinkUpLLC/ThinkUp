<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.SessionDAO.php
 *
 * Copyright (c) 2014 Gina Trapani
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
 * Session Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright Gina Trapani
 * @author CHris Moyer
 *
 */
interface SessionDAO {
    /**
     * open session handler
     */
    public function open();

    /**
     * close session handler
     */
    public function close();

    /**
     * read a session
     * @param str $sid Session ID
     * @return str unserialized data
     */
    public function read($sid);

    /**
     * write a session
     * @param str $sid Session ID
     * @param str $data Data to save
     * @return bool Always true
     */
    public function write($sid, $data);

    /**
     * destroy a session
     * @param str $sid Session ID
     * @return bool Always true
     */
    public function destroy($sid);

    /**
     * garbage collect sessions table
     * @param int $max max age in seconds
     * @return bool Always true
     */
    public function gc($max);
}
