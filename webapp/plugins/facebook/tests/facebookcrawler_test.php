<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/common/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/common/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/common/class.OwnerInstance.php';
require_once $SOURCE_ROOT_PATH.'webapp/common/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/common/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/lib/class.FacebookCrawler.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/tests/classes/mock.facebook.php';
//require_once $SOURCE_ROOT_PATH.'webapp/plugins/facebook/lib/facebook.php';

class TestOfFacebookCrawler extends ThinkTankUnitTestCase {
    var $fb;
    var $instance;

    function TestOfFacebookCrawler() {
        $this->UnitTestCase('FacebookCrawler test');
    }

    function setUp() {
        parent::setUp();

        global $THINKTANK_CFG;
        $r = array('id'=>1, 'network_username'=>'Penelope Caridad', 'network_user_id'=>'606837591', 'network_viewer_id'=>'606837591', 'last_status_id'=>'0', 'last_page_fetched_replies'=>0, 'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0', 'total_users_in_system'=>'0', 'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'', 'api_calls_to_leave_unmade_per_minute'=>2, 'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'facebook');
        $this->instance = new Instance($r);

        $this->fb = new Facebook($THINKTANK_CFG['facebook_api_key'], $THINKTANK_CFG['facebook_api_secret']);
    }

    function tearDown() {
        parent::tearDown();
    }

    function testConstructor() {
        $fbc = new FacebookCrawler($this->instance, $this->logger, $this->fb, $this->db);

        $this->assertTrue($fbc != null);
    }

    function testFetchInstanceUserInfo() {
        $fbc = new FacebookCrawler($this->instance, $this->logger, $this->fb, $this->db);

        $session_key = 'adsfasdfasdfasdf';
        $fbc->fetchInstanceUserInfo($this->instance->network_user_id, $session_key);

    }

    function testFetchUserStreamWithTwoPostsNoComments() {

        $fbc = new FacebookCrawler($this->instance, $this->logger, $this->fb, $this->db);

        $session_key = 'asdfasdfasdfafsd';
        $fbc->fetchUserPostsAndReplies($this->instance->network_user_id, $session_key);

        $pd = new PostDAO($this->db, $this->logger);
        $this->assertTrue($pd->isPostInDB('108956622464235'));
        $this->assertTrue($pd->isPostInDB('107266209295210'));
    }

    function testFetchUserStreamWithTwoPostsAndOneComment() {

        $this->instance->network_user_id='6068375911';
        $fbc = new FacebookCrawler($this->instance, $this->logger, $this->fb, $this->db);

        $session_key = 'adfasdfasdfasdf';
        $fbc->fetchUserPostsAndReplies($this->instance->network_user_id, $session_key);

        $pd = new PostDAO($this->db, $this->logger);
        $p = $pd->getPost('108956622464235');
        $this->assertTrue($p->mention_count_cache == 1);

        $p = $pd->getPost('107266209295210');
        $this->assertTrue($p->mention_count_cache == 0);
    }


    function testFetchUserPagesThatUserIsaFanOf() {

        $this->instance->network_user_id='606837591';
        $fbc = new FacebookCrawler($this->instance, $this->logger, $this->fb, $this->db);

        $session_key = 'asdfasdfasdfa';
        $pages = $fbc->fetchPagesUserIsFanOf($this->instance->network_user_id, $session_key);
        //        print_r($pages);

        $this->assertEqual(sizeof($pages), 43);
        $this->assertEqual($pages[29]['page_id'], '63811549237');
        $this->assertEqual($pages[29]['name'], 'The White House');
        $this->assertEqual($pages[29]['page_url'], 'http://www.facebook.com/WhiteHouse');

        $this->assertEqual($pages[15]['page_id'], '110253595679921');
        $this->assertEqual($pages[15]['name'], 'The Shawshank Redemption (1994)');
        $this->assertEqual($pages[15]['page_url'], 'http://www.imdb.com/title/tt0111161/');

    }

    function testFetchPageStream() {

        $this->instance->network_user_id='606837591';
        $fbc = new FacebookCrawler($this->instance, $this->logger, $this->fb, $this->db);

        $session_key = 'asdfasdfasdfadf';
        $page_id = '63811549237';
        $fbc->fetchPagePostsAndReplies($page_id, $this->instance->network_user_id, $session_key);

        $pd = new PostDAO($this->db, $this->logger);
        $p = $pd->getPost('125634574117714');
        $this->assertEqual($p->post_text, "Thanks for checking out the West Wing Week, your guide to everything that's happening at 1600 Pennsylvania Ave.");

    }


}
?>
