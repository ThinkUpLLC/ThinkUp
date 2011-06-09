UpgradeController
=================
Inherits from `ThinkUpAuthController <./ThinkUpAuthController.html>`_.

ThinkUp/webapp/_lib/controller/class.UpgradeController.php

Copyright (c) 2009-2011 Mark Wilkie

Upgrade Controller



Methods
-------

__construct
~~~~~~~~~~~
* **@param** bool $session_started
* **@return** UpgradeController


Constructor

.. code-block:: php5

    <?php
        public function __construct($session_started=false) {
            if(! getenv('CLI_BACKUP')) {
                parent::__construct($session_started);
            }
        }


authControl
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function authControl() {
    
            $this->disableCaching();
            Utils::defineConstants();
            $config = Config::getInstance();
            $thinkup_db_version = $config->getValue('THINKUP_VERSION');
    
            $install_dao = DAOFactory::getDAO('InstallerDAO');
            $db_version = self::getCurrentDBVersion($cached = false);
            $option_dao = DAOFactory::getDAO('OptionDAO');
    
            // clear options session data
            $option_dao->clearSessionData(OptionDAO::APP_OPTIONS);
    
            if(isset($_GET['migration_index'])) {
                $migrations = $this->getMigrationList($db_version);
                $migration_index = $_GET['migration_index'] - 1;
                $migrations = $this->getMigrationList($db_version);
                $processed = false;
                $sql = $migrations[$migration_index]['sql'];
                // remove comments...
                $sql = preg_replace('/\-\-.*/','', $sql);
    
                try {
                    $install_dao->runMigrationSQL($sql);
                    $processed = true;
                    $this->setJsonData( array( 'processed'=>$processed, 'sql'=>$sql));
                } catch(Exception $e) {
                    $this->setJsonData( array( 'processed'=>$processed, 'message'=>$e->getMessage(), 'sql'=>$sql));
                }
            } else if (isset($_GET['migration_done'])) {
                $option = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, 'database_version');
                if($option) {
                    $option_dao->updateOptionByName(OptionDAO::APP_OPTIONS, 'database_version', $thinkup_db_version);
                } else {
                    $option_dao->insertOption(OptionDAO::APP_OPTIONS, 'database_version', $thinkup_db_version);
                }
                $this->setJsonData(array('migration_complete' => true) );
                $this->deleteTokenFile();
                // remove snowflake in progress session if needed
                $this->snowflakeSession(false, true);
            } else {
                $this->setPageTitle('Upgrade the ThinkUp Database Structure');
                $this->setViewTemplate('install.upgrade.tpl');
                if(version_compare($db_version, $thinkup_db_version, '<')) {
                    ## get migrations we need to run...
                    $migrations = $this->getMigrationList($db_version);
                    $this->addToView('migrations',$migrations);
                    $this->addToView('migrations_json', json_encode($migrations));
                    if(isset($_GET['upgrade_token'])) {
                        $this->addToView('upgrade_token', $_GET['upgrade_token']);
                    }
                    # no migrations needed, just update the application db version option to reflect
                    if(count($migrations) == 0) {
                        $option = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, 'database_version');
                        if($option) {
                            $option_dao->updateOptionByName(OptionDAO::APP_OPTIONS, 'database_version',
                            $thinkup_db_version);
                        } else {
                            $option_dao->insertOption(OptionDAO::APP_OPTIONS, 'database_version', $thinkup_db_version);
                        }
                        $this->addToView('version_updated', true);
                        $this->deleteTokenFile();
                    }
                }
            }
            return $this->generateView();
        }


deleteTokenFile
~~~~~~~~~~~~~~~

Delete token file if it exists

.. code-block:: php5

    <?php
        public function deleteTokenFile() {
            if(file_exists(THINKUP_WEBAPP_PATH . self::CACHE_DIR . '/upgrade_token')) {
                unlink(THINKUP_WEBAPP_PATH . self::CACHE_DIR . '/upgrade_token');
            }
        }


isUpgrading
~~~~~~~~~~~
* **@param** bool Is the current user an admin
* **@param** str The calling classname
* **@return** bool Whether or not we need to show the upgrade page


Determin if ThinkUp needs to show the upgrading page.

.. code-block:: php5

    <?php
        public static function isUpgrading($is_admin, $class_name) {
            $config = Config::getInstance();
            $status = false;
            $db_version = UpgradeController::getCurrentDBVersion($config->getValue( 'cache_pages' ));
            if (version_compare($db_version, $config->getValue('THINKUP_VERSION'), '<') ) {
                if( $class_name != 'UpgradeController' ) {
                    $status = true;
                } else if ( !$is_admin && !isset($_GET['upgrade_token']) ) {
                    $status = true;
                }
                if ($status == true) {
                    self::generateUpgradeToken();
                }
            }
            return $status;
        }


control
~~~~~~~

Override control to allow a user to auth with an upgrade token if needed

.. code-block:: php5

    <?php
        public function control() {
            if ($this->isAdmin()) {
                return $this->authControl();
            } else if(isset($_GET['upgrade_token'])) {
                if($this->isTokenAuth($_GET['upgrade_token'])) {
                    return $this->authControl();
                } else {
                    throw new Exception("This update has already been completed.");
                }
            } else {
                throw new Exception("You must be a ThinkUp admin to do this");
            }
        }


isTokenAuth
~~~~~~~~~~~
* **@param** str token
* **@return** bool True if the token is valid


Token Auth

.. code-block:: php5

    <?php
        public static function isTokenAuth($query_token) {
            $token_file = THINKUP_WEBAPP_PATH . self::CACHE_DIR . '/upgrade_token';
            $status = false;
            if(file_exists($token_file)) {
                $file_token = file_get_contents($token_file);
                if($file_token == $query_token) {
                    $status = true;
                }
            }
            return $status;
        }


getCurrentDBVersion
~~~~~~~~~~~~~~~~~~~
* **@return** float current DB version


Returns the current db version

.. code-block:: php5

    <?php
        public static function getCurrentDBVersion($cached) {
            $option_dao = DAOFactory::getDAO('OptionDAO');
            $db_version = self::RUNNABLE_MIGRATION_MIN;
            $has_options_table = $option_dao->isOptionsTable();
            if ($has_options_table) {
                $db_version = $option_dao->getOptionValue(OptionDAO::APP_OPTIONS, 'database_version', $cached);
                if( !$db_version) {
                    $db_version = self::RUNNABLE_MIGRATION_MIN;
                }
            }
            return $db_version;
        }


getMigrationList
~~~~~~~~~~~~~~~~
* **@param** int The current db version
* **@return** array List of migration sql


Returns a hash of the needed db migrations

.. code-block:: php5

    <?php
        public function getMigrationList($version) {
            $dir = THINKUP_WEBAPP_PATH . self::MIGRATION_DIR;
            $dir_list = glob($dir . '/*.migration');
            $migrations = array();
            $config = Config::getInstance();
            for ($i = 0; $i < count($dir_list); $i++) {
                if(preg_match('/_v(\d+\.\d+(\.\d+)?(\w+)?)\.sql\.migration/', $dir_list[$i], $matches)) {
                    $migration_version = $matches[1];
                    // skip early pre beta 1 versions...
                    if(preg_match('/^0\.00/', $migration_version)) {
                        continue;
                    }
                    if(version_compare($migration_version, $version) > 0 &&
                    version_compare($migration_version, $config->getValue('THINKUP_VERSION')) < 1 ) {
                        if($migration_version == 0.3) {
                            $install_dao = DAOFactory::getDAO('InstallerDAO');
                            if(! $install_dao->needsSnowflakeUpgrade() && ! $this->snowflakeSession(false) ) {
                                continue;
                            } else {
                                // set snowflake in progress session
                                $this->snowflakeSession(true, false);
                            }
                        }
                        $migration_string = file_get_contents($dir_list[$i]);
                        if(! $migration_string) {
                            throw new OpenFileException("Unable to open file: " + $dir_list[$i]);
                        } else {
                            // check for modified prefix
                            $table_prefix = $config->getValue('table_prefix');
                            if($table_prefix != 'tu_') {
                                $migration_string = str_replace('tu_', $table_prefix, $migration_string);
                            }
                            $migration = array("version" =>  $migration_version, 'sql'  => $migration_string);
                            array_push($migrations, $migration);
                        }
                    }
                }
            }
            return $migrations;
        }


generateUpgradeToken
~~~~~~~~~~~~~~~~~~~~

Generates a one time upgrade token, and emails admins with the token info.

.. code-block:: php5

    <?php
        public function generateUpgradeToken() {
            $token_file = THINKUP_WEBAPP_PATH . self::CACHE_DIR . '/upgrade_token';
            $md5_token = '';
            if(! file_exists($token_file)) {
                $fp = fopen($token_file, 'w');
                if($fp) {
                    $token = self::TOKEN_KEY . rand(0, time());
                    $md5_token = md5($token);
                    if(! fwrite($fp, $md5_token)) {
                        throw new OpenFileException("Unable to write upgrade token file: " + $token_file);
                    }
                    fclose($fp);
                } else {
                    throw new OpenFileException("Unable to create upgrade token file: " + $token_file);
                }
                // email our admin with this token.
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $admins = $owner_dao->getAdmins();
                if($admins) {
                    $tos = array();
                    foreach($admins as $admin) {
                        $tos[] = $admin->email;
                    }
                    $to = join(',', $tos);
                    $upgrade_email = new SmartyThinkUp();
                    $upgrade_email->caching=false;
                    $server = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'; //supress test weirdness
                    $upgrade_email->assign('server', $server );
                    $upgrade_email->assign('token', $md5_token );
                    $message = $upgrade_email->fetch('_email.upgradetoken.tpl');
                    $config = Config::getInstance();
                    Mailer::mail($to, "Upgrade Your ThinkUp Database", $message);
                }
            }
        }


snowflakeSession
~~~~~~~~~~~~~~~~
* **@param** boolean Delete the seeion if defined


Sets/Deletes data in the session to let us know we needed to run the snowflake migration

.. code-block:: php5

    <?php
        public function snowflakeSession($value = false, $delete = false) {
            $key = 'runnig_snowflake_uprade';
            if($delete) {
                if( SessionCache::isKeySet($key) ) {
                    SessionCache::unsetKey($key);
                }
            } else {
                if($value) {
                    SessionCache::put($key, $value);
                } else {
                    if( SessionCache::isKeySet($key) ) {
                        return SessionCache::get($key);
                    } else {
                        return false;
                    }
                }
            }
        }




