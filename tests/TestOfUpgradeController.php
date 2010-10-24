<?php
/**
 *
 * ThinkUp/tests/TestOfUpgradeController
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
 * TestOfUpgradeController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfUpgradeController extends ThinkUpUnitTestCase {

    const TEST_TABLE = 'options';

    public function __construct() {
        $this->UnitTestCase('UpgradeController class test');
    }

    public function setUp(){
        parent::setUp();

        $config = Config::getInstance();

        $this->prefix = $config->getValue('table_prefix');
        $this->pdo =  OptionMysqlDAO::$PDO;

        $this->init_db_version = $config->getValue('THINKUP_VERSION');
        $config->setValue('THINKUP_VERSION', $config->getValue('THINKUP_VERSION') + 10); //set a high version num

        $this->token_file = THINKUP_WEBAPP_PATH . UpgradeController::CACHE_DIR . '/upgrade_token';

        $this->migrations_test_dir = THINKUP_ROOT_PATH . 'tests/data/migrations/';
        $this->migrations_dir = THINKUP_WEBAPP_PATH . 'install/sql/mysql_migrations/';
        $this->migrations_file1 = 'migration1.sql';
        $this->migrations_file2 = 'migration2.sql';
    }

    public function tearDown(){
        parent::tearDown();
        $config = Config::getInstance();
        //reset app version
        $config->setValue('THINKUP_VERSION', $this->init_db_version);
        
        /** delete files if needed **/
        // delete token
        if(file_exists($this->token_file)) {
            unlink($this->token_file);
        }
        // delete migration test files
        if(isset($this->test_migrations) && count($this->test_migrations) > 0) {
            foreach($this->test_migrations as $file) {
                if(file_exists($file)) {
                    unlink($file);
                }
            }
            $this->test_migrations = array();
        }
    }

    public function testConstructor() {
        $controller = new UpgradeController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNoMigrationNeeded() {
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeController(true);
        $results = $controller->go();
        $this->assertPattern('/<!-- no upgrade needed -->/', $results);
    }

    public function testDatabaseMigrationNeeded() {

        // create test migration sql
        $this->migrationFiles(1);

        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeController(true);
        $results = $controller->go();
        $this->assertPattern('/1 database migration to run/', $results);
        $v_mgr = $controller->getViewManager();
        $queries = $v_mgr->getTemplateDataItem('migrations');
        $this->assertEqual(1, count($queries), 'one migration query');
        $this->assertEqual(file_get_contents($this->test_migrations[0]), $queries[0]['sql']);

        $this->test_migrations = array(); //clear out old data
        $this->migrationFiles(2);

        $results = $controller->go();
        $this->assertPattern('/2 database migrations to run/', $results);
        $v_mgr = $controller->getViewManager();
        $queries = $v_mgr->getTemplateDataItem('migrations');
        $this->assertEqual(2, count($queries), 'two migration query');
        $this->assertEqual(file_get_contents($this->test_migrations[0]), $queries[1]['sql']);
        $this->assertEqual(file_get_contents($this->test_migrations[1]), $queries[0]['sql']);

    }

    public function testGenerateUpgradeToken() {
        $this->simulateLogin('me@example.com');
        $controller = new UpgradeController(true);
        UpgradeController::generateUpgradeToken();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/^[\da-f]{32}$/', file_get_contents($this->token_file));
    }

    /**
     * Test generating and authenticating with a token
     */
    public function testNotLoggedInNoTAdminGensAndAuthsToken() {
        // not logged in...
        $controller = new UpgradeController(true);
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
        $this->assertPattern('/You must be a ThinkUp admin to do this/', $results);

        // log in with a valid token
        $this->simulateLogin('me@example.com', false);
        $_GET['upgrade_token'] = $token;
        $results = $controller->go();
        $this->assertFalse( file_exists($this->token_file) );
        $this->assertPattern('/<!-- no upgrade needed -->/', $results);

        // NOTE: this will only happen when our db versions are out of sync
        $this->assertPattern('/database version has been updated to relect the latest installed version/', $results);

    }

    /**
     * Test generating and emailing a token to admin(s)
     */
    public function testTokenEmail() {

        // build 1 valid admin and two invalid admins
        $builder1 = FixtureBuilder::build('owners', array('is_admin' => 1, 'is_activated' => 1, 'email' => 'm@w.nz'));
        $builder2 = FixtureBuilder::build('owners', array('is_admin' => 0, 'is_activated' => 1, 'email' => 'm2@w.nz'));
        $builder3 = FixtureBuilder::build('owners', array('is_admin' => 1, 'is_activated' => 0, 'email' => 'm3@w.nz'));

        $controller = new UpgradeController(true);
        $results = $controller->go();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/<!--  we are upgrading -->/', $results);
        $this->assertTrue( file_exists($this->token_file) );
        $token = file_get_contents($this->token_file);
        $this->assertPattern('/^[\da-f]{32}$/', $token);

        // NOTE: see the mock Mailer class at the bottom of this test source to see how
        // we generate the email file
        $test_email = THINKUP_WEBAPP_PATH . UpgradeController::CACHE_DIR . self::TEST_EMAIL;
        $email_file = file_get_contents($test_email);

        $this->assertPattern('/to\: m@w\.nz\s/', $email_file);
        $this->assertPattern('/subject\: Upgrade ThinkUp Database/', $email_file);
        $token_regex = '/\/install\/upgrade.php\?upgrade_token=' . $token . '/';
        $this->assertPattern($token_regex, $email_file);

        // build 1 more valid admin, should have two to emails
        unlink($test_email);
        unlink($this->token_file);
        $builder4 = FixtureBuilder::build('owners', array('is_admin' => 1, 'is_activated' => 1, 'email' => 'm4@w.nz'));

        $results = $controller->go();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/<!--  we are upgrading -->/', $results);
        $this->assertTrue( file_exists($this->token_file) );
        $token = file_get_contents($this->token_file);
        $this->assertPattern('/^[\da-f]{32}$/', $token);

        $test_email = THINKUP_WEBAPP_PATH . UpgradeController::CACHE_DIR . self::TEST_EMAIL;
        $email_file = file_get_contents($test_email);

        $this->assertPattern('/to\: m@w\.nz,m4@w\.nz\s/', $email_file);
        $this->assertPattern('/subject\: Upgrade ThinkUp Database/', $email_file);
        $token_regex = '/\/install\/upgrade.php\?upgrade_token=' . $token . '/';
        $this->assertPattern($token_regex, $email_file);

        // should not send email if a token file exists
        unlink($test_email);
        $results = $controller->go();
        $this->assertFalse( file_exists($test_email) );

    }

    public function testProcessOneMigrations() {
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeController(true);
        $this->migrationFiles(1);
        $_GET['migration_index'] = 1;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $this->assertEqual($obj->sql, file_get_contents($this->test_migrations[0]));
        
        $sql = "show tables like  'test1'";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data[0], 'test1');
        $sql = 'select * from test1';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(count($data), 3);
        
    }

    public function testProcessTwoMigrations() {
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeController(true);
        $this->migrationFiles(2);
        $_GET['migration_index'] = 1;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $this->assertEqual($obj->sql, file_get_contents($this->test_migrations[1]));

        $sql = "show tables like  'test2'";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data[0], 'test2');
        $sql = 'select * from test2';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(count($data), 1);
        
        $_GET['migration_index'] = 2;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $this->assertEqual($obj->sql, file_get_contents($this->test_migrations[0]));

        $sql = "show tables like  'test1'";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data[0], 'test1');
        $sql = 'select * from test1';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEqual(count($data), 3);
        
    }

    public function testProcessMigrationWithAToken() {
        // not logged in...
        $controller = new UpgradeController(true);
        $results = $controller->go();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/<!--  we are upgrading -->/', $results);
        $this->assertTrue( file_exists($this->token_file) );
        $token = file_get_contents($this->token_file);
        $this->assertPattern('/^[\da-f]{32}$/', $token);

        $controller = new UpgradeController(true);
        $this->migrationFiles(1);
        $_GET['migration_index'] = 1;
        $_GET['upgrade_token'] = $token;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $this->assertEqual($obj->sql, file_get_contents($this->test_migrations[0]));
    }

    public function testMigrationDone() {
        // no db record...
        $config = Config::getInstance();
        $app_version = $config->getValue('THINKUP_VERSION');
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeController(true);
        $_GET['migration_done'] = true;

        $results = $controller->go();
        $sql = "select * from " . $this->prefix . 'options';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['option_value'], $app_version);

        // with a db record...
        $config->setValue('THINKUP_VERSION', ($config->getValue('THINKUP_VERSION') + 1 ));
        $results = $controller->go();
        $sql = "select * from " . $this->prefix . 'options';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['option_value'], ($app_version + 1));
    }

    public function testMigrationDoneWithToken() {
        // not logged in...
        $controller = new UpgradeController(true);
        $results = $controller->go();
        $this->assertTrue( file_exists($this->token_file) );
        $this->assertPattern('/<!--  we are upgrading -->/', $results);
        $this->assertTrue( file_exists($this->token_file) );
        $token = file_get_contents($this->token_file);
        $this->assertPattern('/^[\da-f]{32}$/', $token);

        $config = Config::getInstance();
        $app_version = $config->getValue('THINKUP_VERSION');
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeController(true);
        $_GET['migration_done'] = true;
        $_GET['upgrade_token'] = $token;

        $results = $controller->go();
        $sql = "select * from " . $this->prefix . 'options';
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['option_value'], $app_version);
        $this->assertFalse( file_exists($this->token_file) );

    }

    private function migrationFiles($count = 1) {
        $config = Config::getInstance();
        $app_version = $config->getValue('THINKUP_VERSION');
        $migration_version = $app_version - 1;
        $migration_test1 = $this->migrations_test_dir . $this->migrations_file1;
        $migration1 = $this->migrations_dir
        . '2010-09-17_v' . $migration_version . '.sql.migration';
        copy($migration_test1, $migration1);
        $this->test_migrations[] = $migration1;
        if($count == 2) {
            $migration_test2 = $this->migrations_test_dir . $this->migrations_file2;
            $migration_version--;
            $migration2 = $this->migrations_dir
            . '2010-09-16_v' . $migration_version . '.sql.migration';
            copy($migration_test2, $migration2);
            $this->test_migrations[] = $migration2;
        }

    }
}
