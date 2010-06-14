<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}

require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);


require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PluginHook.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Crawler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Webapp.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';

require_once $SOURCE_ROOT_PATH.'webapp/model/interface.ThinkTankPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.CrawlerPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.WebappPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTab.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.WebappTabDataset.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/model/class.FacebookPlugin.php';

class TestOfFacebookPlugin extends ThinkTankUnitTestCase {
    function __construct() {
        $this->UnitTestCase('FacebookPlugin class test');
    }

    function setUp() {
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('facebook', 'FacebookPlugin');
        $webapp->setActivePlugin('facebook');
    }

    function tearDown() {
        parent::tearDown();
    }

    function testWebappTabRegistration() {
        $webapp = Webapp::getInstance();
        $logger = Logger::getInstance();
        $pd = DAOFactory::getDAO('PostDAO');
        $instance = new Instance();
        $instance->network_user_id = 1;

        $post_tabs = $webapp->getChildTabsUnderPosts($instance);

        $this->assertEqual(sizeof($post_tabs), 1, "Test number of post tabs");
        $first_post_tab = $post_tabs[0];
        $this->assertEqual($first_post_tab->short_name, "all_facebook_posts", "Test short name of first post tab");
        $this->assertEqual($first_post_tab->name, "All", "Test name of first post tab");
        $this->assertEqual($first_post_tab->description, "", "Test description of first post tab");

        $first_post_tab_datasets = $first_post_tab->getDatasets();
        $first_post_tab_dataset = $first_post_tab_datasets[0];
        $this->assertEqual($first_post_tab_dataset->name, "all_facebook_posts", "Test first post tab's first dataset name");
        $this->assertEqual($first_post_tab_dataset->fetching_method, "getAllPosts", "Test first post tab's first dataset fetching method");
        $logger->close();
    }
}