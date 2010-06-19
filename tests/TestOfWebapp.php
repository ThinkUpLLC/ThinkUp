<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.ThinkTankPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.CrawlerPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.WebappPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTab.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTabDataset.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';

require_once $SOURCE_ROOT_PATH.'webapp/plugins/hellothinktank/model/class.HelloThinkTankPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';

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

        $this->expectException( new Exception(
        "The HelloThinkTankPlugin object does not have a getChildTabsUnderPosts method.") );
        $webapp->getChildTabsUnderPosts(null);
    }

    /**
     * Test getTab
     */
    public function testGetTab() {
        $webapp = Webapp::getInstance();
        $config = Config::getInstance();
        $webapp->registerPlugin('twitter', "TwitterPlugin");
        $webapp->setActivePlugin('twitter');

        $instance = new Instance();
        $instance->network_user_id = 930061;

        $tab = $webapp->getTab('tweets-all', $instance);
        $this->assertIsA($tab, 'WebappTab');
        $this->assertEqual($tab->view_template, Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl', "Template ");
        $this->assertEqual($tab->short_name, 'tweets-all', "Short name");
        $this->assertEqual($tab->name, 'All', "Name");
        $this->assertEqual($tab->description, 'All tweets', "Description");
        $this->assertIsA($tab->datasets, 'array');
        $this->assertEqual(sizeOf($tab->datasets), 1);

        $tab = $webapp->getTab('nonexistent', $instance);
        $this->assertEqual($tab, null);
    }
}