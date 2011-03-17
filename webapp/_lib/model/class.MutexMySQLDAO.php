<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.MutexMySQLDAO.php
 *
 * Copyright (c) 2009-2011 Guillaume Boudreau
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
 * Mutex Data Access Object implementation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Guillaume Boudreau
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 *
 */
class MutexMySQLDAO extends PDODAO implements MutexDAO {
    /**
     * Try to obtain a named mutex.
     * @param string $name
     * @param integer $timeout Default is 1 second.
     * @return boolean True if the mutex was obtained, false if another thread was already holding this mutex.
     */
    public function getMutex($name, $timeout=1) {
        $lock_name = $this->config->getValue('db_name').'.'.$name;
        $q = "SELECT GET_LOCK(':name', ':timeout') AS result";
        $vars = array(
            ':name' => $lock_name,
            ':timeout' => $timeout
        );
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        return $row['result'] === '1';
    }

    /**
     * Release a named mutex.
     * @param string $name
     * @return boolean True when a lock was released. False otherwise.
     */
    public function releaseMutex($name) {
        $lock_name = $this->config->getValue('db_name').'.'.$name;
        $q = "SELECT RELEASE_LOCK(':name') AS result";
        $vars = array(
            ':name' => $lock_name
        );
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        return $row['result'] === '1';
    }
}