<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("classes/class.ThinkTankTestCase.php");
require_once ("class.User.php");
require_once ("class.Instance.php");
require_once ("class.OwnerInstance.php");
require_once ("class.User.php");
require_once ("class.Post.php");
require_once ("plugins/facebook/lib/class.FacebookCrawler.php");
require_once ("plugins/facebook/lib/facebook.php");


class TestOfFacebookCrawler extends ThinkTankUnitTestCase {
    var $fb;
    var $instance;
    
    function TestOfFacebookCrawler() {
        $this->UnitTestCase('FacebookCrawler test');
    }
    
    function setUp() {
        parent::setUp();
        
        global $THINKTANK_CFG;
        $r = array('id'=>1, 'network_username'=>'Penelope Caridad', 'network_user_id'=>'606837591', 'last_status_id'=>'0', 'last_page_fetched_replies'=>0, 'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0', 'total_users_in_system'=>'0', 'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'', 'api_calls_to_leave_unmade_per_minute'=>2, 'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0');
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
    
    function testFetchUserStream() {
        $fbc = new FacebookCrawler($this->instance, $this->logger, $this->fb, $this->db);
		
        $session_key = 'f8c16f44b43083fc2545a46d-606837591';
        $fbc->fetchUserPostsAndReplies($this->instance->network_user_id, $session_key);
		//TODO: Mock up Facebook API interface that returns expected values without hitting FB itself
        
    }

    
}
?>
