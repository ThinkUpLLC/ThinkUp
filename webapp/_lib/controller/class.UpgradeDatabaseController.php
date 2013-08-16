<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.UpgradeDatabaseController.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie
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
 * Upgrade Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 * Upgrade Controller
 *
 * Checks the current app version with a DB version option stored in the db. If that version option
 * does not exist, or it is older than the current version, we flag the app as in need of migration
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
class UpgradeDatabaseController extends ThinkUpAuthController {

    /**
     * First auto updatable migration
     */
    const RUNNABLE_MIGRATION_MIN = '0.2';

    /**
     * SQL migration directory
     */
    const MIGRATION_DIR = 'install/sql/mysql_migrations';

    /**
     * token key
     */
    const TOKEN_KEY = 'a_token_key';

    /**
     * max table rows before we warn users to use the CLI upgrade interface
     */
    static $WARN_TABLE_ROW_COUNT = 500000;

    /**
     * Constructor
     * @param bool $session_started
     * @return UpgradeDatabaseController
     */
    public function __construct($session_started=false) {
        if (!getenv('CLI_BACKUP')) {
            parent::__construct($session_started);
        }
    }

    public function authControl() {
        $this->disableCaching();
        $config = Config::getInstance();
        $thinkup_db_version = $config->getValue('THINKUP_VERSION');

        $install_dao = DAOFactory::getDAO('InstallerDAO');
        $db_version = self::getCurrentDBVersion($cached = false);
        $option_dao = DAOFactory::getDAO('OptionDAO');

        // clear options session data
        $option_dao->clearSessionData(OptionDAO::APP_OPTIONS);

        if (isset($_GET['migration_index'])) {
            $migrations = $this->getMigrationList($db_version);
            $migration_index = $_GET['migration_index'] - 1;
            $migrations = $this->getMigrationList($db_version);
            $processed = false;
            $sql = $migrations[$migration_index]['sql'];
            $new_migration = $migrations[$migration_index]['new_migration'];
            $migration_file_name = $migrations[$migration_index]['filename'];
            // remove comments...
            $sql = preg_replace('/\-\-.*/','', $sql);

            try {
                $install_dao->runMigrationSQL($sql, $new_migration, $migration_file_name);
                $processed = true;
                $this->setJsonData( array( 'processed'=>$processed, 'sql'=>$sql));
            } catch(Exception $e) {
                $this->setJsonData( array( 'processed'=>$processed, 'message'=>$e->getMessage(), 'sql'=>$sql));
            }
        } else if (isset($_GET['migration_done'])) {
            $option = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, 'database_version');
            if ($option) {
                $option_dao->updateOptionByName(OptionDAO::APP_OPTIONS, 'database_version', $thinkup_db_version);
            } else {
                $option_dao->insertOption(OptionDAO::APP_OPTIONS, 'database_version', $thinkup_db_version);
            }
            $this->setJsonData(array('migration_complete' => true) );
            $this->deleteTokenFile();
            // remove snowflake in progress session if needed
            $this->setSnowflakeSession($value=false, $delete=true);
            // Clear compiled view files and cache
            $this->view_mgr->clear_compiled_tpl();
            $this->view_mgr->clear_all_cache();
        } else {
            $this->setPageTitle('Upgrade the ThinkUp Database Structure');
            $this->setViewTemplate('install.upgrade-database.tpl');
            if (version_compare($db_version, $thinkup_db_version, '<')) {
                // get migrations we need to run...
                $migrations = $this->getMigrationList($db_version);
                $this->addToView('migrations',$migrations);
                $this->addToView('migrations_json', json_encode($migrations));
                if (isset($_GET['upgrade_token'])) {
                    $this->addToView('upgrade_token', $_GET['upgrade_token']);
                }
                // no migrations needed, just update the application db version option to reflect
                if (count($migrations) == 0) {
                    $option = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, 'database_version');
                    if ($option) {
                        $option_dao->updateOptionByName(OptionDAO::APP_OPTIONS, 'database_version',
                        $thinkup_db_version);
                    } else {
                        $option_dao->insertOption(OptionDAO::APP_OPTIONS, 'database_version', $thinkup_db_version);
                    }
                    $this->addToView('version_updated', true);
                    $this->addToView('thinkup_db_version', $thinkup_db_version);
                    $this->deleteTokenFile();
                } else {
                    // pass the count of the table with  the most records
                    $table_stats_dao = DAOFactory::getDAO('TableStatsDAO');
                    $table_counts = $table_stats_dao->getTableRowCounts();
                    if ($table_counts[0]['count'] > self::$WARN_TABLE_ROW_COUNT) {
                        $this->addToView('high_table_row_count',$table_counts[0]);
                    }
                }
            }
        }
        return $this->generateView();
    }
    /**
     * Delete token file if it exists
     */
    public function deleteTokenFile() {
        $file = FileDataManager::getDataPath('.htupgrade_token');
        if (file_exists($file)) {
            unlink($file);
        }
    }
    /**
     * Determine if ThinkUp needs to show the upgrading page.
     * @param bool Is the current user an admin
     * @param str The calling classname
     * @return bool Whether or not we need to show the upgrade page
     */
    public static function isUpgrading($is_admin, $class_name) {
        $config = Config::getInstance();
        $status = false;
        $db_version = UpgradeDatabaseController::getCurrentDBVersion($config->getValue( 'cache_pages' ));
        if (version_compare($db_version, $config->getValue('THINKUP_VERSION'), '<') ) {
            if ( $class_name != 'UpgradeDatabaseController' ) {
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
    /**
     * Override control to allow a user to auth with an upgrade token if needed
     */
    public function control() {
        if ($this->isAdmin()) {
            return $this->authControl();
        } else if (isset($_GET['upgrade_token'])) {
            if ($this->isTokenAuth($_GET['upgrade_token'])) {
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
        $token_file = FileDataManager::getDataPath('.htupgrade_token');
        $status = false;
        if (file_exists($token_file)) {
            $file_token = file_get_contents($token_file);
            if ($file_token == $query_token) {
                $status = true;
            }
        }
        return $status;
    }
    /**
     * Returns the current db version
     * @param bool $cached
     * @return float current DB version
     */
    public static function getCurrentDBVersion($cached) {
        $option_dao = DAOFactory::getDAO('OptionDAO');
        $db_version = self::RUNNABLE_MIGRATION_MIN;
        $has_options_table = $option_dao->isOptionsTable();
        if ($has_options_table) {
            $db_version = $option_dao->getOptionValue(OptionDAO::APP_OPTIONS, 'database_version', $cached);
            if ( !$db_version) {
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
    public function getMigrationList($version, $no_version = false) {
        $dir = THINKUP_WEBAPP_PATH . self::MIGRATION_DIR;
        $config = Config::getInstance();
        $table_prefix = $config->getValue('table_prefix');
        // Next line doesn't work on Travis-CI
        //        $dir_list = glob('{' . $dir . '/*.sql,' . $dir . '/*.migration}', GLOB_BRACE);
        $dir_list_1 = glob($dir . '/*.sql', GLOB_BRACE);
        $dir_list_2 = glob($dir . '/*.migration', GLOB_BRACE);
        $dir_list = array_merge($dir_list_1, $dir_list_2);

        $migrations = array();
        for ($i = 0; $i < count($dir_list); $i++) {
            if (preg_match('/_v(\d+\.\d+(\.\d+)?(\w+)?)\.sql(\.migration)?/', $dir_list[$i], $matches)
            //TODO combine these into a single regex
            || preg_match('/_v(\d+\.\d+(-beta\.\d+)?(\w+)?)\.sql(\.migration)?/', $dir_list[$i], $matches)) {
                $migration_version = $matches[1];
                // skip early pre beta 1 versions...
                if (preg_match('/^0\.00/', $migration_version)) {
                    continue;
                }
                if (version_compare($migration_version, $version) > 0 &&
                version_compare($migration_version, $config->getValue('THINKUP_VERSION')) < 1 ) {
                    if ($migration_version == 0.3) {
                        $install_dao = DAOFactory::getDAO('InstallerDAO');
                        if ( !$install_dao->needsSnowflakeUpgrade() && !$this->setSnowflakeSession($value=false) ) {
                            continue;
                        } else {
                            // set snowflake in progress session
                            $this->setSnowflakeSession($value=true, $delete=false);
                        }
                    }
                    $migration_string = file_get_contents($dir_list[$i]);
                    if (!$migration_string) {
                        throw new OpenFileException("Unable to open file: " + $dir_list[$i]);
                    } else {
                        // check for modified prefix
                        if ($table_prefix != 'tu_') {
                            $migration_string = str_replace('tu_', $table_prefix, $migration_string);
                        }
                        $path_info = pathinfo($dir_list[$i]);
                        $migration =
                        array("version" =>  $migration_version, 'sql'  => $migration_string, 'new_migration' => true,
                        'filename' => $path_info['basename']);
                        array_push($migrations, $migration);
                    }
                }
            }
        }
        // add non-versioned sql if running via command line and no version arg '--with-new-sql'
        if ($no_version) {
            foreach ($dir_list as $file) {
                if (!preg_match('/_v(\d+\.\d+(\.\d+)?(\w+)?)\.sql(\.migration)?/', $file)
                && preg_match("/\.sql$/", $file) ) {

                    //No version in filename
                    if (!preg_match('/_v(\d+\.\d+(\.\d+)?(\w+)?)\.sql(\.migration)?/', $file, $matches)
                    //TODO combine these into a single regex
                    && !preg_match('/_v(\d+\.\d+(-beta\.\d+)?(\w+)?)\.sql(\.migration)?/', $file, $matches)) {
                        $migration_string = file_get_contents($file);

                        // check for modified prefix
                        if ($table_prefix != 'tu_') {
                            $migration_string = str_replace('tu_', $table_prefix, $migration_string);
                        }
                        $path_info = pathinfo($file);
                        $migration =
                        array("version" =>  $migration_version, 'sql'  => $migration_string, 'new_migration' => false,
                        'filename' => $path_info['basename'], 'new_migration' => true);
                        array_push($migrations, $migration);
                    }
                }
            }
        }
        usort($migrations, 'UpgradeDatabaseController::migrationDateSort');
        return $migrations;
    }
    /**
     * Generates a one time upgrade token, and emails admins with the token info.
     */
    public static function generateUpgradeToken() {
        $token_file = FileDataManager::getDataPath('.htupgrade_token');
        $md5_token = '';
        if (!file_exists($token_file)) {
            $fp = fopen($token_file, 'w');
            if ($fp) {
                $token = self::TOKEN_KEY . rand(0, time());
                $md5_token = md5($token);
                if (!fwrite($fp, $md5_token)) {
                    throw new OpenFileException("Unable to write upgrade token file: " + $token_file);
                }
                fclose($fp);
            } else {
                throw new OpenFileException("Unable to create upgrade token file: " + $token_file);
            }
            // email our admin with this token.
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $admins = $owner_dao->getAdmins();
            if ($admins) {
                $tos = array();
                foreach($admins as $admin) {
                    $tos[] = $admin->email;
                }
                $to = join(',', $tos);
                $upgrade_email = new ViewManager();
                $upgrade_email->caching=false;
                $upgrade_email->assign('application_url', Utils::getApplicationURL(false) );
                $upgrade_email->assign('token', $md5_token );
                $message = $upgrade_email->fetch('_email.upgradetoken.tpl');
                $config = Config::getInstance();
                Mailer::mail($to, "Upgrade Your ThinkUp Database", $message);
            }
        }
    }
    /**
     * Sets/deletes in the session to let us know we needed to run the Snowflake migration.
     * @param bool $delete Delete the session if true
     * @param mixed $value Session value, defaults to false
     * @return mixed Boolean true if successful, else contents of session key
     */
    public function setSnowflakeSession($value=false, $delete=false) {
        $key = 'runnig_snowflake_uprade';
        if ($delete) {
            if ( SessionCache::isKeySet($key) ) {
                SessionCache::unsetKey($key);
                return true;
            }
        } else {
            if ($value) {
                SessionCache::put($key, $value);
                return true;
            } else {
                if ( SessionCache::isKeySet($key) ) {
                    return SessionCache::get($key);
                } else {
                    return false;
                }
            }
        }
        return false;
    }
    /**
     * To sort migrations by key
     * For PHP 5.2 compatibility, this method must be public so that we can call usort($migrations,
     * 'UpgradeDatabaseController::migrationDateSort')
     * private/self::migrationDateSort doesn't work in PHP 5.2
     */
    public static function migrationDateSort($a,$b) {
        return strtolower($a['filename']) > strtolower($b['filename']);
    }
}
