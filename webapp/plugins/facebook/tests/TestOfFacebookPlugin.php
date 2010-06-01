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

/* Replicate all the global objects a plugin depends on; normally this is done in init.php */
// TODO Figure out a better way to do all this than global objects in init.php
$crawler = new Crawler();
$webapp = new Webapp();
$i = new Instance(array("network_user_id"=>10, "id"=>1, "network_username"=>'test', "last_status_id"=>0, "last_page_fetched_replies"=>1, "last_page_fetched_tweets"=>0, "total_posts_in_system"=>20, "total_replies_in_system"=>10, "total_follows_in_system"=>10, "total_users_in_system"=>12, "is_archive_loaded_replies"=>0, "is_archive_loaded_follows"=>1, "crawler_last_run"=>"1/1/2010", "earliest_reply_in_system"=>"1/2/2009", "api_calls_to_leave_unmade_per_minute"=>2, "avg_replies_per_day"=>5, "network"=>"twitter", "is_public"=>0, "is_active"=>0, "network_viewer_id"=>101));

// Instantiate global database variable
try {
    $db = new Database($THINKTANK_CFG);
    $conn = $db->getConnection();
}
catch(Exception $e) {
    echo $e->getMessage();
}

class TestOfFacebookPlugin extends ThinkTankUnitTestCase {
    function TestOfFacebookPlugin() {
        $this->UnitTestCase('FacebookPlugin class test');
    }

    function setUp() {
        global $webapp;
        parent::setUp();
        $webapp->registerPlugin('facebook', 'FacebookPlugin');
        $webapp->setActivePlugin('facebook');
    }

    function tearDown() {
        parent::tearDown();
    }

    function testWebappTabRegistration() {
        global $webapp;
        $logger = Logger::getInstance();
        $pd = new PostDAO($this->db, $logger);
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