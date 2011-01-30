<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.UpgradeController.php
 *
 * Copyright (c) 2009-2011 Mark Wilkie
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
 * Upgrade Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 * Upgrade Controller
 *
 * Checks the current app version with a DB version option stored in the db. If that version option
 * does not exists, or it is older than the current version, we flag the app as in need of migration
 * in the root ThinkUp controller, and notify web users that the app is in an upgrade state.
 *
 * If the user who loads the site update message page is not logged in, we generate an upgrade token file,
 * and email that token to admin users. This will allow users to upgrade the app without needing to authenticate,
 * and this will allow database migrations/updates of the owner tables or any other user auth tables.
 *
 * This controller will:
 * * Run any needed sql migration scripts to get the db up to date
 * * Update the option version to reflect the latest version of the app
 *
 *
 */
class UpgradeController extends ThinkUpAuthController {

    /**
     * First auto updatable migration
     */
    const RUNNABLE_MIGRATION_MIN = '0.2';

    /**
     * SQL migration directory
     */
    const MIGRATION_DIR = 'install/sql/mysql_migrations';

    /**
     * cache dir. We will write an upgrade auth token here if needed.
     */
    const CACHE_DIR = '_lib/view/compiled_view';

    /**
     * token key
     */
    const TOKEN_KEY = 'a_token_key';

    /**
     * Constructor
     * @param bool $session_started
     * @return UpgradeController
     */
    public function __construct($session_started=false) {
        if(! getenv('CLI_BACKUP')) {
            parent::__construct($session_started);
        }
    }

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

            if($db_version < $thinkup_db_version) {
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

    /**
     * Delete token file if it exists
     */
    public function deleteTokenFile() {
        if(file_exists(THINKUP_WEBAPP_PATH . self::CACHE_DIR . '/upgrade_token')) {
            unlink(THINKUP_WEBAPP_PATH . self::CACHE_DIR . '/upgrade_token');
        }
    }

    /**
     * Do we need to show the upgrading page?
     * @param boolean Are we an admin?
     * @param str Our classname, so we can filter out the UpgradeController from the upgrade status check
     * @return bool
     */
    public static function isUpgrading($isadmin, $classname) {
        $config = Config::getInstance();
        $status = false;
        $db_version = UpgradeController::getCurrentDBVersion($config->getValue( 'cache_pages' ));
        if($db_version < $config->getValue('THINKUP_VERSION') ) {
            if( $classname != 'UpgradeController' ) {
                $status = true;
            } else if( ! $isadmin && ! isset($_GET['upgrade_token']) ) {
                $status = true;
            }
            if($status == true) {
                self::generateUpgradeToken();
            }
        }
        return $status;
    }

    /**
     * Override control to allow a user to auth with an upgrade token if needed
     */
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

    /**
     * Token Auth
     * @param str token
     * @return bool True if the token is valid
     */
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

    /**
     * Returns the current db version
     * @return float current DB version
     */
    public static function getCurrentDBVersion($cached) {
        $option_dao = DAOFactory::getDAO('OptionDAO');
        $db_version = self::RUNNABLE_MIGRATION_MIN;
        $has_options_table = $option_dao->isOptionsTable();
        if($has_options_table) {
            $db_version = $option_dao->getOptionValue(OptionDAO::APP_OPTIONS, 'database_version', $cached);
            if(! $db_version) {
                $db_version = self::RUNNABLE_MIGRATION_MIN;
            }
        }
        return $db_version;
    }

    /**
     * Returns a hash of the needed db migrations
     * @param int The current db version
     * @return array List of migration sql
     */
    public function getMigrationList($version) {
        $dir = THINKUP_WEBAPP_PATH . self::MIGRATION_DIR;
        $dir_list = glob($dir . '/*.migration');
        $migrations = array();
        $config = Config::getInstance();
        for ($i = 0; $i < count($dir_list); $i++) {
            if(preg_match('/_v(\d+\.\d+)\.sql\.migration/', $dir_list[$i], $matches)) {
                $migration_version = $matches[1];
                if($migration_version > $version && $migration_version <= $config->getValue('THINKUP_VERSION')) {
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
                            $migration_string = preg_replace("/\s`tu_/", " `$table_prefix", $migration_string);
                            $migration_string = preg_replace("/\stu_/", " $table_prefix", $migration_string);
                        }
                        $migration = array("version" =>  $migration_version, 'sql'  => $migration_string);
                        array_push($migrations, $migration);
                    }
                }
            }
        }
        return $migrations;
    }

    /**
     * Generates a one time upgrade token, and emails admins with the token info.
     */
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

    /**
     * Sets/Deletes data in the session to let us know we needed to run the snowflake migration
     * @param boolean Delete the seeion if defined
     */
    public function snowflakeSession($value = false, $delete = false) {
        $config = Config::getInstance();
        $app_path = $config->getValue('source_root_path');
        $key = 'runnig_snowflake_uprade';
        if($delete) {
            if(isset( $_SESSION[$app_path][$key] )) {
                unset($_SESSION[$app_path][$key]);
            }
        } else {
            if($value) {
                $_SESSION[$app_path][$key] = $value;
            } else {
                if(isset($_SESSION[$app_path]) && isset($_SESSION[$app_path][$key]) ) {
                    return $_SESSION[$app_path][$key];
                } else {
                    return false;
                }
            }
        }
    }
}