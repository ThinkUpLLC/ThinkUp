<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.MutexMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Guillaume Boudreau, Gina Trapani
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
 * Mutex Data Access Object implementation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Guillaume Boudreau, Gina Trapani
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class MutexMySQLDAO extends PDODAO implements MutexDAO {
    /**
     * NOTE: PDO does not seem to bind params in MySQL functions, so we escape parameters and concat them manually.
     */
    public function getMutex($name, $timeout=1) {
        $lock_name = $this->config->getValue('db_name').'.'.$name;
        /*
         $q = "SELECT GET_LOCK(':name', ':timeout') AS result";
         $vars = array(
         ':name' => $lock_name,
         ':timeout' => $timeout
         );
         $ps = $this->execute($q, $vars);
         */
        $q = "SELECT GET_LOCK('".$lock_name."', ".$timeout. ") AS result";
        $ps = $this->execute($q);
        $row = $this->getDataRowAsArray($ps);
        return $row['result'] === '1';
    }

    /**
     * NOTE: PDO does not seem to bind params in MySQL functions, so we escape parameters and concat them manually.
     */
    public function releaseMutex($name) {
        $lock_name = $this->config->getValue('db_name').'.'.$name;
        /*
         $q = "SELECT RELEASE_LOCK(':name') AS result";
         $vars = array(
         ':name' => $lock_name
         );
         $ps = $this->execute($q, $vars);
         */
        $q = "SELECT RELEASE_LOCK('".$lock_name."') AS result";
        $ps = $this->execute($q);
        $row = $this->getDataRowAsArray($ps);
        return $row['result'] === '1';
    }

    /**
     * NOTE: PDO does not seem to bind params in MySQL functions, so we escape parameters and concat them manually.
     */
    public function isMutexFree($name) {
        $lock_name = $this->config->getValue('db_name').'.'.$name;
        $q = "SELECT IS_FREE_LOCK('".$lock_name."') AS result";
        $ps = $this->execute($q);
        $row = $this->getDataRowAsArray($ps);
        return $row['result'] === '1';
    }

    /**
     * NOTE: PDO does not seem to bind params in MySQL functions, so we escape parameters and concat them manually.
     */
    public function isMutexUsed($name) {
        $lock_name = $this->config->getValue('db_name').'.'.$name;
        $q = "SELECT IS_USED_LOCK('".$lock_name."') AS result";
        $ps = $this->execute($q);
        $row = $this->getDataRowAsArray($ps);
        return $row['result'] != null;
    }
}