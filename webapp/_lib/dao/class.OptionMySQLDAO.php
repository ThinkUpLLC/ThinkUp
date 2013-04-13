<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.OptionMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie
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
 * Option Data Access Object
 *
 * The data access object for retrieving and saving generic ThinkUp options and their values.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie, Gina Trapani
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class OptionMySQLDAO extends PDODAO implements OptionDAO {

    public function insertOption($namespace, $name, $value) {
        $option = $this->getOptionByName($namespace, $name);
        if ($option) {
            throw new DuplicateOptionException("An option with the namespace $namespace and name $name exists");
        }
        $q = 'INSERT INTO #prefix#options
                (namespace, option_name, option_value, created, last_updated)
            VALUES
                (:namespace, :option_name, :option_value, now(), now())';
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q,
        array(':namespace' => $namespace, ':option_name' => $name, ':option_value' => $value) );
        $this->clearSessionData($namespace);
        return $this->getInsertId($stmt);
    }

    public function updateOption($id, $value, $name = null) {
        $option = $this->getOption($id);
        if ($option) {
            $q = 'UPDATE #prefix#options set option_value = :option_value, last_updated = now() ';
            if ($name) {
                $q .= ', option_name  = :option_name';
            }
            $q .= ' WHERE option_id = :option_id';
            $data = array(':option_id' => $id, ':option_value' => $value);
            if ($name) {
                $data[':option_name'] = $name;
            }
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $stmt = $this->execute($q, $data);
            $this->clearSessionData($option->namespace);
            return $this->getUpdateCount($stmt);
        } else {
            return 0;
        }
    }

    public function updateOptionByName($namespace, $name, $value) {
        $q = 'UPDATE #prefix#options set option_value = :option_value, last_updated = now()
            WHERE namespace = :namespace AND option_name = :option_name';
        $binds = array(':namespace' => $namespace, ':option_name' => $name, 'option_value' => $value);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $binds);
        $this->clearSessionData($namespace);
        return $this->getUpdateCount($stmt);
    }

    public function getOptionByName($namespace, $name){
        $q = 'SELECT option_id, namespace, option_name, option_value FROM #prefix#options
            WHERE namespace = :namespace AND option_name = :option_name';
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, array(':namespace' => $namespace, ':option_name' => $name));
        $option = $this->getDataRowAsObject($stmt, 'Option');
        return $option;
    }

    public function getOption($option_id){
        $q = 'SELECT option_id, namespace, option_name, option_value FROM #prefix#options
            WHERE option_id = :option_id';
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, array(':option_id' => $option_id));
        $option = $this->getDataRowAsObject($stmt, 'Option');
        return $option;
    }

    public function deleteOption($option_id){
        $option = $this->getOption($option_id);
        if ($option) {
            $q = 'DELETE FROM #prefix#options WHERE option_id = :option_id';
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $stmt = $this->execute($q, array(':option_id' => $option_id));
            $this->clearSessionData($option->namespace);
            return $this->getUpdateCount($stmt);
            $this->clearSessionData($namespace);
        } else {
            return 0;
        }
    }

    public function deleteOptionByName($namespace, $name){
        $q = 'DELETE FROM #prefix#options WHERE namespace = :namespace AND option_name = :name';
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, array(':namespace' => $namespace, ':name' => $name));
        $this->clearSessionData($namespace);
        return $this->getUpdateCount($stmt);
    }

    public function getOptions($namespace, $cached = false) {
        $data = null;
        if ($cached) {
            $data = $this->getSessionData($namespace);
        }
        if (is_null($data)) {
            $q = 'SELECT option_id, namespace,  option_name, option_value
                    FROM #prefix#options 
                    WHERE namespace = :namespace';
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $stmt = $this->execute($q, array(':namespace' => $namespace));
            $res = $this->getDataRowsAsArrays($stmt);
            if (count($res ) == 0) {
                $data = null;
            } else {
                $data = array();
                foreach($res as $option_array) {
                    $option = new Option($option_array);
                    $data[$option->option_name] = $option;
                }
            }
        }
        if ($cached) {
            $this->setSessionData($namespace, $data);
        }
        return $data;
    }

    public function getOptionValue($namespace, $name, $cached = false) {
        $options = $this->getOptions($namespace, $cached);
        if ($options && isset($options[$name])) {
            return $options[$name]->option_value;
        } else {
            return null;
        }

    }

    /**
     * Gets option data from session using namespace as a key
     * @param $namespace
     * @retrun $array Hash of option data
     */
    public function getSessionData($namespace) {
        $key = 'options_data:' . $namespace;
        if (SessionCache::isKeySet($key) ) {
            return SessionCache::get($key);
        } else {
            return null;
        }
    }

    /**
     * Sets option data in the session using namespace as a key
     * @param $namespace
     * @param array Hash of option data
     * @retrun $array Hash of option data
     */
    public function setSessionData($namespace, $data) {
        $key = 'options_data:' . $namespace;
        SessionCache::put($key, $data);
    }

    /**
     * Clears session data by namespace
     * @param $namespace
     */
    public function clearSessionData($namespace) {
        $key = 'options_data:' . $namespace;
        if ( SessionCache::isKeySet($key)) {
            SessionCache::unsetKey($key);
        }
    }

    public function isOptionsTable() {
        $q = "show tables like '#prefix#options'";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q);
        $data = $this->getDataRowAsArray($stmt);
        if ($data) {
            return true;
        } else {
            return false;
        }
    }
}