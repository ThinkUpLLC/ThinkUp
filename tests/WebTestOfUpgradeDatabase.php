<?php
/**
 *
 * ThinkUp/tests/classes/WebTestOfUpgradeDatabase.php
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
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

class WebTestOfUpgradeDatabase extends ThinkUpBasicWebTestCase {

    /**
     * Timeout for downloading old thinkup version .zip files from GitHub
     */
    const FILE_DOWNLOAD_TIMEOUT = 60;

    public function setUp() {
        Utils::setDefaultTimezonePHPini();
        parent::setUp();

        $optiondao = new OptionMySQLDAO();
        $this->pdo = OptionMySQLDAO::$PDO;

        $this->install_dir = THINKUP_WEBAPP_PATH.'test_installer';
        $this->installs_dir = THINKUP_ROOT_PATH.'build';
        // Make sure test_installer and build directories exists
        if (!file_exists($this->install_dir)) {
            exec('mkdir ' . $this->install_dir);
            exec('chmod -R 777 '.$this->install_dir);
        }
        if (!file_exists($this->installs_dir)) {
            exec('mkdir ' . $this->installs_dir);
            exec('chmod -R 777 '.$this->install_dir);
        }

        $config = Config::getInstance();
        $this->table_prefix = $config->getValue('table_prefix');

        // in case we exit without a teardown..
        $this->tearDown();
        $this->restart();
        $this->latest_build_made = false; //so we only create th elatest build zip once...
    }

    public function tearDown() {
        //Clean up test installation files
        exec('rm -rf ' . THINKUP_WEBAPP_PATH.'test_installer/*' );

        // Delete test database created during installation process
        require THINKUP_WEBAPP_PATH.'config.inc.php';

        //Override default CFG values
        $THINKUP_CFG['db_name'] = $this->test_database_name;
        $this->testdb_helper = new ThinkUpTestDatabaseHelper();
        $this->testdb_helper->drop($this->test_database_name);

        parent::tearDown();
    }

    public function testMigrations() {
        // run updates and migrations
        require dirname(__FILE__) . '/migration-assertions.php';
        $migrations_count = count($MIGRATIONS);
        $migration_versions = array();
        foreach($MIGRATIONS as  $version => $migration_data) {
            array_push($migration_versions, $version);
        }
        $migration_max_index = $migrations_count-1;
        for($i = 0; $i < $migrations_count-1; $i++) {
            $run_migrations = array($migration_versions[$migration_max_index] =>
            $MIGRATIONS[ $migration_versions[$migration_max_index] ]);
            $this->debug("Testing migration " . $migration_versions[$i] . " => "
            . $migration_versions[$migration_max_index] );
            $data = $this->setUpApp($migration_versions[$i], $MIGRATIONS);
            $this->runMigrations($run_migrations, $migration_versions[$i]);
            if ($data['latest_migration_file'] && file_exists($data['latest_migration_file'])) {
                unlink( $data['latest_migration_file'] );
            }
            $this->tearDown();
            $this->restart();
            $this->debug("Done Testing migration " . $migration_versions[$i] . " => "
            . $migration_versions[$migration_max_index] );
            $this->debug("");
        }
        // then test a migration from 4 that needs a snowflake uprade
        $this->debug("Testing snowflake migration/update");
        $run_migrations = array($migration_versions[$migration_max_index] =>
        $MIGRATIONS[ $migration_versions[$migration_max_index] ]);
        $this->debug("Testing migration " . $migration_versions[2] . " => ".$migration_versions[$migration_max_index]);
        $data = $this->setUpApp($migration_versions[2], $MIGRATIONS);
        $this->pdo->query('ALTER TABLE ' . $this->table_prefix .
        'instances CHANGE last_post_id last_status_id bigint(11) NOT NULL');
        $this->runMigrations($run_migrations, $migration_versions[$i]);
        if ($data['latest_migration_file'] && file_exists($data['latest_migration_file'])) {
            unlink( $data['latest_migration_file'] );
        }
        $this->debug("Done Testing snowflake migration/update");

    }

    public function  testFailAndRerunMigration() {
        // test fail and rerun
        $this->debug("");
        $this->debug("Testing fail and rollback rerun for migration 0.10");
        require 'tests/migration-assertions.php';
        $MIGRATIONS = array('0.9' => $MIGRATIONS['0.9'], $LATEST_VERSION => $MIGRATIONS[$LATEST_VERSION]);
        $run_migrations = array($LATEST_VERSION => $MIGRATIONS[$LATEST_VERSION]);
        $data = $this->setUpApp('0.9', $MIGRATIONS);
        $this->runMigrations($run_migrations, '0.9', $fail_10 = 1);

        $stmt = $this->pdo->query("select * from " . $this->table_prefix . "completed_migrations");
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 1);
        $this->assertEqual($data[0]['migration'], '2011-04-19-0');
        $this->assertPattern('/CREATE TABLE ' . $this->table_prefix . 'follows_b10/',$data[0]['sql_ran']);
        $this->runMigrations($run_migrations, '0.9', $fail_10 = 2);

        $stmt = $this->pdo->query("select * from " . $this->table_prefix .
        "completed_migrations where migration like '2011-04-19%'");
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 73);

        $stmt = $this->pdo->query("select * from " . $this->table_prefix . "completed_migrations");
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), $TOTAL_MIGRATION_COUNT);
    }

    /**
     * Sets up initial app
     */
    private function setUpApp($version, $MIGRATIONS) {
        // run updates and migrations
        require dirname(__FILE__) . '/migration-assertions.php';
        $this->debug("Setting up base install for upgrade: $version");
        $this->travisHeartbeat();
        $zip_url = $MIGRATIONS[$version]['zip_url'];

        require THINKUP_WEBAPP_PATH.'config.inc.php';
        //install beta 1
        $zipfile = $this->getInstall($zip_url, $version, $this->installs_dir);
        //Extract into test_installer directory and set necessary folder permissions
        chdir(dirname(__FILE__) . '/../');
        exec('cp ' . $zipfile .  ' webapp/test_installer/.;'.
        'cd webapp/test_installer/;'.'unzip ' . $zipfile .';chmod -R 777 thinkup;');
        if (!file_exists($this->install_dir.'/thinkup/data/compiled_view')) {
            chdir(dirname(__FILE__) . '/../');
            if (!file_exists($this->install_dir.'/thinkup/data')) {
                exec('cd webapp/test_installer/;mkdir thinkup/data;');
            }
            exec('cd webapp/test_installer/;mkdir thinkup/data/compiled_view;chmod -R 777 thinkup;');
        }
        if (file_exists($this->install_dir.'/thinkup/_lib/view/compiled_view')) {
            chdir(dirname(__FILE__) . '/../');
            exec('cd webapp/test_installer/;chmod -R 777 thinkup/_lib/view/compiled_view;');
        }
        $this->travisSleepTest();

        //Config file doesn't exist
        $this->assertFalse(file_exists($THINKUP_CFG['source_root_path'].
        'webapp/test_installer/thinkup/config.inc.php'));


        //Set test mode
        $this->get($this->url.'/test_installer/thinkup/install/setmode.php?m=tests');
        //Include config again to get test db credentials
        require THINKUP_WEBAPP_PATH.'config.inc.php';
        //$this->showText();

        //Start installation process
        $this->get($this->url.'/test_installer/thinkup/');
        $this->assertTitle("ThinkUp");
        $this->assertText('ThinkUp\'s configuration file does not exist! Try installing ThinkUp.');
        $this->clickLink("installing ThinkUp.");
        $this->assertText('Great! Your system has everything it needs to run ThinkUp.');
        //Set test mode
        putenv("MODE=TESTS");
        //Include config again to get test db credentials
        require THINKUP_WEBAPP_PATH.'config.inc.php';

        $this->get('index.php?step=2');
        if (version_compare($version, '2.0-beta.5', '>')) {
            $this->debug("On step 2 of install for ".$version);
            if ($version == '2.0-beta.5') {
                $this->showSource();
            }
            $this->assertText('Create your ThinkUp account');
        } else {
            $this->assertText('Create Your ThinkUp Account');
        }

        $this->setField('full_name', 'ThinkUp J. User');
        $this->setField('site_email', 'user@example.com');
        $this->setField('password', 'secret123');
        $this->setField('confirm_password', 'secret123');
        $this->setField('timezone', 'America/Los_Angeles');

        $this->setField('db_host', $THINKUP_CFG['db_host']);
        $this->setField('db_name', $THINKUP_CFG['db_name']);
        $this->setField('db_user', $THINKUP_CFG['db_user']);
        $this->setField('db_passwd', $THINKUP_CFG['db_password']);
        $this->setField('db_socket', $THINKUP_CFG['db_socket']);
        $this->setField('db_prefix', $THINKUP_CFG['table_prefix']);
        $this->clickSubmitByName('Submit');

        $this->assertText('ThinkUp has been installed successfully. Check your email account; an account activation '.
        'message has been sent.');
        //Config file has been written
        $this->assertTrue(file_exists($THINKUP_CFG['source_root_path'].
          '/webapp/test_installer/thinkup/config.inc.php'));

        //Get activation code for user from database
        Utils::setDefaultTimezonePHPini();
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
        $this->setField('pwd', 'secret123');
        $this->click("Log In");
        if (version_compare($version, '1.5', '>=')) {
            $this->assertText('Set up a Twitter, Facebook, Google+, or Foursquare account');
        } elseif (version_compare($version, '0.17', '>=')) {
            $this->assertText('Add a Twitter Account');
            $this->assertText('Add a Facebook Account');
            $this->assertText('Add a Google+ Account');
            $this->assertText('Adjust Your Settings');
        }
        if (version_compare($version, '0.16', '>=')) {
            $this->assertText('Welcome to ThinkUp');
        } else {
            $this->assertText('You have no'); //accounts/services configured. Set up an account now');
            $this->assertText('Set up'); //an account/a service like Twitter or Facebook now
        }
        //Visit Configuration/Settings page and assert content there
        if (version_compare($version, '0.6', '>=')) {
            $this->click("Settings"); //link name changed in beta 6
            $this->debug("Clicked Settings");
        } else {
            $this->click("Configuration");
            $this->debug("Clicked Configuration");
        }
        $config = Config::getInstance();
        $this->assertTitle('Configure Your Account | ThinkUp');

        // run updates and migrations
        require dirname(__FILE__) . '/migration-assertions.php';

        // build latest  version for testing
        $migration_sql_dir = THINKUP_WEBAPP_PATH.'install/sql/mysql_migrations/';
        $latest_migration_file = false;

        $current_version = $config->getValue('THINKUP_VERSION');
        $latest_migration = glob($migration_sql_dir . '*_v' . $LATEST_VERSION .'.sql.migration');
        if ($LATEST_VERSION == $current_version && $this->latest_build_made == false) {
            $this->debug("Building zip for latest version: $LATEST_VERSION");
            exec('extras/scripts/generate-distribution');
            exec('cp build/thinkup.zip build/' . $LATEST_VERSION . '.zip');
            if (file_exists($latest_migration_file)) {
                unlink( $latest_migration_file );
            }
            $this->latest_build_made = true;
        }
        return array('MIGRATIONS' => $MIGRATIONS, 'latest_migration_file'  => $latest_migration_file );
    }
    /**
     * Runs migrations list
     */
    private function runMigrations($TMIGRATIONS, $base_version, $fail = false) {
        require dirname(__FILE__) . '/migration-assertions.php';
        require THINKUP_WEBAPP_PATH.'config.inc.php';
        foreach($TMIGRATIONS as  $version => $migration_data) {
            $this->debug("Running migration test for version: $version");
            $this->travisHeartbeat();
            $url = $migration_data['zip_url'];
            $zipfile = $this->getInstall($url, $version, $this->installs_dir);
            $this->debug("unzipping $zipfile");
            chdir(dirname(__FILE__) . '/../');
            //Extract into test_installer directory and set necessary folder permissions
            exec('cp ' . $zipfile .  ' webapp/test_installer/.;cd webapp/test_installer/;'.
            'rm -rf thinkup/_lib;rm -rf thinkup/plugins;unzip -o ' . $zipfile.';');
            $this->travisSleepTest();
            if (!file_exists($this->install_dir.'/thinkup/data/compiled_view')) {
                if (!file_exists($this->install_dir.'/thinkup/data')) {
                    exec('mkdir thinkup/data;');
                }
                exec('mkdir thinkup/data/compiled_view;chmod -R 777 thinkup');
            }
            // run updates and migrations
            require dirname(__FILE__) . '/migration-assertions.php';

            // update version php file
            if ($version == $LATEST_VERSION) {
                $version_file = $this->install_dir . '/thinkup/install/version.php';
                $version_php = file_get_contents($version_file);
                $version_php =
                preg_replace("/THINKUP_VERSION =.*;/", "THINKUP_VERSION = '$LATEST_VERSION';", $version_php);
                $fp = fopen($version_file, 'w');
                fwrite($fp, $version_php);
                fclose($fp);
            }

            // if we want to break a sql migration for testing
            if ($fail && $fail == 1 ) {
                $this->debug("Munging migration v0.10 sql for fail testing");
                $migration_10 = $this->install_dir
                . '/thinkup/install/sql/mysql_migrations/2011-04-19_v0.10.sql.migration';
                $msql = file_get_contents($migration_10);
                $msql = preg_replace('/INSERT INTO tu_follows_b10 \(SELECT/',
                'INSERT INTO tu_follows_b10 (SELECTs', $msql);
                file_put_contents($migration_10, $msql);
            } else if ($fail && $fail == 2 ) {
                $this->debug("Fixing migration v0.10 sql for fail recovery testing");
                $migration_10 = $this->install_dir
                . '/thinkup/install/sql/mysql_migrations/2011-04-19_v0.10.sql.migration';
                $msql = file_get_contents($migration_10);
                $msql = preg_replace('/INSERT INTO tu_follows_b10 \(SELECTs/',
                'INSERT INTO tu_follows_b10 (SELECT', $msql);
                file_put_contents($migration_10, $msql);
            }
            if (getenv('TRAVIS')=='true') {
                sleep(30);
            }
            $this->get($this->url.'/test_installer/thinkup/');
            $this->assertText("ThinkUp's database needs an upgrade");
            // token could be in 1 of 2 places, depending on what version is running
            if (file_exists($this->install_dir.'/thinkup/_lib/view/compiled_view/.htupgrade_token') ) {
                $file_token = file_get_contents($this->install_dir.'/thinkup/_lib/view/compiled_view/.htupgrade_token');
            } else {
                $file_token = file_get_contents($this->install_dir.'/thinkup/data/.htupgrade_token');
            }
            $token_url = $this->url.'/test_installer/thinkup/install/upgrade.php?upgrade_token=' . $file_token;
            $this->get($token_url);
            $content = $this->getBrowser()->getContent();
            //$this->debug($content);
            preg_match("/sql_array = (\[.*?}])/", $content, $matches);
            if (isset($matches[1])) {
                $json_array = json_decode($matches[1]);
            }
            //$this->debug(Utils::varDumpToString($json_array));
            $cnt = 0;
            if (isset($json_array)) {
                foreach($json_array as $json_migration) {
                    $this->debug("Running migration: " . $json_migration->version);

                    // if there is setup_sql run it
                    if (isset($MIGRATIONS[$json_migration->version ]['setup_sql'])) {
                        $this->debug('Running setup_sql scripts');
                        $install_dao = DAOFactory::getDAO('InstallerDAO');
                        foreach($MIGRATIONS[$json_migration->version ]['setup_sql'] as $sql) {
                            $this->debug('Running setup_sql script: ' . substr($sql, 0, 40)  . '...');
                            $install_dao->runMigrationSQL($sql);
                        }
                    }
                    $cnt++;
                    $this->get($token_url . "&migration_index=" . $cnt);
                    if ($fail && $fail == 1) {
                        $this->assertText('{ "processed":false,');
                        $this->assertText('ThinkUp could not execute the following query: ' .
                        'INSERT INTO tu_follows_b10 (SELECTs');
                        return;
                    }
                    $this->assertText('{ "processed":true,');
                    $content = $this->getBrowser()->getContent();
                    if (!preg_match('/"processed":true/', $content)) {
                        $this->debug('ERROR: '.$content);
                        return;
                    }
                    if (isset($json_array[$cnt]) && $json_array[$cnt]->version == $json_migration->version) {
                        continue;
                    }
                    $this->debug("Running migration assertion test for " . $json_migration->version);
                    if ( !isset($MIGRATIONS[ $json_migration->version ])) { continue; } // no assertions, so skip
                    $assertions = $MIGRATIONS[ $json_migration->version ];
                    foreach($assertions['migration_assertions']['sql'] as $assertion_sql) {
                        // don't run the database_version assertion if it exists, this will get run below...
                        if (preg_match("/database_version/i", $assertion_sql['query'])) {
                            continue;
                        }
                        $this->debug("Running assertion sql: " . $assertion_sql['query']);
                        $stmt = $this->pdo->query($assertion_sql['query']);
                        $data = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (isset($assertion_sql['no_match'])) {
                            $this->assertFalse($data, 'no results for query'); // a table or column deleted?
                        } else {
                            $this->assertEqual(preg_match($assertion_sql['match'], $data[ $assertion_sql['column'] ]),
                            1, $assertion_sql['match'] . ' should match ' .  $data[ $assertion_sql['column'] ]);
                            if ( ! preg_match($assertion_sql['match'], $data[ $assertion_sql['column'] ])) {
                                $this->debug("TEST FAIL DEBUGGING:");
                                $this->debug('Query for assertion ' . $assertion_sql['query'] . " with match "
                                . $assertion_sql['match'] . " failed");
                            }
                        }
                        $stmt->closeCursor();
                    }
                }
                $this->get($token_url . '&migration_done=true');
                $this->assertText('{ "migration_complete":true }');
            } else {
                $this->assertText('Your database is up to date');
            }
            //application_options | database_version        | 0.17
            $sql = "SELECT option_value from " . $this->table_prefix .
            "options WHERE namespace = 'application_options' and option_name='database_version'";
            $stmt = $this->pdo->query($sql);
            $data = $stmt->fetch();
            $this->debug("DB option value should now be set to $version");
            $this->assertEqual($data[0], $version);

            // run db migration tests
            if (isset( $migration_data['migration_assertions'] )) {
                $this->debug("Running final migration assertion test for $version");
                foreach($migration_data['migration_assertions'] as $assertions) {
                    foreach($assertions as $assertion) {
                        $this->debug("Running assertion sql: " . $assertion['query']);
                        $stmt = $this->pdo->query($assertion['query']);
                        $data = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (isset($assertion['no_match'])) {
                            $this->assertFalse($data, 'no results for query'); // a table or column deleted?
                        } else {
                            $this->assertEqual(preg_match($assertion['match'], $data[ $assertion['column'] ]), 1,
                            $assertion['match'] . ' should match ' .  $data[ $assertion['column'] ]);
                        }
                        $stmt->closeCursor();
                    }
                }
            } else {
                $this->debug("No final migration assertion test for $version");
            }
        }
    }
    /**
     * Downloads install/upgrade zip file if needed, returns path to zip file.
     * @param str URL
     * @param str Version
     * @return str Path to download file
     */
    private function getInstall($url, $version, $path) {
        $ch = curl_init();
        $zipfile = $path . '/' . $version . '.zip';

        if ( !file_exists($zipfile) ) {
            $this->debug("Fetching zip file $url");
            $ch = curl_init($url);//Here is the file we are downloading
            curl_setopt($ch, CURLOPT_TIMEOUT, self::FILE_DOWNLOAD_TIMEOUT);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $data = curl_exec($ch);
            if ( !$data) {
                $zipfile = false;
                $this->fail("Unable to download zip file: $url " . curl_error($ch) );
                return false;
            }
            file_put_contents($zipfile, $data);
        }
        return $zipfile;
    }

    /**
     * Travis will give up if tests go 10 minutes without any output.
     * This will randomly spit out a .... to keep travis heappy.
     */
    private function travisHeartbeat() {
        // We've got to output something occasionally or travis thinks we died.
        if (getenv('TRAVIS')=='true') {
            print $str."\n";
        }
    }

    /**
     * Latency in travis means we occasionally need to sleep a bit
     * and make sure that the new setup is ready before proceeding.
     */
    private function travisSleepTest() {
        if (getenv('TRAVIS')=='true') {
            $tries = 0;
            do {
                $this->debug('Sleeping for Travis latency: ' . (10*$tries). ' seconds.');
                sleep(10 * $tries++);
                $this->get($this->url.'/test_installer/thinkup/');
                $content = $this->getBrowser()->getContent();
                if ($tries > 3) break;
            } while (preg_match('/Fatal error: (Class|Call to und)/', $content));
        }
    }
}
