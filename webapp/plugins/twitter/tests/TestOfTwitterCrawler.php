<?php
if (!isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Follow.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php';
//require_once $SOURCE_ROOT_PATH.'extlib/twitteroauth/twitteroauth.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterCrawler.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkTank.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.RetweetDetector.php';

class TestOfTwitterCrawler extends ThinkTankUnitTestCase {
    var $api;
    var $instance;
    var $logger;

    function TestOfTwitterCrawler() {
        $this->UnitTestCase('TwitterCrawler test');
    }

    function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();

        //insert test users
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, last_updated) VALUES (36823, 'anildash', 'Anil Dash', 'avatar.jpg', '2007-01-01');";
        $this->db->exec($q);

        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, last_updated) VALUES (930061, 'ginatrapani', 'Gina Trapani', 'avatar.jpg', '2007-01-01');";
        $this->db->exec($q);

        //insert test follow
        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (930061, 36823, '2006-01-08 23:54:41');";
        $this->db->exec($q);
    }

    function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    function setUpInstanceUserAnilDash() {
        global $THINKTANK_CFG;
        $r = array('id'=>1, 'network_username'=>'anildash', 'network_user_id'=>'36823', 'network_viewer_id'=>'36823', 'last_status_id'=>'0', 'last_page_fetched_replies'=>0, 'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0', 'total_users_in_system'=>'0', 'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'', 'api_calls_to_leave_unmade_per_minute'=>2, 'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'twitter');
        $this->instance = new Instance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth('111', '222', $THINKTANK_CFG['oauth_consumer_key'], $THINKTANK_CFG['oauth_consumer_secret'], $this->instance, $THINKTANK_CFG['archive_limit']);

        $this->api->available = true;
        $this->api->available_api_calls_for_crawler = 20;
        $this->instance->is_archive_loaded_follows = true;
    }

    function setUpInstanceUserGinaTrapani() {
        global $THINKTANK_CFG;
        $r = array('id'=>1, 'network_username'=>'ginatrapani', 'network_user_id'=>'930061', 'network_viewer_id'=>'930061', 'last_status_id'=>'0', 'last_page_fetched_replies'=>0, 'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0', 'total_users_in_system'=>'0', 'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'', 'api_calls_to_leave_unmade_per_minute'=>2, 'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'twitter');
        $this->instance = new Instance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth('111', '222', $THINKTANK_CFG['oauth_consumer_key'], $THINKTANK_CFG['oauth_consumer_secret'], $this->instance, $THINKTANK_CFG['archive_limit']);
        $this->api->available = true;
        $this->api->available_api_calls_for_crawler = 20;
        $this->instance->is_archive_loaded_follows = true;
    }

//    function testConstructor() {
//        self::setUpInstanceUserAnilDash();
//        $tc = new TwitterCrawler($this->instance, $this->api, $this->db);
//
//        $this->assertTrue($tc != null);
//    }
//
//    function testFetchInstanceUserInfo() {
//        self::setUpInstanceUserAnilDash();
//
//        $tc = new TwitterCrawler($this->instance, $this->api, $this->db);
//
//        $tc->fetchInstanceUserInfo();
//
//        $udao = new UserDAO($this->db, $this->logger);
//        $user = $udao->getDetails(36823);
//        $this->assertTrue($user->id == 1);
//        $this->assertTrue($user->user_id == 36823);
//        $this->assertTrue($user->username == 'anildash');
//        $this->assertTrue($user->found_in == 'Owner Status');
//    }
//
//    function testFetchSearchResults() {
//        self::setUpInstanceUserAnilDash();
//        $tc = new TwitterCrawler($this->instance, $this->api, $this->db);
//
//        $tc->fetchInstanceUserInfo();
//        $tc->fetchSearchResults('@whitehouse');
//        $pdao = new PostDAO($this->db, $this->logger);
//        $this->assertTrue($pdao->isPostInDB(11837263794));
//
//        $post = $pdao->getPost(11837263794);
//        $this->assertEqual($post->post_text, "RT @whitehouse: The New Start Treaty: Read the text and remarks by President Obama &amp; President Medvedev http://bit.ly/cAm9hF");
//    }
//
//    function testFetchInstanceUserFollowers() {
//        self::setUpInstanceUserAnilDash();
//        $this->instance->is_archive_loaded_follows = false;
//        $tc = new TwitterCrawler($this->instance, $this->api, $this->db);
//
//        $tc->fetchInstanceUserFollowers();
//        $fdao = new FollowDAO($this->db);
//        $this->assertTrue($fdao->followExists(36823, 119950880), 'new follow exists');
//
//        $udao = new UserDAO($this->db);
//        $updated_user = $udao->getUserByName('meatballhat');
//        $this->assertEqual($updated_user->full_name, 'Dan Buch', 'follower full name set');
//        $this->assertEqual($updated_user->location, 'Bedford, OH', 'follower locaiton set');
//    }
//
//    function testFetchInstanceUserFriends() {
//        self::setUpInstanceUserAnilDash();
//        $tc = new TwitterCrawler($this->instance, $this->api, $this->db);
//        $tc->fetchInstanceUserInfo();
//
//        $tc->fetchInstanceUserFriends();
//        $fdao = new FollowDAO($this->db);
//        $this->assertTrue($fdao->followExists(14834340, 36823), 'new friend exists');
//
//        $udao = new UserDAO($this->db);
//        $updated_user = $udao->getUserByName('jayrosen_nyu');
//        $this->assertEqual($updated_user->full_name, 'Jay Rosen', 'friend full name set');
//        $this->assertEqual($updated_user->location, 'New York City', 'friend locaiton set');
//    }
//
//    function testFetchInstanceUserFriendsByIds() {
//        self::setUpInstanceUserAnilDash();
//        $tc = new TwitterCrawler($this->instance, $this->api, $this->db);
//        $tc->fetchInstanceUserInfo();
//
//        $fd = new FollowDAO($this->db);
//        $stale_friend = $fd->getStalestFriend($this->instance->network_user_id);
//        $this->assertTrue(isset($stale_friend), 'there is a stale friend');
//        $this->assertEqual($stale_friend->user_id, 930061, 'stale friend is ginatrapani');
//        $this->assertEqual($stale_friend->username, 'ginatrapani', 'stale friend is ginatrapani');
//
//        $tc->fetchFriendTweetsAndFriends();
//        $fdao = new FollowDAO($this->db);
//        $this->assertTrue($fdao->followExists(14834340, 930061), 'ginatrapani friend loaded');
//    }
//
//    function testFetchInstanceUserFollowersByIds() {
//        self::setUpInstanceUserAnilDash();
//        $this->api->available_api_calls_for_crawler = 2;
//        $tc = new TwitterCrawler($this->instance, $this->api, $this->db);
//        $tc->fetchInstanceUserInfo();
//
//        $tc->fetchInstanceUserFollowers();
//        $fdao = new FollowDAO($this->db);
//        $this->assertTrue($fdao->followExists(36823, 114811186), 'new follow exists');
//    }

    function testFetchRetweetsOfInstanceuser() {
        self::setUpInstanceUserGinaTrapani();
        $tc = new TwitterCrawler($this->instance, $this->api, $this->db);
        $tc->fetchInstanceUserInfo();

        //first, load retweeted tweet into db
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar, post_text, source, pub_date, mention_count_cache, retweet_count_cache) VALUES (14947487415, 930061, 'ginatrapani', 'Gina Trapani', 'avatar.jpg', '&quot;Wearing your new conference tee shirt does NOT count as dressing up.&quot;', 'web', '2006-01-01 00:00:00', ".rand(0, 4).", 0);";
        $this->db->exec($q);

        $pdao = new PostDAO($this->db);
        $tc->fetchRetweetsOfInstanceUser();
        $post = $pdao->getPost(14947487415);
        $this->assertEqual($post->retweet_count_cache, 6, '6 retweets loaded');
        $retweets = $pdao->getRetweetsOfPost(14947487415, true);
        $this->assertEqual(sizeof($retweets), 6, '6 retweets loaded');

        //make sure duplicate posts aren't going into the db on next crawler run
        self::setUpInstanceUserGinaTrapani();
        $tc = new TwitterCrawler($this->instance, $this->api, $this->db);
        $tc->fetchInstanceUserInfo();

        $tc->fetchRetweetsOfInstanceUser();
        $post = $pdao->getPost(14947487415);
        $this->assertEqual($post->retweet_count_cache, 6, '6 retweets loaded');
        $retweets = $pdao->getRetweetsOfPost(14947487415, true);
        $this->assertEqual(sizeof($retweets), 6, '6 retweets loaded');
    }

    //TODO: Test the rest of the TwitterCrawler methods
}
?>
