<?php
/*
 *
 * ThinkUp/webapp/_lib/model/class.SessionMySQLDAO.php
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
 * Session Data Access Object
 * The data access object for retrieving and saving sessions in the ThinkUp database.
 * @license http://www.gnu.org/licenses/gpl.html
 * @ Copyright 2014 Terrance Shepherd
 * @author Chris Moyer
 *
 *
 */
class SessionMySQLDAO extends PDODAO implements SessionDAO {
    /**
     * open session handler
     * In this case, does nothing because the database is managed outside this class
     */
    public function open() {
    }

    /**
     * close session handler
     * In this case, does nothing because the database is managed outside this class
     */
    public function close() {
    }

    /**
     * read a session
     * @param str $sid Session ID
     * @return str unserialized data
     */
    public function read($sid) {
        $q = "SELECT data FROM #prefix#sessions WHERE session_id=:sid";
        $vars = array( ':sid'=>$sid );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $data = $this->getDataRowAsArray($ps);

        $ret = '';
        if ($data) {
            $ret = $data['data'];
        }
        return $ret;
    }

    /**
     * write a session
     * @param str $sid Session ID
     * @param str $data Data to save
     * @return bool Always true
     */
    public function write($sid, $data) {
        $q = 'REPLACE INTO #prefix#sessions (session_id, data, updated) VALUES (:sid, :data, NOW())';
        $vars = array( ':sid'=>$sid, ':data' => $data );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return true;
    }

    /**
     * destroy a session
     * @param str $sid Session ID
     * @return bool Always true
     */
    public function destroy($sid) {
        $q = 'DELETE FROM #prefix#sessions WHERE session_id=:sid';
        $vars = array( ':sid'=>$sid );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return true;
    }

    /**
     * garbage collect sessions table
     * @param int $max max age in seconds
     * @return bool Always true
     */
    public function gc($max) {
        $q = 'DELETE FROM #prefix#sessions WHERE updated < DATE_SUB(NOW(), INTERVAL :max SECOND)';
        $vars = array( ':max'=>$max );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return true;
    }
}
