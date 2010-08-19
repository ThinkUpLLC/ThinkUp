<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfToggleActiveInstanceController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('ToggleActiveInstanceController class test');
    }

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new ToggleActiveInstanceController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new ToggleActiveInstanceController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testNotAnAdmin() {
        $_SESSION['user'] = 'me@example.com';
        $controller = new ToggleActiveInstanceController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must be a ThinkUp admin to do this', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testMissingInstanceParam() {
        $_SESSION['user'] = 'me@example.com';
        $_SESSION['user_is_admin'] = true;
        $_GET['p'] = 1;
        $controller = new ToggleActiveInstanceController(true);
        $results = $controller->go();
        $this->assertEqual($results, 'Missing required parameters.', $results);
    }

    public function testMissingActiveParam() {
        $_SESSION['user'] = 'me@example.com';
        $_SESSION['user_is_admin'] = true;
        $_GET['u'] = 'ginatrapani';
        $controller = new ToggleActiveInstanceController(true);
        $results = $controller->go();
        $this->assertEqual($results, 'Missing required parameters.', $results);
    }

    public function testBothParamsNonExistentInstance() {
        $_SESSION['user'] = 'me@example.com';
        $_SESSION['user_is_admin'] = true;
        $_GET['u'] = 1;
        $_GET['p'] = 1;
        $controller = new ToggleActiveInstanceController(true);
        $results = $controller->go();
        $this->assertEqual($results, 0, $results);
    }

    public function testBothParamsExistentInstance() {
        $builder = FixtureBuilder::build('instances', array('id'=>12, 'is_active'=>1));
        $_SESSION['user'] = 'me@example.com';
        $_SESSION['user_is_admin'] = true;
        $_GET['u'] = '12';
        $_GET['p'] = '0';
        $controller = new ToggleActiveInstanceController(true);
        $results = $controller->go();
        $this->assertEqual($results, 1);
    }
}