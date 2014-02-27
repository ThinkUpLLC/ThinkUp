<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.SessionDAO.php
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
 * Session Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer
 *
 */
interface SessionDAO {
    /**
     * Open session handler.
     * @return void
     */
    public function open();
    /**
     * Close session handler.
     * @return void
     */
    public function close();
    /**
     * Read session data.
     * @param str $session_id Session ID
     * @return mixed unserialized data
     */
    public function read($session_id);
    /**
     * Write data to session.
     * @param str $session_id Session ID
     * @param mixed $data Data to save
     * @return bool Always true
     */
    public function write($session_id, $data);
    /**
     * Destroy a session.
     * @param str $session_id Session ID
     * @return bool Always true
     */
    public function destroy($session_id);
    /**
     * Garbage collect sessions table.
     * @param int $max_age Max age in seconds
     * @return bool Always true
     */
    public function gc($max_age);
}
