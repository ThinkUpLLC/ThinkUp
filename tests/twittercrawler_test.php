<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("classes/class.ThinkTankTestCase.php");
require_once ("common/class.User.php");
require_once ("common/class.Post.php");
require_once ("common/class.Link.php");
require_once ("common/class.Instance.php");
require_once ("classes/mock.TwitterOAuth.php");
//require_once ("plugins/twitter/lib/twitterOAuth.php");
require_once ("common/class.User.php");
require_once ("plugins/twitter/lib/class.TwitterAPIAccessorOAuth.php");
require_once ("plugins/twitter/lib/class.TwitterCrawler.php");

class TestOfTwitterCrawler extends ThinkTankUnitTestCase {
    var $api;
    var $instance;
    
    function TestOfTwitterCrawler() {
        $this->UnitTestCase('TwitterCrawler test');
    }
    
    function setUp() {
        parent::setUp();

        global $THINKTANK_CFG;
        $r = array('id'=>1, 'network_username'=>'anildash', 'network_user_id'=>'930061', 'last_status_id'=>'0', 'last_page_fetched_replies'=>0, 'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0', 'total_users_in_system'=>'0', 'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'', 'api_calls_to_leave_unmade_per_minute'=>2, 'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'twitter');
        $this->instance = new Instance($r);
        
        $this->api = new CrawlerTwitterAPIAccessorOAuth('111', '222', $THINKTANK_CFG['oauth_consumer_key'], $THINKTANK_CFG['oauth_consumer_secret'], $this->instance, $THINKTANK_CFG['archive_limit']);
    }
    
    function tearDown() {
       parent::tearDown();
    }
    
    function testConstructor() {
        $tc = new TwitterCrawler($this->instance, $this->logger, $this->api, $this->db);
        
        $this->assertTrue($tc != null);
    }

    
    function testFetchInstanceUserInfo() {
        $tc = new TwitterCrawler($this->instance, $this->logger, $this->api, $this->db);
        
        $tc->fetchInstanceUserInfo();
        
        $udao = new UserDAO($this->db, $this->logger);
        $user = $udao->getDetails(36823);
        $this->assertTrue($user->id == 1);
        $this->assertTrue($user->user_id == 36823);
        $this->assertTrue($user->username == 'anildash');
        $this->assertTrue($user->found_in == 'Owner Status');
    }

    function testFetchSearchResults() {
    	$this->api->available = true;
		$this->api->available_api_calls_for_crawler = 1;
        $tc = new TwitterCrawler($this->instance, $this->logger, $this->api, $this->db);
        
		$tc->fetchInstanceUserInfo();
        $tc->fetchSearchResults('@whitehouse');
        $pdao = new PostDAO($this->db, $this->logger);
		$this->assertTrue($pdao->isPostInDB(11841192840));
		
		$post = $pdao->getPost(11841192840);
		$this->assertEqual($post->post_text, "RT @CindyPDX: @whitehouse PLS send to my President: http://familiesofautistickids.ning.com/video/through-my-eyes-thanh-bui  &lt;Does he remember our son?");

    }

    
    //TODO: Test the rest of the TwitterCrawler methods
    
}
?>
