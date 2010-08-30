<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test TestController class
 *
 * TestController isn't a real ThinkUp controller, this is just a template for all Controller tests.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestOfTestController extends ThinkUpUnitTestCase {
    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('TestController class test');
    }

    public function setUp(){
        parent::setUp();
        $config = Config::getInstance();
        $config->setValue('debug', true);
    }

    public function tearDown(){
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $controller = new TestController(true);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    /**
     * Test controller
     * @TODO Possibly load the resulting markup as a DOM object and test various children in it;
     * this would enforce valid markup
     */
    public function testControl() {
        $config = Config::getInstance();
        $controller = new TestController(true);
        $results = $controller->go();

        $this->assertEqual('text/html', $controller->getContentType());
        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('test'), 'Testing, testing, 123');
        $this->assertEqual($v_mgr->getTemplateDataItem('app_title'), 'ThinkUp');
        $this->assertEqual($results, '<a href="'.$config->getValue('site_root_path').
        'index.php">ThinkUp</a>: Testing, testing, 123 | Not logged in', "controller output");
    }

    /**
     * Test cache key, no params
     * @TODO Possibly load the resulting markup as a DOM object and test various children in it;
     * this would enforce valid markup
     */
    public function testCacheKeyNoRequestParams() {
        $config = Config::getInstance();
        $config->setValue('cache_pages', true);
        $controller = new TestController(true);
        $results = $controller->go();

        $this->assertEqual($controller->getCacheKeyString(), 'testme.tpl-');
    }

    /**
     * Test json output
     */
    public function testJsonOutput() {
        $config = Config::getInstance();
        $controller = new TestController(true);
        $_GET['json'] = true;
        $results = $controller->go();
        unset($_GET['json']);
        $obj = json_decode($results);
        $this->assertIsA($obj, 'stdClass');
        $this->assertEqual($obj->aname, 'a value');
        $this->assertIsA($obj->alist, 'Array');
        $this->assertEqual( $controller->getContentType(),'application/json');
    }

    /**
     * Test adding script to header
     */
    public function testAddJsScript() {
        $config = Config::getInstance();
        $controller = new TestController(true);
        $controller->addHeaderJavaScript('plugins/hellothinkup/assets/js/test.js');
        $results = $controller->go();

        //test if view javascript variable is set correctly
        $v_mgr = $controller->getViewManager();
        $scripts = $v_mgr->getTemplateDataItem('header_scripts');
        $this->assertEqual($scripts[0], 'plugins/hellothinkup/assets/js/test.js');
    }

    /**
     * Test setting content type header
     */
    public function testAddHeader() {
        $config = Config::getInstance();
        $controller = new TestController(true);
        $_GET['text'] = true;

        $results = $controller->go();
        $this->assertEqual( $controller->getContentType(),'text/plain');
    }

    /**
     * Test exception handling
     */
    public function testExceptionHandling() {
        $_GET['throwexception'] = 'yesindeedy';
        $controller = new TestController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('Testing exception handling!', $v_mgr->getTemplateDataItem('errormsg'));
    }
}
