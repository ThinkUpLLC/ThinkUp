<?php
/**
 * Plugin Data Access Object interface
 *
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
     * @param str A plugin folder (optional). If not defined returns all options for all plugins
     * @param bool $cached Whether or not to retrieved cached options, default to false
     * @return array A list of PluginOption objects
     */
    public function getOptions($plugin_folder = null, $cached = false);

    /**
     * Delete a plugin option by id
     * @param int A plugin option id
     * @return bool If successful or not
     */
    public function deleteOption($option_id);

    /**
     * Get a hash of Option objects keyed on option name
     * @param str Plugin folder name
     * @param bool $cached Whether or not to retrieved cached options, default to false
     * @return array A hash table of Options with option_name as the key
     */
    public function getOptionsHash($plugin_folder, $cached = false);
}
