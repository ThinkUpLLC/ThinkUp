<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.PluginDAO.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie, Gina Trapani
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
 * Plugin Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie, Gina Trapani
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface PluginDAO {

    /**
     * Get all plugins
     * @param bool Only get active plugins
     * @return array A list of Plugin objects
     */
    public function getAllPlugins($isactive = false);

    /**
     * Get all active plugins
     * @return array A list of active Plugin objects
     */
    public function getActivePlugins();

    /**
     * Determine if a plugin is active
     * @param int A plugin ID
     * @return bool
     */
    public function isPluginActive($id);

    /**
     * Inserts a plugin record
     * @throws BadArgumentException If param is not a Plugin object
     * @param Plugin A plugin data object
     * @return int|bool Insert ID of new row, or false if none
     */
    public function insertPlugin($plugin);

    /**
     * Updates a plugin record
     * @throws BadArgumentException If param is not a Plugin object
     * @return bool Successfully updated
     */
    public function updatePlugin($plugin);

    /**
     * Gets a plugin record by folder name
     * @param str A folder name
     * @return int A plugin id
     */
    public function getPluginId($folder_name);

    /**
     * Gets a plugin folder name by id
     * @param int A plugin id
     * @return str A plugin folder name
     */
    public function getPluginFolder($plugin_id);

    /**
     * Set a plugin's active flag
     * @param int Plugin ID
     * @param bool Active flag, 1 if activating, 0 if deactivating
     * @return int number of updated rows
     */
    public function setActive($plugin_id, $is_active);

    /**
     * Detect what plugins exist in the filesystem; parse their header comments for plugin metadata
     * @return array Installed plugins
     */
    public function getInstalledPlugins();

    /**
     * Validate a plugin id
     * @param int A plugin id
     * @return bool If valid
     */
    public function isValidPluginId($plugin_id);
}
