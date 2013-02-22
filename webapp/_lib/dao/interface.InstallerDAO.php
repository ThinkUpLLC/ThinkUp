<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.InstallerDAO.php
 *
 * Copyright (c) 2009-2013 Dwi Widiastuti, Gina Trapani
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
 * Installer Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Dwi Widiastuti, Gina Trapani
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
     * Attempts to create the database specified in the install process. If it
     * already exists then nothing happens. If it does not exist, this
     * function tries to create it.
     *
     * @param array $cfg_vals The array of config values supplied to the
     * installer.
     * @return boolean True on success, false on failure.
     */
    public function createInstallDatabase($cfg_vals = null);

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
     * Run a sql migration command
     *
     * @param str $sql SQL command to execute
     */
    public function runMigrationSQL($sql);

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

    /**
     * Temporary method to determine if database is 64-bit post ID ready
     * This method will be deleted for ThinkUp's 1.0 release.
     */
    public function needsSnowflakeUpgrade();
}