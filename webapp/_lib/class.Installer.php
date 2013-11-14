<?php
/**
 *
 * ThinkUp/webapp/_lib/class.Installer.php
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
 * Installer
 * A singleton class that doess the heavy lifting of installing ThinkUp.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Dwi Widiastuti, Gina Trapani
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class Installer {
    /**
     * Singleton instance of Installer
     *
     * @var Installer
     * @TODO Make sure the instance records unique id (something like IP or mac address) which identifies executor
     */
    private static $instance = null;

    /**
     * Stores error messages.
     *
     * @var array
     */
    private static $error_messages = array();

    const ERROR_FILE_NOT_FOUND = 1;
    const ERROR_CLASS_NOT_FOUND = 2;
    const ERROR_DB_CONNECT = 3;
    const ERROR_DB_SELECT = 4;
    const ERROR_DB_TABLES_EXIST = 5;
    const ERROR_SITE_NAME = 6;
    const ERROR_SITE_EMAIL = 7;
    const ERROR_CONFIG_FILE_MISSING = 8;
    const ERROR_CONFIG_SAMPLE_MISSING = 9;
    const ERROR_CONFIG_SOURCE_ROOT_PATH = 10;
    const ERROR_CONFIG_LOG_LOCATION = 12;
    const ERROR_TYPE_MISMATCH = 13;
    const ERROR_INSTALL_PATH_EXISTS = 14;
    const ERROR_INSTALL_NOT_COMPLETE = 15;
    const ERROR_INSTALL_COMPLETE = 16;
    const ERROR_REPAIR_CONFIG = 17;
    const ERROR_REQUIREMENTS = 18;

    /**
     * Stores current version of ThinkUp
     *
     * @var str
     */
    private static $current_version;

    /**
     * Stores required version of each apps
     *
     * @var str
     */
    private static $required_version;

    /**
     * List of ThinkUp tables.
     * If there are new tables added, make sure this property also updated
     *
     * @var array
     */
    public static $tables;

    /**
     * Result from SHOW TABLES
     *
     * @TODO: Why is this public? Shouldn't be public, security risk
     * @var array
     */
    public static $show_tables;

    /**
     * PDO Instance
     *
     * @var InstallerDAO
     */
    public static $installer_dao;


    public function __construct() {
        self::$tables = $this->getTablesToInstall();
    }

    /**
     * Get Installer instance
     *
     * @return Installer
     */
    public static function getInstance() {
        if ( self::$instance == null ) {
            self::$instance = new Installer();

            // use lazy loading
            if ( !class_exists('Loader', FALSE) ) {
                require_once THINKUP_WEBAPP_PATH . '_lib/class.Loader.php';
            }
            Loader::register();

            // get required version of php and mysql
            // and set current version
            require (THINKUP_WEBAPP_PATH . 'install/version.php');

            self::$required_version = array(
                'php' => $THINKUP_VERSION_REQUIRED['php'],
                'mysql' => $THINKUP_VERSION_REQUIRED['mysql']
            );
            self::$current_version = $THINKUP_VERSION;
        }

        return self::$instance;
    }

    /**
     * Check PHP version
     *
     * @param str $ver can be used for testing for failing
     * @return bool Whether or not required version of PHP is present
     */
    public function checkVersion($ver = '') {
        // when testing
        if ( defined('TESTS_RUNNING') && TESTS_RUNNING && !empty($ver) ) {
            $version = $ver;
        } else {
            $version = PHP_VERSION;
        }
        return version_compare( $version, self::$required_version['php'], '>=' );
    }

    /**
     * Get current version
     *
     * @return int Current PHP version
     */
    public function getCurrentVersion() {
        return self::$current_version;
    }

    /**
     * Get required version
     *
     * @return int Required PHP version
     */
    public function getRequiredVersion() {
        return self::$required_version;
    }

    /**
     * Check GD, cURL, PDO, and JSON extensions are loaded
     *
     * @param array $libs For use in tests
     * @return array
     */
    public function checkDependency($libs = array()) {
        $ret = array('curl'=>false, 'gd'=>false, 'pdo'=>false, 'pdo_mysql'=>false, 'json'=>false, 'hash'=>false,
        'ZipArchive'=>false);

        // check curl
        if ( self::curlDependenciesMet() ) {
            $ret['curl'] = true;
        }
        // check GD
        if ( extension_loaded('gd') && function_exists('gd_info') ) {
            $ret['gd'] = true;
        }
        // check PDO
        if ( extension_loaded('pdo') ) {
            $ret['pdo'] = true;
        }
        // check PDO MySQL
        if ( extension_loaded('pdo_mysql') ) {
            $ret['pdo_mysql'] = true;
        }
        // check JSON
        if ( extension_loaded('json') && function_exists('json_decode') && function_exists('json_encode') ) {
            $ret['json'] = true;
        }
        // check php5-hash
        if ( extension_loaded('hash') ) {
            $ret['hash'] = true;
        }
        // check ZipArchive
        if ( class_exists('ZipArchive')) {
            $ret['ZipArchive'] = true;
        }
        // when testing
        if ( defined('TESTS_RUNNING') && TESTS_RUNNING && !empty($libs) ) {
            $ret = $libs;
        }
        return $ret;
    }

    /**
     * Confirm that the cURL extension is loaded and configured as needed
     *
     * @return bool
     */
    private function curlDependenciesMet() {
        if ( !extension_loaded('curl') || !function_exists('curl_exec') || !function_exists('curl_version') ) {
            return false;
        }
        $curl_ver = curl_version();
        if ( !in_array( 'https', $curl_ver['protocols'] ) ) {
            return false;
        }
        return true;
    }

    /**
     * Check if log and template directories are writable
     *
     * @param array $perms can be used for testing for failing
     * @return array 'compiled_view'=>true/false, 'cache'=>true/false
     */
    public function checkPermission($perms = array()) {
        $data_dir = THINKUP_WEBAPP_PATH . 'data/';
        $cache_dir = $data_dir."cache/";
        $ret = array('data_dir' => false, 'cache' => false);
        if ( is_writable($data_dir) ) {
            $ret['data_dir'] = true;
            if (!file_exists($cache_dir)) {
                $ret['cache'] = mkdir($cache_dir, 0777);
            }
            $ret['cache'] = is_writable($cache_dir);
        }

        // when testing
        if ( defined('TESTS_RUNNING') && TESTS_RUNNING && !empty($perms) ) {
            $ret = $perms;
        }
        return $ret;
    }

    /**
     * Check if session directory is writable
     *
     * @return bool
     */
    public function isSessionDirectoryWritable() {
        return (is_writable(session_save_path()) );
    }

    /**
     * Check if Thinkup's paths exists.
     *
     * @throws InstallerException
     * @param array $config
     * @return bool
     */
    public function checkPath($config) {
        // check if $THINKUP_CFG related to path exists
        if ( !is_dir($config['source_root_path']) ) {
            throw new InstallerException("ThinkUp's source root directory is not found.",
            self::ERROR_CONFIG_SOURCE_ROOT_PATH);
        }
        return true;
    }

    /**
     * Check all requirements on step 1
     * Check PHP version, cURL, GD, JSON, and path permission
     *
     * @param array $pass can be used for testing for failing
     * @return bool
     */
    public function checkStep1($pass = true) {
        $version_compat = $this->checkVersion();

        $lib_depends = $this->checkDependency();
        $lib_depends_ret = true;
        foreach ($lib_depends as $lib) {
            $lib_depends_ret = $lib_depends_ret && $lib;
        }

        $writable_permission = $this->checkPermission();
        $writable_permission_ret = true;
        foreach ($writable_permission as $permission) {
            $writable_permission_ret = $writable_permission_ret && $permission;
        }

        $writable_session_permission = $this->isSessionDirectoryWritable();

        // when testing
        if ( defined('TESTS_RUNNING') && TESTS_RUNNING && !empty($pass) ) {
            $ret = $pass;
        } else {
            $ret = ($version_compat && $lib_depends_ret && $writable_permission_ret && $writable_session_permission);
        }
        return $ret;
    }

    /**
     * Set Installer DAO
     * @throws InstallerException
     * @param array $config Database config
     * @return InstallerMySQLDAO
     */
    public function setDb($config) {
        try {
            self::$installer_dao = DAOFactory::getDAO('InstallerDAO', $config);
        } catch (PDOException $e) {
            throw new InstallerException('Failed establishing database connection. '. $e->getMessage() .'',
            self::ERROR_DB_CONNECT);
        }
        return self::$installer_dao;
    }

    /**
     * Get SHOW TABLES at current $db
     *
     * @param array $config
     * @return array tables
     */
    public function showTables($config = null) {
        if ( is_array(self::$show_tables) && !empty(self::$show_tables) ) {
            return self::$show_tables;
        }

        if ( !self::$installer_dao ) {
            self::setDb($config);
        }
        self::$show_tables = self::$installer_dao->getTables();

        return self::$show_tables;
    }

    /**
     * Check database
     *
     * @param array $config Database credentials
     * @return bool
     */
    public function checkDb($config) {
        try {
            self::setDb($config);
            return true;
        } catch (InstallerException $e) {
            return $e;
        }
    }

    /**
     * Check if ThinkUp tables exist
     * See also self::doThinkUpTablesExist($config).
     * Unlike doThinkUpTablesExist, this method throws installer exceptions.
     * This method should be called during installation steps.
     *
     * @param array $config
     * @return bool return true when ThinkUp tables don't exist, throw error when table exists
     */
    public function checkTable($config) {
        if ( !self::$show_tables ) {
            self::showTables($config);
        }
        if ( count(self::$show_tables) > 0 ) { // database contains tables
            foreach ( self::$tables as $table ) {
                if ( in_array( $config['table_prefix'] .$table, self::$show_tables) ) {
                    // database contains at least 1 ThinkUp table
                    throw new InstallerException("<strong>Oops!</strong><br /> Looks like at least some of ThinkUp's ".
                    "database tables already exist. To install ThinkUp from scratch, drop its tables in the ".
                    "<code>{$config['db_name']}</code> database.<br />".
                    "To repair your existing tables, click <a href=\"" . Utils::getSiteRootPathFromFileSystem() .
                    "install/index.php?step=repair&m=db\">here</a>.",
                    self::ERROR_DB_TABLES_EXIST);
                }
            }
        }
        return true;
    }

    /**
     * Check if ThinkUp table exist
     * See also self::checkTable($config)
     * Unlike doThinkUpTablesExist, this method doesn't throw an error; it simply returns a  boolean result.
     * This method should be called when not in installation steps.
     *
     * @param array $config
     * @return bool true When ThinkUp tables exist
     */
    public function doThinkUpTablesExist($config) {
        if ( !self::$show_tables ) {
            self::showTables($config);
        }

        $total_tables_found = 0;
        if ( count(self::$show_tables) > 0 ) { // database contains tables
            foreach ( self::$tables as $table ) {
                if ( in_array($config['table_prefix'] . $table, self::$show_tables) ) {
                    $total_tables_found++;
                }
            }
        }
        return ( $total_tables_found == count(self::$tables) ) ;
    }

    /**
     * Check if table is OK
     *
     * @param string $tablename Table name
     * @param array $config
     * @return array Check table results
     */
    private function isTableOk($tablename, $config) {
        if ( !self::$installer_dao ) {
            self::setDb($config);
        }
        $full_tablename = $config['table_prefix'] . $tablename;
        return self::$installer_dao->checkTable($full_tablename);
    }

    /**
     * Check if ThinkUp is already installed, that is, that:
     *  all system requirements are met;
     *  the ThinkUp config.inc.php file exists;
     *  all ThinkUp tables exist
     *  all tables report a status ok "Okay"
     *
     * @param array $config
     * @return bool true when ThinkUp is already installed
     */
    public function isThinkUpInstalled($config) {
        // check if file config present
        $config_file_exists = false;
        $config_file = THINKUP_WEBAPP_PATH . 'config.inc.php';

        // check if we have made config.inc.php
        if ( file_exists($config_file) ) {
            $config_file_exists = true;
        } else {
            self::$error_messages['config_file'] = "Config file doesn't exist.";
            return false;
        }

        // check version is met
        $version_met = self::checkStep1();
        // when testing
        if ( defined('TESTS_RUNNING') && TESTS_RUNNING && !empty($pass) ) {
            $version_met = $pass;
        }
        if ( !$version_met ) {
            self::$error_messages['requirements'] = "Requirements are not met. " .
                "Make sure your PHP version >= " . self::$required_version['php'] . ", " .
                "you have cURL and GD extension installed, and template and log directories are writable";
            return false;
        }

        // database is okay
        $db_check = self::checkDb($config);

        // table present
        $table_present = true;
        if ( !self::doThinkUpTablesExist($config) ) {
            self::$error_messages['table'] = 'ThinkUp\'s database tables are not fully installed.';
            $table_present = false;
        }

        return ($version_met && $db_check === true && $table_present);
    }

    /**
     * Populate tables/execute queries in build-db_mysql.sql
     *
     * @param array $config database configuration
     * @return array Queries for update
     */
    public function populateTables($config) {
        $install_queries = self::getInstallQueries($config['table_prefix']);
        $expected_queries = self::$installer_dao->diffDataStructure($install_queries,
        self::$installer_dao->getTables($config) );
        foreach ($expected_queries['queries'] as $query) {
            PDODAO::$PDO->exec($query);
        }
        return $expected_queries['for_update'];
    }

    /**
     * Read the contents of the webapp/install/sql/build-db_mysql.sql file.
     * Replace all instances of 'tu_' with the custom table prefix.
     *
     * @param string $table_prefix custom table prefix to replace the 'tu_' prefix
     * @return string
     */
    private function getInstallQueries($table_prefix) {
        if ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") || getenv("MODE")=="TESTS") {
            $query_file = THINKUP_WEBAPP_PATH . 'install/sql/build-db_mysql-upcoming-release.sql';
        } else {
            $query_file = THINKUP_WEBAPP_PATH . 'install/sql/build-db_mysql.sql';
        }
        if ( !file_exists($query_file) ) {
            throw new InstallerException("File <code>$query_file</code> is not found.", self::ERROR_FILE_NOT_FOUND);
        }
        $str_query = file_get_contents($query_file);
        $search = array();
        $replace = array();
        foreach (self::$tables as $key => $table) {
            $search[$key] = '/\btu_' . $table . '/';
            $replace[$key] = $table_prefix . $table;
        }
        // additional search for adding two spaces after PRIMARY KEY
        $search[]  = '/PRIMARY KEY \(/';
        $replace[] = 'PRIMARY KEY  (';

        $str_query = preg_replace($search, $replace, $str_query);
        return $str_query;
    }

    /**
     * Repair tables
     *
     * @param array $config
     * @return array Messages
     */
    public function repairTables($config) {
        if ( !self::$show_tables ) {
            self::showTables($config);
        }

        // check total tables is the same with the default defined
        $total_table_found = 0;
        if ( count(self::$show_tables) > 0 ) { // database contains tables
            foreach ( self::$tables as $table ) {
                if ( in_array($config['table_prefix'] .$table, self::$show_tables) ) {
                    $total_table_found++;
                }
            }
        }
        $messages = array();

        // show missing table
        $total_table_not_found = count(self::$tables) - $total_table_found;
        if ( $total_table_not_found > 0 ) {
            $messages['missing_tables']  = "There are <strong class=\"not_okay\">" .
            $total_table_not_found . " missing tables</strong>. ThinkUp will attempt to create missing tables and ".
            "alter existing tables if something is missing&hellip;<br />&nbsp;&nbsp;&nbsp;&nbsp;".
            "<span class=\"repair_log\">Create and alter some tables&hellip;</span>";
            $queries_logs = self::populateTables($config, true);
            if ( !empty($queries_logs) ) {
                foreach ( $queries_logs as $log ) {
                    $messages['missing_tables'] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
                    "<span class=\"repair_log\">$log</span>";
                }
            }
        } else {
            $messages['table_complete'] = 'Your ThinkUp table repairs are <strong class="okay">complete</strong>.';
        }

        // does checking on tables that exist
        $okay = true;
        $table = '';
        foreach (self::$tables as $t) {
            $table =  $t;
            $table_status = self::isTableOk($table, $config);
            if ( $table_status['Msg_text'] == "OK" ) {
                $messages[$t] = "<p>The <code>$table</code> table is <strong class=\"okay\">okay</strong>.</p>";
            } else {
                $messages[$t]  = "<p>The <code>$table</code> table is <strong class=\"not_okay\">not okay</strong>. ";
                $messages[$t] .= "It is reporting the following error: <code>".$table_status['Msg_text']."</code>. ";
                $messages[$t] .= "ThinkUp will attempt to repair this table&hellip;";

                // repairs table that not okay
                $row = self::$installer_dao->repairTable($table);

                if ( isset($row['Msg_text']) && $row['Msg_text'] == 'OK' ) {
                    $messages[$t] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"repair_log\">" .
                                     "Sucessfully repaired the $table table.</span>";
                } else { // failed to repair the table
                    $messages[$t] .= "<br />&nbsp;&nbsp;&nbsp;&nbsp;<span class=\"repair_log\">" .
                                     "Failed to repair the $table table. " .
                                     "Error: {$row['Msg_text']}</span><br />";
                    self::$error_messages[$t] = "<p class=\"repair_log\">Failed to repair the $table table.</p>";
                }

                $messages[$t] .= "</p>";
            }
        }
        return $messages;
    }

    /**
     * Check if sample config (config.sample.inc.php) exists
     *
     * @param string $file absolute file path
     */
    private function checkSampleConfig($file) {
        if ( !file_exists($file) ) {
            throw new InstallerException(
                'Sorry, ThinkUp requires the config.sample.inc.php file to work. Please re-upload this file from the '.
            'ThinkUp installation package.', self::ERROR_CONFIG_SAMPLE_MISSING);
        }
    }

    /**
     * Create config file
     *
     * @param array $db_config
     * @param array $admin_user
     * @return bool  true if config successfuly created
     */
    public function createConfigFile($db_config, $admin_user) {
        $config_file = THINKUP_WEBAPP_PATH . 'config.inc.php';

        if (!file_exists($config_file) || filesize($config_file) === 0) {
            $new_config_file_contents = self::generateConfigFile($db_config, $admin_user);
            if ( !file_exists($config_file) && !is_writable(dirname($config_file)) ) {
                // Config file doesn't exist, and I won't be able to create it.
                return false;
            } else if ( file_exists($config_file) && !is_writable($config_file) ) {
                // Config file exist, but I can't write to it.
                return false;
            } else {
                /* write the config file */
                $handle = fopen($config_file, 'w');
                foreach( $new_config_file_contents as $line ) {
                    fwrite($handle, $line);
                }
                fclose($handle);
                return true;
            }
        }
    }

    /**
     * Create config file
     *
     * @param array $db_config
     * @param array $admin_user
     * @return array Strings of new config file contents
     */
    public function generateConfigFile($db_config, $admin_user) {
        $sample_config_filename = THINKUP_WEBAPP_PATH . 'config.sample.inc.php';
        self::checkSampleConfig($sample_config_filename);

        $new_config = array(
            'site_root_path' => Utils::getSiteRootPathFromFileSystem(),
            'source_root_path' => "dirname( __FILE__ ) . '/'",
            'db_host' => $db_config['db_host'],
            'db_user' => $db_config['db_user'],
            'db_password' => $db_config['db_password'],
            'db_name' => $db_config['db_name'],
            'db_socket' => $db_config['db_socket'],
            'db_port' => $db_config['db_port'],
            'table_prefix' => $db_config['table_prefix'],
            'timezone' => $db_config['timezone']
        );

        // read sample configuration file and replace some lines
        $sample_config = file($sample_config_filename);
        foreach ($sample_config as $line_num => $line) {
            if (preg_match('/\[\'([a-zA-Z0-9_]+)\'\]/', $line, $regs)) {
                $what = $regs[1];
                if (isset($new_config[$what])) {
                    if ($what != 'source_root_path') {
                        $sample_config[$line_num] = preg_replace('/=.*;(.*)/', "= '" . $new_config[$what] . "';\\1",
                        $sample_config[$line_num]);
                    } else { //don't quote source_root_path default value
                        $sample_config[$line_num] = preg_replace('/=.*;(.*)/', "= " . $new_config[$what] . ";\\1",
                        $sample_config[$line_num]);
                    }
                }
            }
        } // end foreach
        return $sample_config;
    }

    /**
     * Get error messages.
     *
     * @return array Error messages
     */
    public function getErrorMessages() {
        return self::$error_messages;
    }

    /**
     * Clear error messages.
     *
     * @return void
     */
    public function clearErrorMessages() {
        self::$error_messages = array();
    }

    /**
     * Repairer does checking on step #1
     *
     * @return bool
     */
    public function repairerCheckStep1() {
        if ( !self::checkStep1() ) {
            throw new InstallerException(
                "ThinkUp's requirements are not met. Make sure your PHP version >= " . self::$required_version['php'] .
                ", you have the cURL and GD extension installed, and the template and log directories are writable.",
            self::ERROR_REQUIREMENTS);
        }
        return true;
    }

    /**
     * Repairer does checking on files configuration existent
     *
     * @return string Path file
     */
    public function repairerCheckConfigFile() {
        $config_file = THINKUP_WEBAPP_PATH . 'config.inc.php';

        if ( !file_exists($config_file) ) {
            throw new InstallerException(
                'ThinkUp needs a <code>config.inc.php</code> file to work from. ' .
                'Please upload this file to <code>' . THINKUP_WEBAPP_PATH . '</code> or ' .
                'copy / rename from <code>' . THINKUP_WEBAPP_PATH . 'config.sample.inc.php</code> to ' .
                '<code>' . THINKUP_WEBAPP_PATH . 'config.inc.php</code>. If you don\'t have permission to ' .
                'do this, you can reinstall ThinkUp by clearing out ThinkUp tables and then clicking '.
                '<a href="' . Utils::getSiteRootPathFromFileSystem() . 'install/">here</a>',
            self::ERROR_CONFIG_FILE_MISSING);
        }
        return $config_file;
    }

    /**
     * Repairer does checking on files configuration if $THINKUP_CFG['repair'] has been defined or not
     *
     * @param array $config ThinkUp configuration values
     * @return bool
     */
    public function repairerIsDefined($config) {
        if ( !isset($config['repair']) or !$config['repair'] ) {
            throw new InstallerException('To repair ThinkUp\'s installation, please add '.
            '<code>$THINKUP_CFG[\'repair\'] = true; </code><br>in your configuration file at <code>' .
            THINKUP_WEBAPP_PATH . 'config.inc.php</code>', self::ERROR_REPAIR_CONFIG);
        }
        return true;
    }

    /**
     * Return array of tables that appear in ThinkUp's build-db_mysql.sql file
     * @return array Table names
     */
    public function getTablesToInstall() {
        $table_names = array();
        if ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") || getenv("MODE")=="TESTS") {
            $install_queries = file_get_contents(THINKUP_WEBAPP_PATH."install/sql/build-db_mysql-upcoming-release.sql");
        } else {
            $install_queries = file_get_contents(THINKUP_WEBAPP_PATH."install/sql/build-db_mysql.sql");
        }
        $queries = explode(';', $install_queries);
        if ( $queries[count($queries)-1] == '' ) {
            array_pop($queries);
        }
        foreach($queries as $query) {
            if (preg_match("|CREATE TABLE ([^ ]*)|", $query, $matches)) {
                $table_names[] = str_replace('tu_', '', $matches[1]);
            }
        }
        return $table_names;
    }

    /**
     * Store the application's server name in application settings as last-resort use by command-line scripts.
     */
    public static function storeServerName() {
        $server_name = empty($_SERVER['SERVER_NAME']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        if ($server_name != '') {
            $option_dao = DAOFactory::getDAO('OptionDAO');
            $current_stored_server_name = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, 'server_name');
            if ($current_stored_server_name) {
                $option_dao->updateOption($current_stored_server_name->option_id, $server_name);
            } else {
                $option_dao->insertOption(OptionDAO::APP_OPTIONS, 'server_name', $server_name);
            }
        }
    }
}
