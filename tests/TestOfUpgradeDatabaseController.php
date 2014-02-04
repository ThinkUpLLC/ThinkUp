<?php
/**
 *
 * ThinkUp/tests/TestOfUpgradeDatabaseController.php
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
 * TestOfUpgradeDatabaseController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

class TestOfUpgradeDatabaseController extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'options';

    public function setUp(){
        parent::setUp();

        $config = Config::getInstance();
        $this->config = $config;
        $dao = DAOFactory::getDAO('OptionDAO');
        $this->pdo = OptionMySQLDAO::$PDO;

        $this->init_db_version = $config->getValue('THINKUP_VERSION');
        $new_version = $config->getValue('THINKUP_VERSION') + 10;
        if (!preg_match('/\./', $new_version)) {
            $new_version .= '.0';
        }
        $config->setValue('THINKUP_VERSION', $new_version ); //set a high version num
        $this->token_file = FileDataManager::getDataPath('.htupgrade_token');

        $this->migrations_test_dir = THINKUP_ROOT_PATH . 'tests/data/migrations/';
        $this->migrations_dir = THINKUP_WEBAPP_PATH . 'install/sql/mysql_migrations/';
        $this->migrations_file1 = 'migration1.sql';
        $this->migrations_file2 = 'migration2.sql';
        $this->migrations_file3 = 'migration3.sql';
    }

    public function tearDown(){
        parent::tearDown();
        $config = Config::getInstance();
        //reset app version
        $config->setValue('THINKUP_VERSION', $this->init_db_version);

        /** delete files if needed **/
        // delete token
        if (file_exists($this->token_file)) {
            unlink($this->token_file);
        }
        // delete migration test files
        if (isset($this->test_migrations) && count($this->test_migrations) > 0) {
            foreach($this->test_migrations as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            $this->test_migrations = array();
        }
    }

    public function testConstructor() {
        $controller = new UpgradeDatabaseController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNoMigrationNeeded() {
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeDatabaseController(true);
        $results = $controller->go();
        $this->assertPattern('/<!-- no upgrade needed -->/', $results);
    }

    public function testDatabaseMigrationNeeded() {
        // create test migration sql
        $this->migrationFiles(1);

        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeDatabaseController(true);
        $results = $controller->go();
        $this->assertPattern('/needs 1 database update/', $results);
        $v_mgr = $controller->getViewManager();
        $queries = $v_mgr->getTemplateDataItem('migrations');
        $this->assertEqual(1, count($queries), 'one migration query');
        $this->assertEqual(str_replace('tu_', $this->table_prefix, file_get_contents($this->test_migrations[0])),
        $queries[0]['sql']);

        $this->test_migrations = array(); //clear out old data
        $this->migrationFiles(2);

        $results = $controller->go();
        $this->assertPattern('/needs 2 database updates/', $results);
        $v_mgr = $controller->getViewManager();
        $queries = $v_mgr->getTemplateDataItem('migrations');
        $this->assertEqual(2, count($queries), 'two migration query');
        $this->assertEqual(str_replace('tu_', $this->table_prefix, file_get_contents($this->test_migrations[0])),
        $queries[1]['sql']);
        $this->assertEqual(str_replace('tu_', $this->table_prefix, file_get_contents($this->test_migrations[1])),
        $queries[0]['sql']);
    }

    public function testDatabaseMigrationNeededHighTableCount() {
        // create test migration sql
        $this->migrationFiles(1);

        // table row counts are good
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeDatabaseController(true);
        $results = $controller->go();

        $this->assertPattern('/needs 1 database update/', $results);
        $v_mgr = $controller->getViewManager();
        $this->assertNull($v_mgr->getTemplateDataItem('high_table_row_count') ) ;

        // table row counts are bad
        $old_count = UpgradeDatabaseController::$WARN_TABLE_ROW_COUNT;
        UpgradeDatabaseController::$WARN_TABLE_ROW_COUNT = 2;
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeDatabaseController(true);
        $results = $controller->go();
        $this->assertPattern('/we recommend that you use the.*command line upgrade tool.*when upgrading ThinkUp/sm',
        $results);
        $this->assertPattern('/needs 1 database update/', $results);
        $v_mgr = $controller->getViewManager();
        $table_counts = $v_mgr->getTemplateDataItem('high_table_row_count');
        $this->assertNotNull($table_counts);
        $this->assertNotNull(3, $table_counts['count']); // tu_plugins, defaults to three
        UpgradeDatabaseController::$WARN_TABLE_ROW_COUNT = $old_count;
    }

    public function testGetMigrationList() {
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeDatabaseController(true);
        $this->migrationFiles(1);
        $this->newMigrationFiles('some_stuff');
        $this->newMigrationFiles('some_stuff', $old = true); // older already ran?
        $db_version = UpgradeDatabaseController::getCurrentDBVersion($cached = false);
        $list = $controller->getMigrationList($db_version);
        $this->assertEqual(count($list), 2);
        $this->assertTrue($list[0]['new_migration']);
        $this->assertTrue($list[1]['new_migration']);
        $this->assertPattern("/^2010-09-17_v\d+\.\d+\.sql\.migration$/",$list[0]['filename']);
        $this->assertPattern("/^2011-09-21_some_stuff_v\d+\.\d+\.sql$/",$list[1]['filename']);
        //var_dump($list);
    }

    public function testGetMigrationListWithNewSQL() {
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeDatabaseController(true);
        $this->migrationFiles(1);
        $this->newMigrationFiles('some_stuff');
        $this->newMigrationFiles('some_stuff2', $old = false, $add_sql = false, $no_version = true);
        $db_version = UpgradeDatabaseController::getCurrentDBVersion($cached = false);
        $app_version = $this->config->getValue('THINKUP_VERSION');
        $list = $controller->getMigrationList($db_version, $no_version = true);
        $clean_list = array();
        $cnt = 0;
        foreach($list as $migration) {
            if (preg_match("/some_stuff/", $migration['filename'])) {
                array_push($clean_list, $migration);
            }
            $cnt++;
        }
        $this->assertTrue($clean_list[0]['new_migration']);
        $this->assertTrue($clean_list[1]['new_migration']);
        $this->assertPattern("/^2011-09-21_some_stuff2.sql$/",$clean_list[0]['filename']);
        $this->assertPattern("/^2011-09-21_some_stuff_v".$app_version.".sql$/",$clean_list[1]['filename']);

        // run migration
        $install_dao = DAOFactory::getDAO('InstallerDAO');
        foreach($clean_list as $migration) {
            $sql = preg_replace('/\-\-.*/','', $migration['sql']);
            $install_dao->runMigrationSQL($sql, $migration['new_migration'], $migration['filename']);
        }
        $stmt = $this->pdo->query("select * from " . $this->table_prefix . "completed_migrations");
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 5);
        $this->assertEqual($data[0]['migration'], '2011-09-21_some_stuff2-0');
        $this->assertEqual($data[1]['migration'], '2011-09-21_some_stuff2-1');
        $this->assertEqual($data[2]['migration'], '2011-09-21_some_stuff-0');
        $this->assertEqual($data[3]['migration'], '2011-09-21_some_stuff-1');
        $this->assertEqual($data[4]['migration'], '2011-09-21_some_stuff-2');

        // run same migration file now as a versioned file with one new sql line
        $migration = $clean_list[0];
        $filename = $migration['filename'] = '2011-09-21_some_stuff2_v1.1.sql';
        $migration['sql'] .= "\nINSERT INTO " . $this->table_prefix . "test1 (value) VALUES (5);";
        $sql = preg_replace('/\-\-.*/','', $migration['sql']);
        $install_dao->runMigrationSQL($sql, $migration['new_migration'], $filename);

        $stmt = $this->pdo->query("select * from " . $this->table_prefix . "completed_migrations");
        $data2 = $stmt->fetchAll();
        $this->assertEqual(count($data2), 6);
        $this->assertEqual($data2[0]['migration'], '2011-09-21_some_stuff2-0');
        $this->assertEqual($data2[1]['migration'], '2011-09-21_some_stuff2-1');
        $this->assertEqual($data2[2]['migration'], '2011-09-21_some_stuff-0');
        $this->assertEqual($data2[3]['migration'], '2011-09-21_some_stuff-1');
        $this->assertEqual($data2[4]['migration'], '2011-09-21_some_stuff-2');
        $this->assertEqual($data2[5]['migration'], '2011-09-21_some_stuff2-2');
    }

    public function testRunNewMigration() {
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeDatabaseController(true);
        $this->newMigrationFiles('some_stuff');
        $this->newMigrationFiles('some_stuff', $old = true); // older already ran?
        $db_version = UpgradeDatabaseController::getCurrentDBVersion($cached = false);
        $list = $controller->getMigrationList($db_version);
        $this->assertEqual(count($list), 1);
        $this->assertTrue($list[0]['new_migration']);
        $this->assertPattern("/^2011-09-21_some_stuff_v\d+\.\d+\.sql$/",$list[0]['filename']);

        $_GET['migration_index'] = 1;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $sql = str_replace('tu_', $this->table_prefix, file_get_contents($this->test_migrations[0]));
        $sql = preg_replace('/\-\-.*/','', $sql);
        $this->assertEqual($obj->sql, $sql);

        $sql = "show tables like  '" . $this->table_prefix . "test1'";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data[0], $this->table_prefix . 'test1');
        $sql = 'select * from ' . $this->table_prefix . 'test1';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(count($data), 3);
        $this->assertEqual($data[0]['value'], 1);
        $this->assertEqual($data[1]['value'], 2);
        $this->assertEqual($data[2]['value'], 3);
        // tu_completed_migrations table should contain a record for our latest migration
        $stmt = $this->pdo->query("select * from " . $this->table_prefix . "completed_migrations");
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 3);
        $this->assertEqual($data[0]['migration'], '2011-09-21_some_stuff-0');
        $this->assertEqual($data[1]['migration'], '2011-09-21_some_stuff-1');
        $this->assertEqual($data[2]['migration'], '2011-09-21_some_stuff-2');

        // run it againto veriy it skips alrready run migrations, but add a new one as well
        $new_sql = "INSERT INTO " . $this->table_prefix . "test1 (value) VALUES (4),(5),(6);";
        $this->newMigrationFiles('some_stuff', false, $new_sql);
        $_GET['migration_index'] = 1;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $sql = str_replace('tu_', $this->table_prefix, file_get_contents($this->test_migrations[0]));
        $sql = preg_replace('/\-\-.*/','', $sql);
        $this->assertEqual($obj->sql, $sql);
        $sql = 'select * from ' . $this->table_prefix . 'test1';
        $stmt = $this->pdo->query($sql);
        $data2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(count($data2), 6);
        $this->assertEqual($data2[0]['value'], 1);
        $this->assertEqual($data2[1]['value'], 2);
        $this->assertEqual($data2[2]['value'], 3);
        $this->assertEqual($data2[3]['value'], 4);
        $this->assertEqual($data2[4]['value'], 5);
        $this->assertEqual($data2[5]['value'], 6);

        $stmt = $this->pdo->query("select * from " . $this->table_prefix . "completed_migrations");
        $data2 = $stmt->fetchAll();
        $this->assertEqual(count($data2), 4);
        $this->assertEqual($data2[0]['migration'], '2011-09-21_some_stuff-0');
        $this->assertEqual($data2[1]['migration'], '2011-09-21_some_stuff-1');
        $this->assertEqual($data2[2]['migration'], '2011-09-21_some_stuff-2');
        $this->assertEqual($data2[3]['migration'], '2011-09-21_some_stuff-3');
        $this->assertEqual($data2[0]['date_ran'], $data[0]['date_ran']);
        $this->assertEqual($data2[1]['date_ran'], $data[1]['date_ran']);
        $this->assertEqual($data2[2]['date_ran'], $data[2]['date_ran']);
    }

    public function testRunNewFailedMigration() {
        $this->simulateLogin('me@example.com', true);

        $controller = new UpgradeDatabaseController(true);
        $this->newMigrationFiles2('some_stuff2', $date = false, $bad_sql = true);

        $db_version = UpgradeDatabaseController::getCurrentDBVersion($cached = false);
        $list = $controller->getMigrationList($db_version);
        $this->assertEqual(count($list), 1);
        $this->assertTrue($list[0]['new_migration']);
        $this->assertPattern("/^2011-09-21_some_stuff2_v\d+\.\d+\.sql$/",$list[0]['filename']);

        $_GET['migration_index'] = 1;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertFalse($obj->processed);

        $stmt = $this->pdo->query("select * from  " . $this->table_prefix . "completed_migrations");
        $data2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(5, count($data2));
        $stmt = $this->pdo->query("desc " . $this->table_prefix . "test3");
        $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertFalse($stmt->fetch(PDO::FETCH_ASSOC)); // no user_id column
        $stmt = $this->pdo->query("select count(*) as count from  " . $this->table_prefix . "test3");
        $data = $stmt->fetch();
        $this->assertEqual(3, $data['count']);

        $controller = new UpgradeDatabaseController(true);
        $this->newMigrationFiles2('some_stuff2', $date = false, $bad_sql = false);
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $stmt = $this->pdo->query("select * from " . $this->table_prefix . "completed_migrations");
        $data2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(9, count($data2));

        $stmt = $this->pdo->query("desc " . $this->table_prefix . "test3");
        $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertTrue($stmt->fetch(PDO::FETCH_ASSOC)); // has a user_id column

        $stmt = $this->pdo->query("select * from " . $this->table_prefix . "test3 where value = 8");
        $data2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual($data2[0]['value'], '8');
        $this->assertEqual($data2[0]['user_id'], '1003');

        $stmt = $this->pdo->query("select * from " . $this->table_prefix . "test3 where value = 5");
        $data2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual($data2[0]['value'], '5');
        $this->assertEqual($data2[0]['user_id'], '1000');

    }

    public function testRunNewMigrationUpdateCompletedTable() {
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeDatabaseController(true);
        $this->newMigrationFiles('some_stuff');
        $this->newMigrationFiles('some_stuff', $old = true); // older already ran?
        $db_version = UpgradeDatabaseController::getCurrentDBVersion($cached = false);
        $list = $controller->getMigrationList($db_version);
        $this->assertEqual(count($list), 1);
        $this->assertTrue($list[0]['new_migration']);
        $this->assertPattern("/^2011-09-21_some_stuff_v\d+\.\d+\.sql$/",$list[0]['filename']);

        // create completion table
        $com_sql_file = THINKUP_WEBAPP_PATH.'install/sql/completed_migrations.sql';
        //echo $com_sql_file;
        $com_sql = file_get_contents($com_sql_file);
        $com_sql = str_replace('tu_', $this->table_prefix, $com_sql);
        $this->pdo->query($com_sql);
        $this->pdo->query("alter table " . $this->table_prefix . "completed_migrations DROP column sql_ran");

        $_GET['migration_index'] = 1;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $sql = file_get_contents($this->test_migrations[0]);
        $sql = preg_replace('/\-\-.*/','', $sql);
        $sql = str_replace('tu_', $this->table_prefix, $sql);
        $this->assertEqual($obj->sql, $sql);
        $stmt = $this->pdo->query("select * from " . $this->table_prefix . "completed_migrations");
        $data2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(count($data2), 3);
        $this->assertPattern('/CREATE TABLE `.*test1`/', $data2[0]['sql_ran']);
    }

    public function testGenerateUpgradeToken() {
        $this->simulateLogin('me@example.com');
        UpgradeDatabaseController::generateUpgradeToken();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/^[\da-f]{32}$/', file_get_contents($this->token_file));
    }

    /**
     * Test generating and authenticating with a token
     */
    public function testNotLoggedInNoTAdminGensAndAuthsToken() {
        // not logged in...
        $controller = new UpgradeDatabaseController(true);
        $results = $controller->go();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/<!--  we are upgrading -->/', $results);
        $this->assertTrue( file_exists($this->token_file) );
        $token = file_get_contents($this->token_file);
        $this->assertPattern('/^[\da-f]{32}$/', $token);

        // logged in, but not an admin
        unlink($this->token_file);
        $this->simulateLogin('me@example.com', false);
        $results = $controller->go();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/<!--  we are upgrading -->/', $results);
        $this->assertTrue( file_exists($this->token_file) );
        $token = file_get_contents($this->token_file);
        $this->assertPattern('/^[\da-f]{32}$/', $token);

        // login with a bad token
        $_GET['upgrade_token'] = 'badtoken';
        $results = $controller->go();
        $this->assertPattern('/This update has already been completed/', $results);

        // log in with a valid token
        $this->simulateLogin('me@example.com', false);
        $_GET['upgrade_token'] = $token;
        $results = $controller->go();
        $this->assertFalse( file_exists($this->token_file) );
        $this->assertPattern('/<!-- no upgrade needed -->/', $results);
    }

    /**
     * Test generating and emailing a token to admin(s)
     */
    public function testTokenEmail() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');

        // build 1 valid admin and two invalid admins
        $builder1 = FixtureBuilder::build('owners', array('is_admin' => 1, 'is_activated' => 1, 'email' => 'm@w.nz'));
        $builder2 = FixtureBuilder::build('owners', array('is_admin' => 0, 'is_activated' => 1, 'email' => 'm2@w.nz'));
        $builder3 = FixtureBuilder::build('owners', array('is_admin' => 1, 'is_activated' => 0, 'email' => 'm3@w.nz'));

        $_SERVER['HTTP_HOST'] = "mytestthinkup";

        $controller = new UpgradeDatabaseController(true);
        $results = $controller->go();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/<!--  we are upgrading -->/', $results);
        $this->assertTrue( file_exists($this->token_file) );
        $token = file_get_contents($this->token_file);
        $this->assertPattern('/^[\da-f]{32}$/', $token);

        $email_file = Mailer::getLastMail();
        $this->debug($email_file);

        $this->assertPattern('/to\: m@w\.nz\s/', $email_file);
        $this->assertPattern('/subject\: Upgrade Your ThinkUp Database/', $email_file);
        $token_regex = '/http:\/\/mytestthinkup'.str_replace('/', '\/', $site_root_path).
        'install\/upgrade-database.php\?upgrade_token=' . $token . '/';
        $this->assertPattern($token_regex, $email_file);

        // build 1 more valid admin, should have two to emails
        $test_email = FileDataManager::getDataPath(Mailer::EMAIL);
        unlink($test_email);
        unlink($this->token_file);
        $builder4 = FixtureBuilder::build('owners', array('is_admin' => 1, 'is_activated' => 1, 'email' => 'm4@w.nz'));

        $results = $controller->go();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/<!--  we are upgrading -->/', $results);
        $this->assertTrue( file_exists($this->token_file) );
        $token = file_get_contents($this->token_file);
        $this->assertPattern('/^[\da-f]{32}$/', $token);

        $email_file = Mailer::getLastMail();

        $this->assertPattern('/to\: m@w\.nz,m4@w\.nz\s/', $email_file);
        $this->assertPattern('/subject\: Upgrade Your ThinkUp Database/', $email_file);
        $token_regex = '/\/install\/upgrade-database.php\?upgrade_token=' . $token . '/';
        $this->assertPattern($token_regex, $email_file);

        // should not send email if a token file exists
        $test_email = FileDataManager::getDataPath(Mailer::EMAIL);
        unlink($test_email);
        $results = $controller->go();
        $this->assertFalse( file_exists($test_email) );
    }

    public function testTokenEmailWithSSL() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');

        // build 1 valid admin and two invalid admins
        $builder1 = FixtureBuilder::build('owners', array('is_admin' => 1, 'is_activated' => 1, 'email' => 'm@w.nz'));
        $builder2 = FixtureBuilder::build('owners', array('is_admin' => 0, 'is_activated' => 1, 'email' => 'm2@w.nz'));
        $builder3 = FixtureBuilder::build('owners', array('is_admin' => 1, 'is_activated' => 0, 'email' => 'm3@w.nz'));

        $_SERVER['HTTP_HOST'] = "mytestthinkup";
        $_SERVER['HTTPS'] = "mytestthinkup";

        $controller = new UpgradeDatabaseController(true);
        $results = $controller->go();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/<!--  we are upgrading -->/', $results);
        $this->assertTrue( file_exists($this->token_file) );
        $token = file_get_contents($this->token_file);
        $this->assertPattern('/^[\da-f]{32}$/', $token);

        $email_file = Mailer::getLastMail();
        $this->debug($email_file);

        $this->assertPattern('/to\: m@w\.nz\s/', $email_file);
        $this->assertPattern('/subject\: Upgrade Your ThinkUp Database/', $email_file);
        $token_regex = '/https:\/\/mytestthinkup'.str_replace('/', '\/', $site_root_path).
        'install\/upgrade-database.php\?upgrade_token=' . $token . '/';
        $this->assertPattern($token_regex, $email_file);

        // build 1 more valid admin, should have two to emails
        $test_email = FileDataManager::getDataPath(Mailer::EMAIL);
        unlink($test_email);
        unlink($this->token_file);
        $builder4 = FixtureBuilder::build('owners', array('is_admin' => 1, 'is_activated' => 1, 'email' => 'm4@w.nz'));

        $results = $controller->go();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/<!--  we are upgrading -->/', $results);
        $this->assertTrue( file_exists($this->token_file) );
        $token = file_get_contents($this->token_file);
        $this->assertPattern('/^[\da-f]{32}$/', $token);

        $email_file = Mailer::getLastMail();

        $this->assertPattern('/to\: m@w\.nz,m4@w\.nz\s/', $email_file);
        $this->assertPattern('/subject\: Upgrade Your ThinkUp Database/', $email_file);
        $token_regex = '/\/install\/upgrade-database.php\?upgrade_token=' . $token . '/';
        $this->assertPattern($token_regex, $email_file);

        // should not send email if a token file exists
        $test_email = FileDataManager::getDataPath(Mailer::EMAIL);
        unlink($test_email);
        $results = $controller->go();
        $this->assertFalse( file_exists($test_email) );
    }

    public function testProcessOneMigrations() {
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeDatabaseController(true);
        $this->migrationFiles(1);
        $_GET['migration_index'] = 1;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $sql_file = str_replace('tu_', $this->table_prefix, file_get_contents($this->test_migrations[0]));
        $sql_file = preg_replace('/\-\-.*/','', $sql_file);
        $this->assertEqual($obj->sql, $sql_file);

        $sql = "show tables like  '" . $this->table_prefix . "test1'";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data[0], $this->table_prefix . 'test1');
        $sql = 'select * from ' . $this->table_prefix . 'test1';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(count($data), 3);
    }

    public function testProcessMinorVersionMigration() {
        $this->simulateLogin('me@example.com', true);
        $config = Config::getInstance();
        $version = $config->getValue('THINKUP_VERSION') + 10;
        $version .= '.2';
        $config->setValue('THINKUP_VERSION', $version); //set a high minor version
        $controller = new UpgradeDatabaseController(true);
        $this->migrationFiles(1);
        $_GET['migration_index'] = 1;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $sql = str_replace('tu_', $this->table_prefix, file_get_contents($this->test_migrations[0]));
        $sql = preg_replace('/\-\-.*/','', $sql);
        $this->assertEqual($obj->sql, $sql);

        $sql = "show tables like  '" . $this->table_prefix . "test1'";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data[0], $this->table_prefix . 'test1');
        $sql = 'select * from ' . $this->table_prefix . 'test1';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(count($data), 3);
    }

    public function testProcessMinorBetaVersionMigration() {
        $this->simulateLogin('me@example.com', true);
        $config = Config::getInstance();
        $test_version = $config->getValue('THINKUP_VERSION') + 10.1;
        $test_version .= '.2beta';
        $config->setValue('THINKUP_VERSION', $test_version); //set a high minor version with beta string
        $controller = new UpgradeDatabaseController(true);
        $this->migrationFiles(1);
        $_GET['migration_index'] = 1;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $sql = str_replace('tu_', $this->table_prefix, file_get_contents($this->test_migrations[0]));
        $sql = preg_replace('/\-\-.*/','', $sql);
        $this->assertEqual($obj->sql, $sql);

        $sql = "show tables like  '" . $this->table_prefix . "test1'";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data[0], $this->table_prefix . 'test1');
        $sql = 'select * from ' . $this->table_prefix . 'test1';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(count($data), 3);
    }

    public function testProcessSnowflakeMigration() {
        $config = Config::getInstance();
        $app_path = $config->getValue('source_root_path');
        $snowflakekey = 'runnig_snowflake_uprade';

        // no snowflake update needed...
        $this->pdo->query("truncate table " . $this->table_prefix . "options");
        $this->simulateLogin('me@example.com', true);
        $this->assertFalse(SessionCache::isKeySet($snowflakekey));

        $config = Config::getInstance();
        $config->setValue('THINKUP_VERSION', '0.4');
        $controller = new UpgradeDatabaseController(true);
        $results = $controller->go();
        $this->debug($results);
        $this->assertPattern('/needs 1 database update/', $results);

        // snowflake update needed...
        $this->pdo->query("drop table " . $this->table_prefix . "options");
        $this->testdb_helper->runSQL('ALTER TABLE ' . $this->table_prefix .
        'instances CHANGE last_post_id last_status_id bigint(11) NOT NULL');
        $this->testdb_helper->runSQL('ALTER TABLE ' . $this->table_prefix .'links ADD  post_id BIGINT( 20 ) NOT NULL,'.
        'ADD network VARCHAR( 20 ) NOT NULL');
        $this->debug('Haven\'t instantiated controller yet');
        $controller = new UpgradeDatabaseController(true);
        $this->debug('Just ran controller');
        $results = $controller->go();
        $this->debug($results);
        $this->assertPattern('/needs 2 database updates/', $results);
        $v_mgr = $controller->getViewManager();
        $queries = $v_mgr->getTemplateDataItem('migrations');
        $this->assertEqual(2, count($queries), 'two migration queries');
        $this->assertTrue(SessionCache::isKeySet($snowflakekey));

        // run snowflake migration
        $_GET['migration_index'] = 1;
        $controller = new UpgradeDatabaseController(true);
        $results = $controller->go();
        $this->debug($results);
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $stmt = $this->pdo->query("desc " . $this->table_prefix . "instances last_post_id");
        $data = $stmt->fetch();
        $this->assertEqual($data['Field'], 'last_post_id');
        $this->assertPattern('/bigint\(20\)\s+unsigned/i', $data['Type']);
        $this->assertTrue(SessionCache::isKeySet($snowflakekey));

        // run version 4 upgrade
        $_GET['migration_index'] = 2;
        $controller = new UpgradeDatabaseController(true);
        $results = $controller->go();
        $this->debug($results);
        $this->assertTrue($obj->processed);
        $stmt = $this->pdo->query("desc " . $this->table_prefix . "instances last_post_id");
        $data = $stmt->fetch();
        $this->assertEqual($data['Field'], 'last_post_id');
        $this->assertPattern('/bigint\(20\)\s+unsigned/i', $data['Type']);

        // no snowflake session data when complete
        $config = Config::getInstance();
        unset($_GET['migration_index']);
        $_GET['migration_done'] = true;
        $results = $controller->go();
        $this->debug($results);
        $obj = json_decode($results);
        $this->assertTrue($obj->migration_complete);
        $this->assertFalse(SessionCache::isKeySet($snowflakekey));
    }

    public function testProcessTwoMigrations() {
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeDatabaseController(true);
        $this->migrationFiles(2);
        $_GET['migration_index'] = 1;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $sql = str_replace('tu_', $this->table_prefix, file_get_contents($this->test_migrations[1]));
        $sql = preg_replace('/\-\-.*/','', $sql);
        $this->assertEqual($obj->sql, $sql);

        $sql = "show tables like  '" . $this->table_prefix . "test2'";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data[0], $this->table_prefix . 'test2');
        $sql = 'select * from ' . $this->table_prefix . 'test2';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(count($data), 1);

        $_GET['migration_index'] = 2;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $sql = str_replace('tu_', $this->table_prefix, file_get_contents($this->test_migrations[0]));
        $sql = preg_replace('/\-\-.*/','', $sql);
        $this->assertEqual($obj->sql, $sql);

        $sql = "show tables like  '" . $this->table_prefix . "test1'";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data[0], $this->table_prefix . 'test1');
        $sql = 'select * from ' . $this->table_prefix . 'test1';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(count($data), 3);
    }

    public function testProcessMigrationWithAToken() {
        // not logged in...
        $controller = new UpgradeDatabaseController(true);
        $results = $controller->go();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/<!--  we are upgrading -->/', $results);
        $this->assertTrue( file_exists($this->token_file) );
        $token = file_get_contents($this->token_file);
        $this->assertPattern('/^[\da-f]{32}$/', $token);

        $controller = new UpgradeDatabaseController(true);
        $this->migrationFiles(1);
        $_GET['migration_index'] = 1;
        $_GET['upgrade_token'] = $token;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $sql = str_replace('tu_', $this->table_prefix, file_get_contents($this->test_migrations[0]));
        $sql = preg_replace('/\-\-.*/','', $sql);
        $this->assertEqual($obj->sql, $sql);
    }

    public function testMigrationDone() {
        // no db record...
        $config = Config::getInstance();
        $app_version = $config->getValue('THINKUP_VERSION');
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeDatabaseController(true);
        $_GET['migration_done'] = true;

        $results = $controller->go();
        $sql = "select * from " . $this->table_prefix . 'options';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['option_value'], $app_version);

        // with a db record...
        $config->setValue('THINKUP_VERSION', ($config->getValue('THINKUP_VERSION') + 1 ));
        $results = $controller->go();
        $sql = "select * from " . $this->table_prefix . 'options';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['option_value'], ($app_version + 1));
    }

    public function testMigrationDoneWithToken() {
        // not logged in...
        $controller = new UpgradeDatabaseController(true);
        $results = $controller->go();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/<!--  we are upgrading -->/', $results);
        $this->assertTrue( file_exists($this->token_file) );
        $token = file_get_contents($this->token_file);
        $this->assertPattern('/^[\da-f]{32}$/', $token);

        $config = Config::getInstance();
        $app_version = $config->getValue('THINKUP_VERSION');
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeDatabaseController(true);
        $_GET['migration_done'] = true;
        $_GET['upgrade_token'] = $token;

        $results = $controller->go();
        $sql = "select * from " . $this->table_prefix . 'options';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['option_value'], $app_version);
        $this->assertFalse( file_exists($this->token_file) );
    }

    public function testProcessMigrationsDifferentPrefix() {

        $config = Config::getInstance();
        $old_table_prefix = $config->getValue('table_prefix');
        $config->setValue('table_prefix', 'new_prefix_');

        $stmt = $this->pdo->query("show tables");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        //var_dump($data);
        foreach($data as $table) {
            foreach($table as $key=> $value) {
                $new_value = preg_replace("/^" . $old_table_prefix . "/", " new_prefix_", $value);
                $sql = "RENAME TABLE $value TO $new_value";
                $this->pdo->query($sql);
            }
        }

        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeDatabaseController(true);
        $this->migrationFiles(1);
        $_GET['migration_index'] = 1;
        $results = $controller->go();

        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $updated_file = file_get_contents($this->test_migrations[0]);
        $updated_file = preg_replace('/\-\-.*/','', $updated_file);
        $updated_file = str_replace('tu_', 'new_prefix_', $updated_file);
        $this->debug($obj->sql);
        $this->assertEqual($obj->sql, $updated_file);
        $this->assertFalse(strrpos($obj->sql, 'tu_'));
        $sql = "show tables like 'new_prefix_test1'";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data[0], 'new_prefix_test1');
        $sql = 'select * from new_prefix_test1';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(count($data), 3);
    }

    private function migrationFiles($count = 1) {
        $config = Config::getInstance();
        $app_version = $config->getValue('THINKUP_VERSION');
        $migration_version = $app_version - 1;
        if (!preg_match('/\./', $migration_version)) {
            $migration_version .= '.0';
        }
        $migration_test1 = $this->migrations_test_dir . $this->migrations_file1;
        $migration1 = $this->migrations_dir
        . '2010-09-17_v' . $migration_version . '.sql.migration';
        copy($migration_test1, $migration1);
        $this->test_migrations[] = $migration1;
        if ($count == 2) {
            $migration_test2 = $this->migrations_test_dir . $this->migrations_file2;
            $migration_version--;
            $migration_version += 0.12;
            $migration_version .= 'beta';
            $migration2 = $this->migrations_dir
            . '2010-09-16_v' . $migration_version . '.sql.migration';
            copy($migration_test2, $migration2);
            $this->test_migrations[] = $migration2;
        }
    }
    private function newMigrationFiles($name, $old = false, $add_sql = false, $no_version = false, $date = false) {
        $config = Config::getInstance();
        $app_version = $config->getValue('THINKUP_VERSION');
        if (!$date) {
            $date = '2011-09-21';
        }
        $migration_version = $app_version;
        if ($old == false) {
            $migration_version = $app_version;
        } else {
            $migration_version = $app_version - 10;
        }
        $migration_test1 = $this->migrations_test_dir . $this->migrations_file1;
        if ($no_version) {
            $migration_test1 = $this->migrations_test_dir . $this->migrations_file2;
        }
        $migration1 = $this->migrations_dir . $date . '_' . $name;
        if (!$no_version) {
            $migration1 .= '_v' . $migration_version;
        }
        $migration1 .= '.sql';
        if (file_exists($migration1)) {
            unlink($migration1);
        }
        copy($migration_test1, $migration1);
        if ($add_sql) {
            $msql = file_get_contents($migration1);
            $msql .= "\n" . $add_sql;
            file_put_contents($migration1, $msql);
        }
        $this->test_migrations[] = $migration1;
    }
    private function newMigrationFiles2($name, $date = false, $bad_sql = false) {
        $config = Config::getInstance();
        $app_version = $config->getValue('THINKUP_VERSION');
        if (!$date) {
            $date = '2011-09-21';
        }
        $migration_version = $app_version;
        $migration_version = $app_version;
        $migration_test3 = $this->migrations_test_dir . $this->migrations_file3;
        $migration3 = $this->migrations_dir . $date . '_' . $name;
        $migration3 .= '_v' . $migration_version;
        $migration3 .= '.sql';
        if (file_exists($migration3)) {
            unlink($migration3);
        }
        copy($migration_test3, $migration3);
        if ($bad_sql) {
            $msql = file_get_contents($migration3);
            $msql = preg_replace("/INSERT INTO tu_test3_b16/", 'INSERT INTO tu_test3_b16_badtable', $msql);
            file_put_contents($migration3, $msql);
        }
        $this->test_migrations[] = $migration3;
    }
}
