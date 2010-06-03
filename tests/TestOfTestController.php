<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/interface.Controller.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankController.php';
require_once $SOURCE_ROOT_PATH.'tests/classes/class.TestController.php';
require_once $SOURCE_ROOT_PATH.'extlib/Smarty-2.6.26/libs/Smarty.class.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.SmartyThinkTank.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Config.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test TestController class
 *
 * TestController isn't a real ThinkTank controller, this is just a template for all Controller tests.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestOfTestController extends ThinkTankBasicUnitTestCase {
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
        $controller = new TestController(true);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    /**
     * Test controller
     * @TODO Possibly load the resulting markup as a DOM object and test various children in it; this would enforce valid markup
     */
    function testControl() {
        $config = Config::getInstance();
        $controller = new TestController(true);
        $results = $controller->go();

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('test'), 'Testing, testing, 123');
        $this->assertEqual($v_mgr->getTemplateDataItem('app_title'), 'ThinkTank');

        $this->assertEqual($results, '<a href="'.$config->getValue('site_root_path').'index.php">ThinkTank</a>: Testing, testing, 123 | Not logged in', "controller output");

    }

    /**
     * Test cache key, no params
     * @TODO Possibly load the resulting markup as a DOM object and test various children in it; this would enforce valid markup
     */
    function testCacheKeyNoRequestParams() {
        $config = Config::getInstance();
        $config->setValue('cache_pages', true);
        $controller = new TestController(true);
        $results = $controller->go();

        $this->assertEqual($controller->getCacheKeyString(), '');
    }

}
