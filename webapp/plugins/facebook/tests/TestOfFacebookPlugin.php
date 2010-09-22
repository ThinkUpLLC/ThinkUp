<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}

require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/model/class.FacebookPlugin.php';

class TestOfFacebookPlugin extends ThinkUpUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('FacebookPlugin class test');
    }

    public function setUp() {
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('facebook', 'FacebookPlugin');
        $webapp->setActivePlugin('facebook');
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testWebappTabRegistration() {
        $webapp = Webapp::getInstance();
        $logger = Logger::getInstance();
        $pd = DAOFactory::getDAO('PostDAO');
        $instance = new Instance();
        $instance->network_user_id = 1;

        $post_tabs = $webapp->getChildTabsUnderPosts($instance);

        $this->assertEqual(sizeof($post_tabs), 2, "Test number of post tabs");
        $first_post_tab = $post_tabs[0];
        $this->assertEqual($first_post_tab->short_name, "all_facebook_posts", "Test short name of first post tab");
        $this->assertEqual($first_post_tab->name, "All", "Test name of first post tab");
        $this->assertEqual($first_post_tab->description, "", "Test description of first post tab");

        $first_post_tab_datasets = $first_post_tab->getDatasets();
        $first_post_tab_dataset = $first_post_tab_datasets[0];
        $this->assertEqual($first_post_tab_dataset->name, "all_facebook_posts",
        "Test first post tab's first dataset name");
        $this->assertEqual($first_post_tab_dataset->dao_name, 'PostDAO');
        $this->assertEqual($first_post_tab_dataset->dao_method_name, "getAllPosts",
        "Test first post tab's first dataset fetching method");
        $logger->close();
    }
}