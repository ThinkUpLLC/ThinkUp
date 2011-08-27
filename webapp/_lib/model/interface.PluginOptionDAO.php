<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.PluginOptionDAO.php
 *
 * Copyright (c) 2009-2011 Mark Wilkie, Gina Trapani
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
 * Plugin Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Mark Wilkie, Gina Trapani
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
interface PluginOptionDAO {

    /**
     * Add/Insert a plugin option by plugin id
     * @param int A plugin id
     * @param str A plugin option name
     * @param mixed A plugin option value
     * @return int Inserted plugin option ID
     */
    public function insertOption($plugin_id, $name, $value);

    /**
     * Update a plugin option by id
     * @param int A plugin option id
     * @param str A plugin option name
     * @param int A plugin option value
     * @return bool If successful or not
     */
    public function updateOption($id, $name, $value);

    /**
     * Get plugin options
     * @param str A plugin folder
     * @param bool $cached Whether or not to retrieved cached options, default to false
     * @return array A list of PluginOption objects
     */
    public function getOptions($plugin_folder, $cached = false);

    /**
     * Delete a plugin option by id
     * @param int A plugin option id
     * @return bool If successful or not
     */
    public function deleteOption($id);

    /**
     * Get a hash of Option objects keyed on option name
     * @param str Plugin folder name
     * @param bool $cached Whether or not to retrieved cached options, default to false
     * @return array A hash table of Options with option_name as the key
     */
    public function getOptionsHash($plugin_folder, $cached = false);
}
