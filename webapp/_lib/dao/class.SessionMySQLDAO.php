<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.SessionMySQLDAO.php
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
 * Session Data Access Object
 * The data access object for retrieving and saving sessions in the ThinkUp database.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer
 *
 */
class SessionMySQLDAO extends PDODAO implements SessionDAO {
    /**
     * Open session handler.
     * In this case, does nothing because the database is managed outside this class.
     */
    public function open() {
    }

    /**
     * Close session handler.
     * In this case, does nothing because the database is managed outside this class.
     */
    public function close() {
    }

    public function read($session_id) {
        $q = "SELECT data FROM #prefix#sessions WHERE session_id=:session_id";
        $vars = array( ':session_id'=>$session_id );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $data = $this->getDataRowAsArray($ps);

        if (isset($data['data'])) {
            return $data['data'];
        } else {
            return '';
        }
    }

    public function write($session_id, $data) {
        $q = "REPLACE INTO #prefix#sessions (session_id, data, updated) VALUES (:session_id, :data, NOW())";
        $vars = array( ':session_id'=>$session_id, ':data' => $data );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return true;
    }

    public function destroy($session_id) {
        $q = "DELETE FROM #prefix#sessions WHERE session_id=:session_id";
        $vars = array( ':session_id'=>$session_id );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return true;
    }

    public function gc($max) {
        $q = "DELETE FROM #prefix#sessions WHERE updated < DATE_SUB(NOW(), INTERVAL :max SECOND)";
        $vars = array( ':max'=>$max );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return true;
    }
}
