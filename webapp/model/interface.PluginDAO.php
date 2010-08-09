<?php
/**
 * Plugin Data Access Object interface
 *
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
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
     * @return bool Whether or not it was insertedss
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
     * @return array A hash of plugin data
     */
    public function getPluginId($folder_name);

    /**
     * Set a plugin's active flag
     * @param int Plugin ID
     * @param bool Active flag, 1 if activating, 0 if deactivating
     * @return int number of updated rows
     */
    public function setActive($plugin_id, $is_active);

    /**
     * Detect what plugins exist in the filesystem; parse their header comments for plugin metadata
     * @param str Plugin path
     * @return array Installed plugins
     */
    public function getInstalledPlugins($plugin_path);
}

