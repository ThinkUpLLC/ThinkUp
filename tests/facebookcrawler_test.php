<?php
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("classes/class.ThinkTankTestCase.php");
require_once ("common/class.User.php");
require_once ("common/class.Instance.php");
require_once ("common/class.OwnerInstance.php");
require_once ("common/class.User.php");
require_once ("common/class.Link.php");
require_once ("common/class.Post.php");
require_once ("plugins/facebook/lib/class.FacebookCrawler.php");
//require_once ("plugins/facebook/lib/facebook.php");
require_once ("classes/mock.facebook.php");

class TestOfFacebookCrawler extends ThinkTankUnitTestCase {
    var $fb;
    var $instance;

    function TestOfFacebookCrawler() {
        $this->UnitTestCase('FacebookCrawler test');
    }

    function setUp() {
        parent::setUp();

        global $THINKTANK_CFG;
        $r = array('id'=>1, 'network_username'=>'Penelope Caridad', 'network_user_id'=>'606837591', 'last_status_id'=>'0', 'last_page_fetched_replies'=>0, 'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0', 'total_users_in_system'=>'0', 'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'', 'api_calls_to_leave_unmade_per_minute'=>2, 'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'facebook');
        $this->instance = new Instance($r);

        $this->fb = new Facebook($THINKTANK_CFG['facebook_api_key'], $THINKTANK_CFG['facebook_api_secret']);
    }

    function tearDown() {
        parent::tearDown();
    }

    function testFetchUserPagesThatUserIsaFanOf() {

        $this->instance->network_user_id='606837591';
        $fbc = new FacebookCrawler($this->instance, $this->logger, $this->fb, $this->db);

        $session_key = '78c23c7f3bb853be336b9668-606837591';
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



    function testConstructor() {
        $fbc = new FacebookCrawler($this->instance, $this->logger, $this->fb, $this->db);

        $this->assertTrue($fbc != null);
    }

    function testFetchInstanceUserInfo() {
        $fbc = new FacebookCrawler($this->instance, $this->logger, $this->fb, $this->db);

        $session_key = 'f8c16f44b43083fc2545a46d-606837591';
        $fbc->fetchInstanceUserInfo($this->instance->network_user_id, $session_key);

    }

    function testFetchUserStreamWithTwoPostsNoComments() {
        /*
         $this->assertTrue(unserialize('a:2:{s:5:"posts";a:2:{i:0;a:21:{s:7:"post_id";s:25:"606837591_108956622464235";s:9:"viewer_id";s:9:"606837591";s:9:"source_id";s:9:"606837591";s:4:"type";s:2:"46";s:6:"app_id";s:0:"";s:11:"attribution";s:0:"";s:8:"actor_id";s:9:"606837591";s:9:"target_id";s:0:"";s:7:"message";s:50:"The Pacific is really good. Can\'t wait for part 3.";s:10:"attachment";a:1:{s:11:"description";s:0:"";}s:8:"app_data";s:0:"";s:12:"action_links";s:0:"";s:8:"comments";a:4:{s:10:"can_remove";s:1:"1";s:8:"can_post";s:1:"1";s:5:"count";s:1:"0";s:12:"comment_list";s:0:"";}s:5:"likes";a:6:{s:4:"href";s:82:"http://www.facebook.com/social_graph.php?node_id=108956622464235&class=LikeManager";s:5:"count";s:1:"0";s:6:"sample";s:0:"";s:7:"friends";s:0:"";s:10:"user_likes";s:1:"0";s:8:"can_like";s:1:"1";}s:7:"privacy";a:6:{s:11:"description";s:12:"Only Friends";s:5:"value";s:11:"ALL_FRIENDS";s:7:"friends";s:0:"";s:8:"networks";s:0:"";s:5:"allow";s:0:"";s:4:"deny";s:0:"";}s:12:"updated_time";s:10:"1269488627";s:12:"created_time";s:10:"1269488627";s:10:"tagged_ids";s:0:"";s:9:"is_hidden";s:1:"0";s:10:"filter_key";s:34:"f8c16f44b43083fc2545a46d-606837591";s:9:"permalink";s:82:"http://www.facebook.com/profile.php?v=feed&story_fbid=108956622464235&id=606837591";}i:1;a:21:{s:7:"post_id";s:25:"606837591_107266209295210";s:9:"viewer_id";s:9:"606837591";s:9:"source_id";s:9:"606837591";s:4:"type";s:2:"46";s:6:"app_id";s:0:"";s:11:"attribution";s:0:"";s:8:"actor_id";s:9:"606837591";s:9:"target_id";s:0:"";s:7:"message";s:23:"SHAKE IT LIKE A POM-POM";s:10:"attachment";a:1:{s:11:"description";s:0:"";}s:8:"app_data";s:0:"";s:12:"action_links";s:0:"";s:8:"comments";a:4:{s:10:"can_remove";s:1:"1";s:8:"can_post";s:1:"1";s:5:"count";s:1:"0";s:12:"comment_list";s:0:"";}s:5:"likes";a:6:{s:4:"href";s:82:"http://www.facebook.com/social_graph.php?node_id=107266209295210&class=LikeManager";s:5:"count";s:1:"0";s:6:"sample";s:0:"";s:7:"friends";s:0:"";s:10:"user_likes";s:1:"0";s:8:"can_like";s:1:"1";}s:7:"privacy";a:6:{s:11:"description";s:12:"Only Friends";s:5:"value";s:11:"ALL_FRIENDS";s:7:"friends";s:0:"";s:8:"networks";s:0:"";s:5:"allow";s:0:"";s:4:"deny";s:0:"";}s:12:"updated_time";s:10:"1269411918";s:12:"created_time";s:10:"1269411918";s:10:"tagged_ids";s:0:"";s:9:"is_hidden";s:1:"0";s:10:"filter_key";s:34:"f8c16f44b43083fc2545a46d-606837591";s:9:"permalink";s:82:"http://www.facebook.com/profile.php?v=feed&story_fbid=107266209295210&id=606837591";}}s:8:"profiles";a:1:{i:0;a:5:{s:2:"id";s:9:"606837591";s:3:"url";s:0:"";s:4:"name";s:12:"Gina Trapani";s:10:"pic_square";s:60:"http://profile.ak.fbcdn.net/v222/1942/94/q606837591_9678.jpg";s:4:"type";s:4:"user";}}}'));
         $stream = unserialize('a:2:{s:5:"posts";a:2:{i:0;a:21:{s:7:"post_id";s:25:"606837591_108956622464235";s:9:"viewer_id";s:9:"606837591";s:9:"source_id";s:9:"606837591";s:4:"type";s:2:"46";s:6:"app_id";s:0:"";s:11:"attribution";s:0:"";s:8:"actor_id";s:9:"606837591";s:9:"target_id";s:0:"";s:7:"message";s:50:"The Pacific is really good. Can\'t wait for part 3.";s:10:"attachment";a:1:{s:11:"description";s:0:"";}s:8:"app_data";s:0:"";s:12:"action_links";s:0:"";s:8:"comments";a:4:{s:10:"can_remove";s:1:"1";s:8:"can_post";s:1:"1";s:5:"count";s:1:"0";s:12:"comment_list";s:0:"";}s:5:"likes";a:6:{s:4:"href";s:82:"http://www.facebook.com/social_graph.php?node_id=108956622464235&class=LikeManager";s:5:"count";s:1:"0";s:6:"sample";s:0:"";s:7:"friends";s:0:"";s:10:"user_likes";s:1:"0";s:8:"can_like";s:1:"1";}s:7:"privacy";a:6:{s:11:"description";s:12:"Only Friends";s:5:"value";s:11:"ALL_FRIENDS";s:7:"friends";s:0:"";s:8:"networks";s:0:"";s:5:"allow";s:0:"";s:4:"deny";s:0:"";}s:12:"updated_time";s:10:"1269488627";s:12:"created_time";s:10:"1269488627";s:10:"tagged_ids";s:0:"";s:9:"is_hidden";s:1:"0";s:10:"filter_key";s:34:"f8c16f44b43083fc2545a46d-606837591";s:9:"permalink";s:82:"http://www.facebook.com/profile.php?v=feed&story_fbid=108956622464235&id=606837591";}i:1;a:21:{s:7:"post_id";s:25:"606837591_107266209295210";s:9:"viewer_id";s:9:"606837591";s:9:"source_id";s:9:"606837591";s:4:"type";s:2:"46";s:6:"app_id";s:0:"";s:11:"attribution";s:0:"";s:8:"actor_id";s:9:"606837591";s:9:"target_id";s:0:"";s:7:"message";s:23:"SHAKE IT LIKE A POM-POM";s:10:"attachment";a:1:{s:11:"description";s:0:"";}s:8:"app_data";s:0:"";s:12:"action_links";s:0:"";s:8:"comments";a:4:{s:10:"can_remove";s:1:"1";s:8:"can_post";s:1:"1";s:5:"count";s:1:"0";s:12:"comment_list";s:0:"";}s:5:"likes";a:6:{s:4:"href";s:82:"http://www.facebook.com/social_graph.php?node_id=107266209295210&class=LikeManager";s:5:"count";s:1:"0";s:6:"sample";s:0:"";s:7:"friends";s:0:"";s:10:"user_likes";s:1:"0";s:8:"can_like";s:1:"1";}s:7:"privacy";a:6:{s:11:"description";s:12:"Only Friends";s:5:"value";s:11:"ALL_FRIENDS";s:7:"friends";s:0:"";s:8:"networks";s:0:"";s:5:"allow";s:0:"";s:4:"deny";s:0:"";}s:12:"updated_time";s:10:"1269411918";s:12:"created_time";s:10:"1269411918";s:10:"tagged_ids";s:0:"";s:9:"is_hidden";s:1:"0";s:10:"filter_key";s:34:"f8c16f44b43083fc2545a46d-606837591";s:9:"permalink";s:82:"http://www.facebook.com/profile.php?v=feed&story_fbid=107266209295210&id=606837591";}}s:8:"profiles";a:1:{i:0;a:5:{s:2:"id";s:9:"606837591";s:3:"url";s:0:"";s:4:"name";s:12:"Gina Trapani";s:10:"pic_square";s:60:"http://profile.ak.fbcdn.net/v222/1942/94/q606837591_9678.jpg";s:4:"type";s:4:"user";}}}');
         $this->assertTrue(is_array($stream));
         $this->assertTrue(is_array($stream["posts"]));
         //print_r($stream["posts"]);
         */
        $fbc = new FacebookCrawler($this->instance, $this->logger, $this->fb, $this->db);

        $session_key = 'f8c16f44b43083fc2545a46d-606837591';
        $fbc->fetchUserPostsAndReplies($this->instance->network_user_id, $session_key);
         
        $pd = new PostDAO($this->db, $this->logger);
        $this->assertTrue($pd->isPostInDB('108956622464235'));
        $this->assertTrue($pd->isPostInDB('107266209295210'));
    }

    function testFetchUserStreamWithTwoPostsAndOneComment() {
        /*
         $this->assertTrue(unserialize('a:2:{s:5:"posts";a:2:{i:0;a:21:{s:7:"post_id";s:25:"606837591_108956622464235";s:9:"viewer_id";s:9:"606837591";s:9:"source_id";s:9:"606837591";s:4:"type";s:2:"46";s:6:"app_id";s:0:"";s:11:"attribution";s:0:"";s:8:"actor_id";s:9:"606837591";s:9:"target_id";s:0:"";s:7:"message";s:50:"The Pacific is really good. Can\'t wait for part 3.";s:10:"attachment";a:1:{s:11:"description";s:0:"";}s:8:"app_data";s:0:"";s:12:"action_links";s:0:"";s:8:"comments";a:4:{s:10:"can_remove";s:1:"1";s:8:"can_post";s:1:"1";s:5:"count";s:1:"0";s:12:"comment_list";s:0:"";}s:5:"likes";a:6:{s:4:"href";s:82:"http://www.facebook.com/social_graph.php?node_id=108956622464235&class=LikeManager";s:5:"count";s:1:"0";s:6:"sample";s:0:"";s:7:"friends";s:0:"";s:10:"user_likes";s:1:"0";s:8:"can_like";s:1:"1";}s:7:"privacy";a:6:{s:11:"description";s:12:"Only Friends";s:5:"value";s:11:"ALL_FRIENDS";s:7:"friends";s:0:"";s:8:"networks";s:0:"";s:5:"allow";s:0:"";s:4:"deny";s:0:"";}s:12:"updated_time";s:10:"1269488627";s:12:"created_time";s:10:"1269488627";s:10:"tagged_ids";s:0:"";s:9:"is_hidden";s:1:"0";s:10:"filter_key";s:34:"f8c16f44b43083fc2545a46d-606837591";s:9:"permalink";s:82:"http://www.facebook.com/profile.php?v=feed&story_fbid=108956622464235&id=606837591";}i:1;a:21:{s:7:"post_id";s:25:"606837591_107266209295210";s:9:"viewer_id";s:9:"606837591";s:9:"source_id";s:9:"606837591";s:4:"type";s:2:"46";s:6:"app_id";s:0:"";s:11:"attribution";s:0:"";s:8:"actor_id";s:9:"606837591";s:9:"target_id";s:0:"";s:7:"message";s:23:"SHAKE IT LIKE A POM-POM";s:10:"attachment";a:1:{s:11:"description";s:0:"";}s:8:"app_data";s:0:"";s:12:"action_links";s:0:"";s:8:"comments";a:4:{s:10:"can_remove";s:1:"1";s:8:"can_post";s:1:"1";s:5:"count";s:1:"0";s:12:"comment_list";s:0:"";}s:5:"likes";a:6:{s:4:"href";s:82:"http://www.facebook.com/social_graph.php?node_id=107266209295210&class=LikeManager";s:5:"count";s:1:"0";s:6:"sample";s:0:"";s:7:"friends";s:0:"";s:10:"user_likes";s:1:"0";s:8:"can_like";s:1:"1";}s:7:"privacy";a:6:{s:11:"description";s:12:"Only Friends";s:5:"value";s:11:"ALL_FRIENDS";s:7:"friends";s:0:"";s:8:"networks";s:0:"";s:5:"allow";s:0:"";s:4:"deny";s:0:"";}s:12:"updated_time";s:10:"1269411918";s:12:"created_time";s:10:"1269411918";s:10:"tagged_ids";s:0:"";s:9:"is_hidden";s:1:"0";s:10:"filter_key";s:34:"f8c16f44b43083fc2545a46d-606837591";s:9:"permalink";s:82:"http://www.facebook.com/profile.php?v=feed&story_fbid=107266209295210&id=606837591";}}s:8:"profiles";a:1:{i:0;a:5:{s:2:"id";s:9:"606837591";s:3:"url";s:0:"";s:4:"name";s:12:"Gina Trapani";s:10:"pic_square";s:60:"http://profile.ak.fbcdn.net/v222/1942/94/q606837591_9678.jpg";s:4:"type";s:4:"user";}}}'));
         $stream = unserialize('a:2:{s:5:"posts";a:2:{i:0;a:21:{s:7:"post_id";s:25:"606837591_108956622464235";s:9:"viewer_id";s:9:"606837591";s:9:"source_id";s:9:"606837591";s:4:"type";s:2:"46";s:6:"app_id";s:0:"";s:11:"attribution";s:0:"";s:8:"actor_id";s:9:"606837591";s:9:"target_id";s:0:"";s:7:"message";s:50:"The Pacific is really good. Can\'t wait for part 3.";s:10:"attachment";a:1:{s:11:"description";s:0:"";}s:8:"app_data";s:0:"";s:12:"action_links";s:0:"";s:8:"comments";a:4:{s:10:"can_remove";s:1:"1";s:8:"can_post";s:1:"1";s:5:"count";s:1:"0";s:12:"comment_list";s:0:"";}s:5:"likes";a:6:{s:4:"href";s:82:"http://www.facebook.com/social_graph.php?node_id=108956622464235&class=LikeManager";s:5:"count";s:1:"0";s:6:"sample";s:0:"";s:7:"friends";s:0:"";s:10:"user_likes";s:1:"0";s:8:"can_like";s:1:"1";}s:7:"privacy";a:6:{s:11:"description";s:12:"Only Friends";s:5:"value";s:11:"ALL_FRIENDS";s:7:"friends";s:0:"";s:8:"networks";s:0:"";s:5:"allow";s:0:"";s:4:"deny";s:0:"";}s:12:"updated_time";s:10:"1269488627";s:12:"created_time";s:10:"1269488627";s:10:"tagged_ids";s:0:"";s:9:"is_hidden";s:1:"0";s:10:"filter_key";s:34:"f8c16f44b43083fc2545a46d-606837591";s:9:"permalink";s:82:"http://www.facebook.com/profile.php?v=feed&story_fbid=108956622464235&id=606837591";}i:1;a:21:{s:7:"post_id";s:25:"606837591_107266209295210";s:9:"viewer_id";s:9:"606837591";s:9:"source_id";s:9:"606837591";s:4:"type";s:2:"46";s:6:"app_id";s:0:"";s:11:"attribution";s:0:"";s:8:"actor_id";s:9:"606837591";s:9:"target_id";s:0:"";s:7:"message";s:23:"SHAKE IT LIKE A POM-POM";s:10:"attachment";a:1:{s:11:"description";s:0:"";}s:8:"app_data";s:0:"";s:12:"action_links";s:0:"";s:8:"comments";a:4:{s:10:"can_remove";s:1:"1";s:8:"can_post";s:1:"1";s:5:"count";s:1:"0";s:12:"comment_list";s:0:"";}s:5:"likes";a:6:{s:4:"href";s:82:"http://www.facebook.com/social_graph.php?node_id=107266209295210&class=LikeManager";s:5:"count";s:1:"0";s:6:"sample";s:0:"";s:7:"friends";s:0:"";s:10:"user_likes";s:1:"0";s:8:"can_like";s:1:"1";}s:7:"privacy";a:6:{s:11:"description";s:12:"Only Friends";s:5:"value";s:11:"ALL_FRIENDS";s:7:"friends";s:0:"";s:8:"networks";s:0:"";s:5:"allow";s:0:"";s:4:"deny";s:0:"";}s:12:"updated_time";s:10:"1269411918";s:12:"created_time";s:10:"1269411918";s:10:"tagged_ids";s:0:"";s:9:"is_hidden";s:1:"0";s:10:"filter_key";s:34:"f8c16f44b43083fc2545a46d-606837591";s:9:"permalink";s:82:"http://www.facebook.com/profile.php?v=feed&story_fbid=107266209295210&id=606837591";}}s:8:"profiles";a:1:{i:0;a:5:{s:2:"id";s:9:"606837591";s:3:"url";s:0:"";s:4:"name";s:12:"Gina Trapani";s:10:"pic_square";s:60:"http://profile.ak.fbcdn.net/v222/1942/94/q606837591_9678.jpg";s:4:"type";s:4:"user";}}}');
         $this->assertTrue(is_array($stream));
         $this->assertTrue(is_array($stream["posts"]));
         //print_r($stream["posts"]);
         */
        $this->instance->network_user_id='6068375911';
        $fbc = new FacebookCrawler($this->instance, $this->logger, $this->fb, $this->db);

        $session_key = 'f8c16f44b43083fc2545a46d-606837591';
        $fbc->fetchUserPostsAndReplies($this->instance->network_user_id, $session_key);

        $pd = new PostDAO($this->db, $this->logger);
        $p = $pd->getPost('108956622464235');
        $this->assertTrue($p->mention_count_cache == 1);

        $p = $pd->getPost('107266209295210');
        $this->assertTrue($p->mention_count_cache == 0);
    }


}
?>
