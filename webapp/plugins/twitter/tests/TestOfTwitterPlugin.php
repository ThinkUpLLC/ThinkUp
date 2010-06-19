<?php
if (!isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS) {
    require_once '../../../../tests/config.tests.inc.php';
}

require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);


require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/interface.Controller.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankController.php';
require_once $SOURCE_ROOT_PATH.'webapp/controller/class.ThinkTankAuthController.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PluginHook.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Crawler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Webapp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';

require_once $SOURCE_ROOT_PATH.'webapp/model/interface.ThinkTankPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.CrawlerPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.WebappPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTab.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTabDataset.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';

/**
 * Test of TwitterPlugin class
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfTwitterPlugin extends ThinkTankUnitTestCase {
    var $logger;
    var $webapp;
    var $crawler;

    function __construct() {
        $this->UnitTestCase('TwitterPlugin class test');
    }

    function setUp() {
        parent::setUp();
        $this->webapp = Webapp::getInstance();
        $this->crawler = Crawler::getInstance();
        $this->webapp->registerPlugin('twitter', 'TwitterPlugin');
        $this->crawler->registerCrawlerPlugin('TwitterPlugin');
        $this->webapp->setActivePlugin('twitter');
        $this->logger = Logger::getInstance();
    }

    function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    function testWebappTabRegistration() {
        $pd = DAOFactory::getDAO('PostDAO');
        $instance = new Instance();
        $instance->network_user_id = 1;

        $post_tabs = $this->webapp->getChildTabsUnderPosts($instance);

        $this->assertEqual(sizeof($post_tabs), 4, "Test number of post tabs");
        $first_post_tab = $post_tabs[0];
        $this->assertEqual($first_post_tab->short_name, "tweets-all", "Test short name of first post tab");
        $this->assertEqual($first_post_tab->name, "All", "Test name of first post tab");
        $this->assertEqual($first_post_tab->description, "All tweets", "Test description of first post tab");

        $first_post_tab_datasets = $first_post_tab->getDatasets();
        $first_post_tab_dataset = $first_post_tab_datasets[0];
        $this->assertEqual($first_post_tab_dataset->name, "all_tweets", "Test first post tab's first dataset name");
        $this->assertEqual($first_post_tab_dataset->dao_name, 'PostDAO');
        $this->assertEqual($first_post_tab_dataset->dao_method_name, "getAllPosts", 
        "Test first post tab's first dataset fetching method");
    }
}