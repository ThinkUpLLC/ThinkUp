<?php
/**
 * Installer Data Access Object interface
 *
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 *
 */
interface InstallerDAO {
    /**
     * Get array of tables from current connected database
     *
     * @return array Table name strings
     */
    public function getTables();

    /**
     * Check table
     *
     * @param str $table_name
     * @return array If table exists and okay result will be array('Msg_text' => 'OK')
     */
    public function checkTable($table_name);

    /**
     * Repair table
     *
     * @param str $table_name Name of table to repair
     * @return array Row that consists of key Message_text.
     * If table exists and okay it must be array('Msg_text' => 'OK')
     */
    public function repairTable($table_name);

    /**
     * Describe table
     *
     * @param str $table_name
     * @return array table descriptions that consist of following case-sensitive properties:
     *             - Field => name of field
     *             - Type => type of field
     *             - Null => is type allowed to be null
     *             - Default => Default value for field
     *             - Extra => such as auto_increment
     */
    public function describeTable($table_name);

    /**
     * Get index from particular table
     *
     * @param str $table_name with prefix
     * @return array Tables indices of $table_name
     */

    public function showIndex($table_name);

    /**
     * Diff the current database table structure with desired table structure.
     * This is a modified version of WordPress' dbDelta function
     * More info: http://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table
     *
     * @param str $desired_structure_sql_string
     * @param array $existing_tables
     * @return array Array of 'queries', and 'for_update', what SQL will update the current structure to desired state
     */
    public function diffDataStructure($queries = '', $tables = array());
}