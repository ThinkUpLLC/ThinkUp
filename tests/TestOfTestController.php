<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/interface.Controller.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankController.php';
require_once $SOURCE_ROOT_PATH.'tests/classes/class.TestController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkTank.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';
try {
    $db = new Database($THINKTANK_CFG);
    $conn = $db->getConnection();
} catch(Exception $e) {
    echo $e->getMessage();
}

class TestOfTestController extends ThinkTankUnitTestCase {

    function TestTestController() {
        $this->UnitTestCase('TestController class test');
    }

    function setUp(){
        parent::setUp();
    }

    function tearDown(){
        parent::tearDown();
    }


    function testConstructor() {
        $controller = new TestController(true);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    function testControlWithDefaultValues() {
        $config = Config::getInstance();

        $controller = new TestController(true);
        $results = $controller->control();
        $this->assertEqual($results, "Testing, testing, 123 | Site root path: ".$config->getValue('site_root_path')." | Not logged in", "controller output");
    }

    function testIsLoggedIn() {
        $_SESSION['user'] = 'me@example.com';
        $config = Config::getInstance();
        $controller = new TestController(true);
        $results = $controller->control();
        $this->assertEqual($results, "Testing, testing, 123 | Site root path: ".$config->getValue('site_root_path')." | Logged in as me@example.com", "controller output when logged in");
    }
}
