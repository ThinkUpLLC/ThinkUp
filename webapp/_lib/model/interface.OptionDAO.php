<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.OptionDAO.php
 *
 * Copyright (c) 2009-2011 Mark Wilkie
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
 * Option Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Mark Wilkie, Gina Trapani
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
interface OptionDAO {

    /**
     * Application-wide option namespace
     * @var str
     */
    const APP_OPTIONS = 'application_options';
    /**
     * Plugin-specific option namespace
     * @var str
     */
    const PLUGIN_OPTIONS = 'plugin_options';

    /**
     * Add/Insert a plugin option by nanmespace and name
     * @param $str Namespace
     * @param $str A name
     * @param $str A option value
     * @throws DuplicateOptionException
     * @return $int Inserted option ID
     */
    public function insertOption($namespace, $name, $value);

    /**
     * Update a plugin option by id
     * @param int A option id
     * @param str A option value
     * @param str An optional name value
     * @return int Number of records updated
     */
    public function updateOption($id, $value, $name = null);

    /**
     * Get a plugin option
     * @param str namespace
     * @param str A key/name
     * @param bool $cached Whether or not to retrieved cached option, default to false
     * @return Option An Option object
     */
    public function getOptionByName($namespace, $name);

    /**
     * Get a plugin option by id
     * @param int Option id
     * @param bool $cached Whether or not to retrieved cached option, default to false
     * @return Option An Option object
     */
    public function getOption($option_id);

    /**
     * Delete a option by id
     * @param int A option id
     * @return bool If successful or not
     */
    public function deleteOption($option_id);

    /**
     * Delete a option by namespace and name
     * @param str A namespace
     * @param str A names
     * @return bool If successful or not
     */
    public function deleteOptionByName($namespace, $name);

    /**
     * Get a hash of Option objects keyed on option name
     * @param str namespace
     * @param bool $cached Whether or not to retrieved cached options, (optional) defaults to false
     * @return array A hash table of Options with option_name as the key
     */
    public function getOptions($namespace, $cached = false);

    /**
     * Get a option value by namespace and name
     * @param str namespace
     * @param str name
     * @param bool Return a cached version if in the cache, (optional) defaults to false.
     * @return str Option value
     */
    public function getOptionValue($namespace, $name, $cached = false);

    /**
     * Check if the options table exists
     * @return bool Whether or not an options table exists
     */
    public function isOptionsTable();
    
    /**
     * Does ? (Updates a option, maybe)
     * @param $namespace
     * @param $name
     * @param $value 
     * @return int Update Count
     */
    public function updateOptionByName($namespace, $name, $value);
    
    /**
     * Gets option data from session using namespace as a key
     * @param $namespace
     * @retrun $array Hash of option data
     */
    public function getSessionData($namespace);
    
    /**
     * Sets option data in the session using namespace as a key
     * @param $namespace
     * @param array Hash of option data
     * @retun $array Hash of option data
     */
    public function setSessionData($namespace, $data);
    
    /**
     * Clears session data by namespace
     * @param $namespace
     */
    public function clearSessionData($namespace);
}
