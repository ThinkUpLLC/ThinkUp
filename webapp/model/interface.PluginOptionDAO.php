<?php
/**
 * Plugin Data Access Object interface
 *
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
interface PluginOptionDAO {

    /**
     * Add/Insert a plugin option by plugin id
     * @param int A plugin id
     * @param str A plugin option name
     * @param int A plugin option value
     * @return bool If successful or not
     */
    public function insertOption($option_id, $name, $value);

    /**
     * Updates a plugin option by id
     * @param int An id
     * @param str A plugin option name
     * @param int A plugin option value
     * @return int insert id
     */
    public function updateOption($id, $name, $value);

    /**
     * Gets plugin options
     * @param int A plugin id (optional). If not defined returns all options for all plugins
     * @return array A list of PluginOption objects
     */
    public function getOptions($plugin_id = null);
        
    /**
     * Delete a plugin option by id
     * @param int A plugin option id
     * @return bool If successful or not
     */
    public function deleteOption($option_id);

    /**
     * Validate a plugin id
     * @param int A plugin id
     * @return bool If =valid
     */
    public function validatePluginId($option_id);
}
