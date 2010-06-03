<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/interface.Controller.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankAuthController.php';
require_once $SOURCE_ROOT_PATH.'tests/classes/class.TestAuthController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkTank.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Config.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test TestAuthController class
 *
 * TestController isn't a real ThinkTank controller, this is just a template for all Controller tests.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestOfTestAuthController extends ThinkTankBasicUnitTestCase {
    /**
     * Constructor
     */
    function __construct() {
        $this->UnitTestCase('TestController class test');
    }

    function setUp(){
        parent::setUp();
        $config = Config::getInstance();
        $config->setValue('debug', true);
    }

    function tearDown(){
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    function testConstructor() {
        $controller = new TestAuthController(true);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    /**
     * Test controller for non-logged in user
     * @TODO Possibly load the resulting markup as a DOM object and test various children in it; this would enforce valid markup
     */
    function testControlNotLoggedIn() {
        $config = Config::getInstance();
        $controller = new TestAuthController(true);
        $results = $controller->go();

        $this->assertEqual($results, 'You must be logged in to do this', "not logged in, auth controller output");

    }

    /**
     * Test controller for logged-in user
     * @TODO Possibly load the resulting markup as a DOM object and test various children in it; this would enforce valid markup
     */
    function testIsLoggedIn() {
        $_SESSION['user'] = 'me@example.com';
        $config = Config::getInstance();
        $config->setValue('site_root_path', '/my/path/to/thinktank/');

        $controller = new TestAuthController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('test'), 'Testing, testing, 123');
        $this->assertEqual($v_mgr->getTemplateDataItem('app_title'), 'ThinkTank');

        $this->assertEqual($results, '<a href="/my/path/to/thinktank/index.php">ThinkTank</a>: Testing, testing, 123 | Logged in as me@example.com', "auth controller output when logged in");
    }

    /**
     * Test cache key logged in, no params
     */
    function testCacheKeyLoggedIn() {
        $_SESSION['user'] = 'me@example.com';

        $config = Config::getInstance();
        $config->setValue('cache_pages', true);
        $controller = new TestAuthController(true);
        $results = $controller->go();

        $this->assertEqual($controller->getCacheKeyString(), 'me@example.com');
    }

}
