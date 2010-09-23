<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfUpgradeController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('UpgradeController class test');
    }

    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');
    }

    public function tearDown(){
        parent::tearDown();
        if(file_exists(THINKUP_WEBAPP_PATH . UpgradeController::UPGRADE_IN_PROGRESS_FILE)) {
            unlink( THINKUP_WEBAPP_PATH . UpgradeController::UPGRADE_IN_PROGRESS_FILE );
        }
    }

    public function testConstructor() {
        $controller = new UpgradeController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new UpgradeController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testNoMigrationNeeded() {
        $this->simulateLogin('me@example.com');
        $controller = new UpgradeController(true);
        $results = $controller->go();
        $this->assertPattern('/<!-- no upgrade needed -->/', $results);
    }

    public function testOneDatabaseMigrationNeeded() {
        $this->simulateLogin('me@example.com');
        $this->db->exec('alter table tu_owners drop index email');
        $controller = new UpgradeController(true);
        $results = $controller->go();
        $this->assertPattern('/1 database migration to run/', $results);
        $v_mgr = $controller->getViewManager();
        $queries = $v_mgr->getTemplateDataItem('migrations');
        $this->assertEqual(1, count($queries), 'one migration query');
        $this->assertEqual('ALTER TABLE tu_owners ADD UNIQUE KEY email (email)', $queries[0]);
        $this->assertTrue(file_exists(THINKUP_WEBAPP_PATH . UpgradeController::UPGRADE_IN_PROGRESS_FILE));
    }

    public function testTwoDatabaseMigrationNeeded() {
        $this->simulateLogin('me@example.com');
        $this->db->exec('alter table tu_owners drop index email');
        $this->db->exec('alter table tu_owners drop column full_name');
        $controller = new UpgradeController(true);
        $results = $controller->go();
        $this->assertPattern('/2 database migrations to run/', $results);
        $v_mgr = $controller->getViewManager();
        $queries = $v_mgr->getTemplateDataItem('migrations');
        $this->assertEqual(2, count($queries), 'two migration queries');
        $this->assertEqual('ALTER TABLE tu_owners ADD COLUMN full_name varchar(200) NOT NULL', $queries[0]);
        $this->assertEqual('ALTER TABLE tu_owners ADD UNIQUE KEY email (email)', $queries[1]);
        $this->assertTrue(file_exists(THINKUP_WEBAPP_PATH . UpgradeController::UPGRADE_IN_PROGRESS_FILE));
    }

    public function testProcessMigrationsBadSQL() {
        $this->simulateLogin('me@example.com');
        $this->db->exec('alter table tu_owners drop index email');
        $this->db->exec('alter table tu_owners drop column full_name');
        $controller = new UpgradeController(true);
        $results = $controller->go();
        $this->assertPattern('/2 database migrations to run/', $results);
        $v_mgr = $controller->getViewManager();
        $queries = $v_mgr->getTemplateDataItem('migrations');

        // bad sql
        $controller = new UpgradeController(true);
        $_GET['process_sql'] = 'drop table tu_users';
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertFalse($obj->processed);
    }

    public function testProcessMigrationsGoodSQL() {
        $this->simulateLogin('me@example.com');
        $this->db->exec('alter table tu_owners drop index email');
        $this->db->exec('alter table tu_owners drop column full_name');
        $query1 = 'ALTER TABLE tu_owners ADD COLUMN full_name varchar(200) NOT NULL';
        $controller = new UpgradeController(true);
        $_GET['process_sql'] = $query1;
        $results = $controller->go();
        $obj = json_decode($results);
        $this->assertTrue($obj->processed);
        $this->assertEqual($obj->sql, $query1);
    }

    public function testMigrationDone() {
        $this->simulateLogin('me@example.com');
        touch( THINKUP_WEBAPP_PATH . UpgradeController::UPGRADE_IN_PROGRESS_FILE );
        $this->assertTrue(file_exists(THINKUP_WEBAPP_PATH . UpgradeController::UPGRADE_IN_PROGRESS_FILE));
        $controller = new UpgradeController(true);
        $_GET['migration_done'] = true;
        $results = $controller->go();
        $this->assertFalse(file_exists(THINKUP_WEBAPP_PATH . UpgradeController::UPGRADE_IN_PROGRESS_FILE));
    }
}