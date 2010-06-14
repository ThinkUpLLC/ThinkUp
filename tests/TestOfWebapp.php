<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.ThinkTankPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.CrawlerPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/hellothinktank/model/class.HelloThinkTankPlugin.php';

/**
 * Test Webapp object
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfWebapp extends ThinkTankBasicUnitTestCase {

    /**
     * Constructor
     */
    function __construct() {
        $this->UnitTestCase('Webapp class test');
    }

    /**
     * Set up test
     */
    function setUp() {
        parent::setUp();
    }

    /**
     * Tear down test
     */
    function tearDown() {
        parent::tearDown();
    }

    /**
     * Test Webapp singleton instantiation
     */
    public function testWebappSingleton() {
        $webapp = Webapp::getInstance();
        //test default active plugin
        $this->assertEqual($webapp->getActivePlugin(), "twitter");
    }

    /**
     * Test activePlugin getter/setter
     */
    public function testWebappGetSetActivePlugin() {
        $webapp = Webapp::getInstance();
        $this->assertEqual($webapp->getActivePlugin(), "twitter");
        $webapp->setActivePlugin('facebook');
        $this->assertEqual($webapp->getActivePlugin(), "facebook");

        //make sure another instance reports back the same values
        $webapp_two = Webapp::getInstance();
        $this->assertEqual($webapp_two->getActivePlugin(), "facebook");
    }

    /**
     * Test registerPlugin when plugin object does not have the right methods available
     */
    public function testWebappRegisterPluginWithoutWebappInterfaceImplemented() {
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('hellothinktank', "HelloThinkTankPlugin");
        $webapp->setActivePlugin('hellothinktank');

        $this->expectException( new Exception("The HelloThinkTankPlugin object does not have a getChildTabsUnderPosts method.") );
        $webapp->getChildTabsUnderPosts(null);
    }
}