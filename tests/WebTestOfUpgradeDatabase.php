<?php
/**
 *
 * ThinkUp/tests/classes/WebTestOfUpgradeDatabase.php
 *
 * Copyright (c) 2009-2010 Mark Wilkie
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
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Mark Wilkie
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

class WebTestOfUpgradeDatabase extends ThinkUpBasicWebTestCase {

    public function setUp() {
        date_default_timezone_set('America/Los_Angeles');

        parent::setUp();

        $optiondao = new OptionMySQLDAO();
        $this->pdo = OptionMysqlDAO::$PDO;

        $this->install_dir = THINKUP_ROOT_PATH.'webapp/test_installer';
        $this->installs_dir = THINKUP_ROOT_PATH.'build';
        // Make sure test_installer and build directories exists
        if (!file_exists($this->install_dir)) {
            exec('mkdir ' . $this->install_dir);
        }
        if (!file_exists($this->installs_dir)) {
            exec('mkdir ' . $this->installs_dir);
        }

        //Clean up files from test installation
        exec('rm -rf ' . THINKUP_ROOT_PATH.'webapp/test_installer' . '/*');
    }

    public function tearDown() {
        //Clean up test installation files
        exec('rm -rf ' . THINKUP_ROOT_PATH.'webapp/test_installer' . '/*');

        // Delete test database created during installation process
        require THINKUP_WEBAPP_PATH.'config.inc.php';
        global $TEST_DATABASE;

        //Override default CFG values
        $THINKUP_CFG['db_name'] = $TEST_DATABASE;
        $this->db = new Database($THINKUP_CFG);
        $this->conn = $this->db->getConnection();
        $this->testdb_helper = new ThinkUpTestDatabaseHelper();
        $this->testdb_helper->drop($this->db);
        $this->db->closeConnection($this->conn);

        parent::tearDown();
    }

    public function testMigrations() {
        require THINKUP_WEBAPP_PATH.'config.inc.php';
        global $TEST_DATABASE;

        //install beta 1
        $zipfile = $this->getIntsall('http://github.com/downloads/ginatrapani/ThinkUp/thinkup-0.1.zip',
        '0.1', $this->installs_dir);

        //Extract into test_installer directory and set necessary folder permissions
        exec('cp ' . $zipfile .  ' webapp/test_installer/.;'.
        'cd webapp/test_installer/;'.
        'unzip ' . $zipfile . ';chmod -R 777 thinkup');

        //Config file doesn't exist
        $this->assertFalse(file_exists($THINKUP_CFG['source_root_path'].
        'webapp/test_installer/thinkup/config.inc.php'));

        //Start installation process
        $this->get($this->url.'/test_installer/thinkup/');
        $this->assertTitle("ThinkUp");
        $this->assertText('ThinkUp\'s configuration file does not exist! Try installing ThinkUp.');
        $this->clickLink("installing ThinkUp.");
        $this->assertText('Great! Your system has everything it needs to run ThinkUp. You may proceed to the next '.
        'step.');
        $this->get('index.php?step=2');
        //$this->showSource();
        $this->assertText('Create Your ThinkUp Account');

        $this->setField('full_name', 'ThinkUp J. User');
        $this->setField('site_email', 'user@example.com');
        $this->setField('password', 'secret');
        $this->setField('confirm_password', 'secret');
        $this->setField('timezone', 'America/Los_Angeles');

        $this->setField('db_host', $THINKUP_CFG['db_host']);
        $this->setField('db_name', $TEST_DATABASE);
        $this->setField('db_user', $THINKUP_CFG['db_user']);
        $this->setField('db_passwd', $THINKUP_CFG['db_password']);
        $this->setField('db_socket', $THINKUP_CFG['db_socket']);
        $this->clickSubmitByName('Submit');

        $this->assertText('ThinkUp has been installed successfully. Check your email account; an account activation '.
        'message has been sent.');

        //Config file has been written
        $this->assertTrue(file_exists($THINKUP_CFG['source_root_path'].
        'webapp/test_installer/thinkup/config.inc.php'));

        //Test bad activation code
        $this->get($this->url.'/test_installer/thinkup/session/activate.php?usr=user@example.com&code=dummycode');
        $this->assertText('Houston, we have a problem: Account activation failed.');

        //Get activation code for user from database
        date_default_timezone_set('America/Los_Angeles');
        $owner_dao = new OwnerMySQLDAO();
        $code = $owner_dao->getActivationCode('user@example.com');
        $activation_code = $code['activation_code'];

        //Visit activation page
        $this->get($this->url.'/test_installer/thinkup/session/activate.php?usr=user@example.com&code='.
        $activation_code);
        $this->assertNoText('Houston, we have a problem: Account activation failed.');
        $this->assertText('Success! Your account has been activated. Please log in.');

        //Log into ThinkUp
        $this->clickLink('Log in');

        $this->setField('email', 'user@example.com');
        $this->setField('pwd', 'secret');
        $this->click("Log In");
        $this->assertText('You have no accounts configured. Set up an account now');

        //Visit Configuration page and assert content there
        $this->click("Configuration");
        $this->assertTitle('Configure Your Account | ThinkUp');
        $this->assertText('As an administrator you can configure all installed plugins.');

        // run updates and migrations
        require 'tests/migration-assertions.php';

        // build latest  version for testing
        $migration_sql_dir = THINKUP_ROOT_PATH . 'webapp/install/sql/mysql_migrations/';
        $latest_migration_file = false;
        $config = Config::getInstance();

        $current_version = $config->getValue('THINKUP_VERSION');
        if($LATEST_VERSION == $current_version) {
            if($this->DEBUG) {
                $this->debug("Building  build for latest version: $LATEST_VERSION");
            }
            $sql_files = glob($migration_sql_dir . '*.sql');
            if (sizeof($sql_files) > 0) {
                $this->debug("found sql update for lasest version $LATEST_VERSION: $sql_files[0]");
                $latest_migration = glob($migration_sql_dir . '*_v' . $LATEST_VERSION .'.sql.migration');
                if(! isset($latest_migration[0])) {
                    $latest_migration_file = $migration_sql_dir . '0001-01-01_v' . $LATEST_VERSION .'.sql.migration';
                    $fp = fopen($latest_migration_file, 'w');
                    $sql_files = glob($migration_sql_dir . '*.sql');
                    $sql_file = $sql_files[0];
                    $sql_migration = file_get_contents($sql_file);
                    fwrite($fp, " -- migration file " . $sql_file . "\n\n");
                    fwrite($fp, $sql_migration);
                    fwrite($fp, "\n\n--");
                    fclose($fp);
                }
            }
            exec('extras/scripts/generate-distribution');
            exec('cp build/thinkup.zip build/' . $LATEST_VERSION . '.zip');
        }

        foreach($MIGRATIONS as  $version => $migration_data) {
            $this->debug("Running migration test for version: $version");
            $url = $migration_data['zip_url'];
            $zipfile = $this->getIntsall($url, $version, $this->installs_dir);
            if(! $zipfile) {
                error_log("Warn: $zipfile not found...");
                continue;
            }
            //Extract into test_installer directory and set necessary folder permissions
            exec('cp ' . $zipfile .  ' webapp/test_installer/.;'.
            'cd webapp/test_installer/;unzip -o ' . $zipfile);

            // update version php file
            if($version == $LATEST_VERSION) {
                $version_file = $this->install_dir . '/thinkup/install/version.php';
                $version_php = file_get_contents($version_file);
                $version_php =
                preg_replace("/THINKUP_VERSION =.*;/", "THINKUP_VERSION = $LATEST_VERSION;", $version_php);
                $fp = fopen($version_file, 'w');
                fwrite($fp, $version_php);
                fclose($fp);
            }

            // no web based upgrade mode until version 4
            if($version > 0.3) {
                $this->get($this->url.'/test_installer/thinkup/');
                $this->assertText("ThinkUp's database needs an update");
                $file_token = file_get_contents($this->install_dir.'/thinkup/_lib/view/compiled_view/upgrade_token');
                $token_url = $this->url.'/test_installer/thinkup/install/upgrade.php?upgrade_token=' . $file_token;
                $this->get($token_url);
                for($i = 1; $i <= $migration_data['migrations']; $i++) {
                    $this->get($token_url . "&migration_index=" . $i);
                }
                $this->get($token_url . '&migration_done=true');
                $this->assertText('{"migration_complete":true}');
                $this->get($this->url.'/test_installer/thinkup/');
                $this->assertText('Logged in as: user@example.com');
            } else {
                $dir = $this->install_dir . '/thinkup/install/sql/mysql_migrations';
                $dir_list = glob($dir . '/*.migration');
                for ($i = 0; $i < count($dir_list); $i++) {
                    $mig_match = '/_v' . $version .'\.sql\.migration/';
                    if(preg_match($mig_match, $dir_list[$i], $matches)) {
                        $migration_string = file_get_contents($dir_list[$i]);
                        $this->pdo->query($migration_string);
                    }
                }
            }
            // run db migration tests
            foreach($migration_data['migration_assertions'] as $assertions) {
                foreach($assertions as $assertion) {
                    $stmt = $this->pdo->query($assertion['query']);
                    $data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $this->assertEqual(preg_match($assertion['match'], $data[ $assertion['column'] ]), 1,
                    $assertion['match'] . ' should match ' .  $data[ $assertion['column'] ]);
                    $stmt->closeCursor();
                }
            }
        }
        if($latest_migration_file && file_exists($latest_migration_file)) {
            unlink( $latest_migration_file );
        }
    }

    /**
     * Downloads install/upgrade zip file if needed, returns path to zip file.
     * @param str Url
     * @param str Version
     * @return str Path to download file
     */
    public function getIntsall($url, $version, $path) {
        $ch = curl_init();
        $zipfile = $path . '/' . $version . '.zip';
        if(! file_exists($zipfile)) {
            $ch = curl_init($url);//Here is the file we are downloading
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $data = curl_exec($ch);
            file_put_contents($zipfile, $data);
        }
        return $zipfile;
    }
}