Installer
=========

ThinkUp/webapp/_lib/model/class.Installer.php

Copyright (c) 2009-2011 Dwi Widiastuti, Gina Trapani

Installer
A singleton class that doess the heavy lifting of installing ThinkUp.


Properties
----------

instance
~~~~~~~~

Singleton instance of Installer

error_messages
~~~~~~~~~~~~~~

Stores error messages.

current_version
~~~~~~~~~~~~~~~

Stores current version of ThinkUp

required_version
~~~~~~~~~~~~~~~~

Stores required version of each apps

tables
~~~~~~

List of ThinkUp tables.
If there are new tables added, make sure this property also updated

show_tables
~~~~~~~~~~~

Result from SHOW TABLES

@TODO: Why is this public? Shouldn't be public, security risk

installer_dao
~~~~~~~~~~~~~

PDO Instance



Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function __construct() {
            self::$tables = $this->getTablesToInstall();
        }


getInstance
~~~~~~~~~~~
* **@return** Installer


Get Installer instance

.. code-block:: php5

    <?php
        public static function getInstance() {
            if ( self::$instance == null ) {
                self::$instance = new Installer();
    
                // use lazy loading
                if ( !class_exists('Loader', FALSE) ) {
                    require_once THINKUP_WEBAPP_PATH . '_lib/model/class.Loader.php';
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


checkVersion
~~~~~~~~~~~~
* **@param** str $ver can be used for testing for failing
* **@return** bool Whether or not required version of PHP is present


Check PHP version

.. code-block:: php5

    <?php
        public function checkVersion($ver = '') {
            // when testing
            if ( defined('TESTS_RUNNING') && TESTS_RUNNING && !empty($ver) ) {
                $version = $ver;
            } else {
                $version = PHP_VERSION;
            }
            return version_compare( $version, self::$required_version['php'], '>=' );
        }


getCurrentVersion
~~~~~~~~~~~~~~~~~
* **@return** int Current PHP version


Get current version

.. code-block:: php5

    <?php
        public function getCurrentVersion() {
            return self::$current_version;
        }


getRequiredVersion
~~~~~~~~~~~~~~~~~~
* **@return** int Required PHP version


Get required version

.. code-block:: php5

    <?php
        public function getRequiredVersion() {
            return self::$required_version;
        }


checkDependency
~~~~~~~~~~~~~~~
* **@param** array $libs For use in tests
* **@return** array


Check GD, cURL, PDO, and JSON extensions are loaded

.. code-block:: php5

    <?php
        public function checkDependency($libs = array()) {
            $ret = array('curl'=>false, 'gd'=>false, 'pdo'=>false, 'pdo_mysql'=>false, 'json'=>false);
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
            // when testing
            if ( defined('TESTS_RUNNING') && TESTS_RUNNING && !empty($libs) ) {
                $ret = $libs;
            }
            return $ret;
        }


curlDependenciesMet
~~~~~~~~~~~~~~~~~~~
* **@return** bool


Confirm that the cURL extension is loaded and configured as needed

.. code-block:: php5

    <?php
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


checkPermission
~~~~~~~~~~~~~~~
* **@param** array $perms can be used for testing for failing
* **@return** array 'compiled_view'=>true/false, 'cache'=>true/false


Check if log and template directories are writeable

.. code-block:: php5

    <?php
        public function checkPermission($perms = array()) {
            $compile_dir = THINKUP_WEBAPP_PATH . '_lib/view/compiled_view';
            $cache_dir = "$compile_dir/cache";
            $ret = array('compiled_view' => false, 'cache' => false);
            if ( is_writable($compile_dir) ) {
                $ret['compiled_view'] = true;
            }
            if ( is_writable($cache_dir) ) {
                $ret['cache'] = true;
            }
            // when testing
            if ( defined('TESTS_RUNNING') && TESTS_RUNNING && !empty($perms) ) {
                $ret = $perms;
            }
            return $ret;
        }


checkPath
~~~~~~~~~
* **@throws** InstallerException
* **@param** array $config
* **@return** bool


Check if Thinkup's paths exists.

.. code-block:: php5

    <?php
        public function checkPath($config) {
            // check if $THINKUP_CFG related to path exists
            if ( !is_dir($config['source_root_path']) ) {
                throw new InstallerException("ThinkUp's source root directory is not found.",
                self::ERROR_CONFIG_SOURCE_ROOT_PATH);
            }
            return true;
        }


checkStep1
~~~~~~~~~~
* **@param** array $pass can be used for testing for failing
* **@return** bool


Check all requirements on step 1
Check PHP version, cURL, GD, JSON, and path permission

.. code-block:: php5

    <?php
        public function checkStep1($pass = true) {
            $version_compat = $this->checkVersion();
    
            $lib_depends = $this->checkDependency();
            $lib_depends_ret = true;
            foreach ($lib_depends as $lib) {
                $lib_depends_ret = $lib_depends_ret && $lib;
            }
    
            $writeable_permission = $this->checkPermission();
            $writeable_permission_ret = true;
            foreach ($writeable_permission as $permission) {
                $writeable_permission_ret = $writeable_permission_ret && $permission;
            }
            // when testing
            if ( defined('TESTS_RUNNING') && TESTS_RUNNING && !empty($pass) ) {
                $ret = $pass;
            } else {
                $ret = ($version_compat && $lib_depends_ret && $writeable_permission_ret);
            }
            return $ret;
        }


setDb
~~~~~
* **@throws** InstallerException
* **@param** array $config Database config
* **@return** InstallerMySQLDAO


Set Installer DAO

.. code-block:: php5

    <?php
        public function setDb($config) {
            try {
                self::$installer_dao = DAOFactory::getDAO('InstallerDAO', $config);
            } catch (PDOException $e) {
                throw new InstallerException('Failed establishing database connection. '. $e->getMessage() .'',
                self::ERROR_DB_CONNECT);
            }
            return self::$installer_dao;
        }


showTables
~~~~~~~~~~
* **@param** array $config
* **@return** array tables


Get SHOW TABLES at current $db

.. code-block:: php5

    <?php
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


checkDb
~~~~~~~
* **@param** array $config Database credentials
* **@return** bool


Check database

.. code-block:: php5

    <?php
        public function checkDb($config) {
            try {
                self::setDb($config);
                return true;
            } catch (InstallerException $e) {
                return $e;
            }
        }


checkTable
~~~~~~~~~~
* **@param** array $config
* **@return** bool return true when ThinkUp tables don't exist, throw error when table exists


Check if ThinkUp tables exist
See also self::doThinkUpTablesExist($config).
Unlike doThinkUpTablesExist, this method throws installer exceptions.
This method should be called during installation steps.

.. code-block:: php5

    <?php
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
                        "<code style='font-family: Consolas,Monaco,Courier,monospace; border: 1px solid #999; ".
                        "background-color: #ccc;'>{$config['db_name']}</code> database.<br />".
                        "To repair your existing tables, click <a href=\"" . THINKUP_BASE_URL . 
                        "install/index.php?step=repair&m=db\">here</a>.",
                        self::ERROR_DB_TABLES_EXIST);
                    }
                }
            }
            return true;
        }


doThinkUpTablesExist
~~~~~~~~~~~~~~~~~~~~
* **@param** array $config
* **@return** bool true When ThinkUp tables exist


Check if ThinkUp table exist
See also self::checkTable($config)
Unlike doThinkUpTablesExist, this method doesn't throw an error; it simply returns a  boolean result.
This method should be called when not in installation steps.

.. code-block:: php5

    <?php
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


isTableOk
~~~~~~~~~
* **@param** string $tablename Table name
* **@param** array $config
* **@return** array Check table results


Check if table is OK

.. code-block:: php5

    <?php
        private function isTableOk($tablename, $config) {
            if ( !self::$installer_dao ) {
                self::setDb($config);
            }
            $full_tablename = $config['table_prefix'] . $tablename;
            return self::$installer_dao->checkTable($full_tablename);
        }


isThinkUpInstalled
~~~~~~~~~~~~~~~~~~
* **@param** array $config
* **@return** bool true when ThinkUp is already installed


Check if ThinkUp is already installed, that is, that:
 all system requirements are met;
 the ThinkUp config.inc.php file exists;
 all ThinkUp tables exist
 all tables report a status ok "Okay"

.. code-block:: php5

    <?php
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
                    "you have cURL and GD extension installed, and template and log directories are writeable";
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


populateTables
~~~~~~~~~~~~~~
* **@param** array $config database configuration
* **@return** array Queries for update


Populate tables/execute queries in build-db_mysql.sql

.. code-block:: php5

    <?php
        public function populateTables($config) {
            $install_queries = self::getInstallQueries($config['table_prefix']);
            $expected_queries = self::$installer_dao->diffDataStructure($install_queries,
            self::$installer_dao->getTables($config) );
            foreach ($expected_queries['queries'] as $query) {
                PDODAO::$PDO->exec($query);
            }
            return $expected_queries['for_update'];
        }


getInstallQueries
~~~~~~~~~~~~~~~~~
* **@param** string $table_prefix custom table prefix to replace the 'tu_' prefix
* **@return** string


Read the contents of the webapp/install/sql/build-db_mysql.sql file.
Replace all instances of 'tu_' with the custom table prefix.

.. code-block:: php5

    <?php
        private function getInstallQueries($table_prefix) {
            $query_file = THINKUP_WEBAPP_PATH . 'install/sql/build-db_mysql.sql';
            if ( !file_exists($query_file) ) {
                throw new InstallerException("File <code>$query_file</code> is not found.", self::ERROR_FILE_NOT_FOUND);
            }
            $str_query = file_get_contents($query_file);
            $search = array();
            $replace = array();
            foreach (self::$tables as $key => $table) {
                $search[$key] = 'tu_' . $table;
                $replace[$key] = $table_prefix . $table;
            }
            // additional search for adding two spaces after PRIMARY KEY
            $search[]  = 'PRIMARY KEY (';
            $replace[] = 'PRIMARY KEY  (';
    
            $str_query = str_replace($search, $replace, $str_query);
            return $str_query;
        }


repairTables
~~~~~~~~~~~~
* **@param** array $config
* **@return** array Messages


Repair tables

.. code-block:: php5

    <?php
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
                $messages['table_complete'] = 'Your ThinkUp tables are <strong class="okay">complete</strong>.';
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


checkSampleConfig
~~~~~~~~~~~~~~~~~
* **@param** string $file absolute file path


Check if sample config (config.sample.inc.php) exists

.. code-block:: php5

    <?php
        private function checkSampleConfig($file) {
            if ( !file_exists($file) ) {
                throw new InstallerException(
                    'Sorry, ThinkUp requires the config.sample.inc.php file to work. Please re-upload this file from the '.
                'ThinkUp installation package.', self::ERROR_CONFIG_SAMPLE_MISSING);
            }
        }


createConfigFile
~~~~~~~~~~~~~~~~
* **@param** array $db_config
* **@param** array $admin_user
* **@return** bool  true if config successfuly created


Create config file

.. code-block:: php5

    <?php
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


generateConfigFile
~~~~~~~~~~~~~~~~~~
* **@param** array $db_config
* **@param** array $admin_user
* **@return** array Strings of new config file contents


Create config file

.. code-block:: php5

    <?php
        public function generateConfigFile($db_config, $admin_user) {
            $sample_config_filename = THINKUP_WEBAPP_PATH . 'config.sample.inc.php';
            self::checkSampleConfig($sample_config_filename);
    
            $new_config = array(
                'site_root_path' => THINKUP_BASE_URL,
                'source_root_path' => THINKUP_ROOT_PATH,
                'db_host' => $db_config['db_host'],
                'db_user' => $db_config['db_user'],
                'db_password' => $db_config['db_password'],
                'db_name' => $db_config['db_name'],
                'db_socket' => $db_config['db_socket'],
                'db_port' => $db_config['db_port'],
                'table_prefix' => $db_config['table_prefix'],
                'GMT_offset' => $db_config['GMT_offset'],
                'timezone' => $db_config['timezone']
            );
    
            // read sample configuration file and replace some lines
            $sample_config = file($sample_config_filename);
            foreach ($sample_config as $line_num => $line) {
                if (preg_match('/\[\'([a-zA-Z0-9_]+)\'\]/', $line, $regs)) {
                    $what = $regs[1];
                    if (isset($new_config[$what])) {
                        $sample_config[$line_num] = preg_replace('/=.*;(.*)/', "= '" . $new_config[$what] . "';\\1",
                        $sample_config[$line_num]);
                    }
                }
            } // end foreach
            return $sample_config;
        }


getErrorMessages
~~~~~~~~~~~~~~~~
* **@return** array Error messages


Get error messages.

.. code-block:: php5

    <?php
        public function getErrorMessages() {
            return self::$error_messages;
        }


clearErrorMessages
~~~~~~~~~~~~~~~~~~
* **@return** void


Clear error messages.

.. code-block:: php5

    <?php
        public function clearErrorMessages() {
            self::$error_messages = array();
        }


repairerCheckStep1
~~~~~~~~~~~~~~~~~~
* **@return** bool


Repairer does checking on step #1

.. code-block:: php5

    <?php
        public function repairerCheckStep1() {
            if ( !self::checkStep1() ) {
                throw new InstallerException(
                    "ThinkUp's requirements are not met. Make sure your PHP version >= " . self::$required_version['php'] .
                    ", you have the cURL and GD extension installed, and the template and log directories are writeable.",
                self::ERROR_REQUIREMENTS);
            }
            return true;
        }


repairerCheckConfigFile
~~~~~~~~~~~~~~~~~~~~~~~
* **@return** string Path file


Repairer does checking on files configuration existent

.. code-block:: php5

    <?php
        public function repairerCheckConfigFile() {
            $config_file = THINKUP_WEBAPP_PATH . 'config.inc.php';
    
            if ( !file_exists($config_file) ) {
                throw new InstallerException(
                    'ThinkUp needs a <code>config.inc.php</code> file to work from. ' .
                    'Please upload this file to <code>' . THINKUP_WEBAPP_PATH . '</code> or ' .
                    'copy / rename from <code>' . THINKUP_WEBAPP_PATH . 'config.sample.inc.php</code> to ' .
                    '<code>' . THINKUP_WEBAPP_PATH . 'config.inc.php</code>. If you don\'t have permission to ' .
                    'do this, you can reinstall ThinkUp by clearing out ThinkUp tables and then clicking '.
                    '<a href="' . THINKUP_BASE_URL . 'install/">here</a>', self::ERROR_CONFIG_FILE_MISSING);
            }
            return $config_file;
        }


repairerIsDefined
~~~~~~~~~~~~~~~~~
* **@param** array $config ThinkUp configuration values
* **@return** bool


Repairer does checking on files configuration if $THINKUP_CFG['repair'] has been defined or not

.. code-block:: php5

    <?php
        public function repairerIsDefined($config) {
            if ( !isset($config['repair']) or !$config['repair'] ) {
                throw new InstallerException('To repair ThinkUp\'s installation, please add '.
                '<code>$THINKUP_CFG[\'repair\'] = true; </code><br>in your configuration file at <code>' . 
                THINKUP_WEBAPP_PATH . 'config.inc.php</code>', self::ERROR_REPAIR_CONFIG);
            }
            return true;
        }


getTablesToInstall
~~~~~~~~~~~~~~~~~~
* **@return** array Table names


Return array of tables that appear in ThinkUp's build-db_mysql.sql file

.. code-block:: php5

    <?php
        public function getTablesToInstall() {
            $table_names = array();
            $install_queries = file_get_contents(THINKUP_WEBAPP_PATH."install/sql/build-db_mysql.sql");
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




