<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterCrawler.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 */
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/init.tests.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterCrawler.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.RetweetDetector.php';

/**
 * Test of TwitterCrawler
 *
 * @TODO Test the rest of the TwitterCrawler methods
 * @TODO Add testFetchTweetsWithLinks, assert Links and images get inserted
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfTwitterCrawler extends ThinkUpUnitTestCase {
    var $api;
    var $instance;
    var $logger;

    public function __construct() {
        $this->UnitTestCase('TwitterCrawler test');
    }

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();

        //insert test users
        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, last_updated)
        VALUES (36823, 'anildash', 'Anil Dash', 'avatar.jpg', '2007-01-01');";
        $this->db->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, last_updated)
        VALUES (930061, 'ginatrapani', 'Gina Trapani', 'avatar.jpg', '2007-01-01');";
        $this->db->exec($q);

        //insert test follow
        $q = "INSERT INTO tu_follows (user_id, follower_id, last_seen)
        VALUES (930061, 36823, '2006-01-08 23:54:41');";
        $this->db->exec($q);
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    private function setUpInstanceUserAnilDash() {
        global $THINKUP_CFG;
        $r = array('id'=>1, 'network_username'=>'anildash', 'network_user_id'=>'36823', 'network_viewer_id'=>'36823',
        'last_status_id'=>'0', 'last_page_fetched_replies'=>0, 'last_page_fetched_tweets'=>'17', 
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0', 
        'total_users_in_system'=>'0', 'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 
        'crawler_last_run'=>'', 'earliest_reply_in_system'=>'', 'api_calls_to_leave_unmade_per_minute'=>2, 
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'twitter');
        $this->instance = new Instance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 'fake_key',
        'fake_secret', $this->instance, 1234, 5);

        $this->api->available = true;
        $this->api->available_api_calls_for_crawler = 20;
        $this->instance->is_archive_loaded_follows = true;
    }

    private function setUpInstanceUserGinaTrapani() {
        global $THINKUP_CFG;
        $r = array('id'=>1, 'network_username'=>'ginatrapani', 'network_user_id'=>'930061',
        'network_viewer_id'=>'930061', 'last_status_id'=>'0', 'last_page_fetched_replies'=>0, 
        'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 
        'total_follows_in_system'=>'0', 'total_users_in_system'=>'0', 'is_archive_loaded_replies'=>'0', 
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'', 
        'api_calls_to_leave_unmade_per_minute'=>2, 'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 
        'network'=>'twitter');
        $this->instance = new Instance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 'fake_key',
        'fake_secret', $this->instance, 1234, 5);
        $this->api->available = true;
        $this->api->available_api_calls_for_crawler = 20;
        $this->instance->is_archive_loaded_follows = true;
    }

    public function testConstructor() {
        self::setUpInstanceUserAnilDash();
        $tc = new TwitterCrawler($this->instance, $this->api);

        $this->assertTrue($tc != null);
    }

    public function testFetchInstanceUserInfo() {
        self::setUpInstanceUserAnilDash();

        $tc = new TwitterCrawler($this->instance, $this->api);

        $tc->fetchInstanceUserInfo();

        $udao = DAOFactory::getDAO('UserDAO');
        $user = $udao->getDetails(36823, 'twitter');
        $this->assertTrue($user->id == 1);
        $this->assertTrue($user->user_id == 36823);
        $this->assertTrue($user->username == 'anildash');
        $this->assertTrue($user->found_in == 'Owner Status');
    }

    public function testFetchInstanceUserTweets() {
        self::setUpInstanceUserAnilDash();

        $tc = new TwitterCrawler($this->instance, $this->api);
        $tc->fetchInstanceUserInfo();
        $tc->fetchInstanceUserTweets();

        //Test post with location has location set
        $pdao = DAOFactory::getDAO('PostDAO');
        $this->assertTrue($pdao->isPostInDB(15680112737, 'twitter'));

        $post = $pdao->getPost(15680112737, 'twitter');
        $this->assertEqual($post->location, "NYC: 40.739069,-73.987082");
        $this->assertEqual($post->place, "Stuyvesant Town, New York");
        $this->assertEqual($post->geo, "40.73410845 -73.97885982");

        //Test post without location doesn't have it set
        $post = $pdao->getPost(15660552927, 'twitter');
        $this->assertEqual($post->location, "NYC: 40.739069,-73.987082");
        $this->assertEqual($post->place, "");
        $this->assertEqual($post->geo, "");
    }

    public function testFetchSearchResults() {
        self::setUpInstanceUserAnilDash();
        $tc = new TwitterCrawler($this->instance, $this->api);

        $tc->fetchInstanceUserInfo();
        $tc->fetchSearchResults('@whitehouse');
        $pdao = DAOFactory::getDAO('PostDAO');
        $this->assertTrue($pdao->isPostInDB(11837263794, 'twitter'));

        $post = $pdao->getPost(11837263794, 'twitter');
        $this->assertEqual($post->post_text,
        "RT @whitehouse: The New Start Treaty: Read the text and remarks by President Obama &amp; ".
        'President Medvedev http://bit.ly/cAm9hF');
    }

    public function testFetchInstanceUserFollowers() {
        self::setUpInstanceUserAnilDash();
        $this->instance->is_archive_loaded_follows = false;
        $tc = new TwitterCrawler($this->instance, $this->api);

        $tc->fetchInstanceUserFollowers();
        $fdao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($fdao->followExists(36823, 119950880, 'twitter'), 'new follow exists');

        $udao = DAOFactory::getDAO('UserDAO');
        $updated_user = $udao->getUserByName('meatballhat', 'twitter');
        $this->assertEqual($updated_user->full_name, 'Dan Buch', 'follower full name set to '.$updated_user->full_name);
        $this->assertEqual($updated_user->location, 'Bedford, OH', 'follower location set to '.$updated_user->location);
    }

    public function testFetchInstanceUserFriends() {
        self::setUpInstanceUserAnilDash();
        $tc = new TwitterCrawler($this->instance, $this->api);
        $tc->fetchInstanceUserInfo();

        $tc->fetchInstanceUserFriends();
        $fdao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($fdao->followExists(14834340, 36823, 'twitter'), 'new friend exists');

        $udao = DAOFactory::getDAO('UserDAO');
        $updated_user = $udao->getUserByName('jayrosen_nyu', 'twitter');
        $this->assertEqual($updated_user->full_name, 'Jay Rosen', 'friend full name set');
        $this->assertEqual($updated_user->location, 'New York City', 'friend location set');
    }

    public function testFetchInstanceUserFriendsByIds() {
        self::setUpInstanceUserAnilDash();
        $tc = new TwitterCrawler($this->instance, $this->api);
        $tc->fetchInstanceUserInfo();

        $fd = DAOFactory::getDAO('FollowDAO');
        $stale_friend = $fd->getStalestFriend($this->instance->network_user_id, $this->instance->network);
        $this->assertTrue(isset($stale_friend), 'there is a stale friend');
        $this->assertEqual($stale_friend->user_id, 930061, 'stale friend is ginatrapani');
        $this->assertEqual($stale_friend->username, 'ginatrapani', 'stale friend is ginatrapani');

        $tc->fetchFriendTweetsAndFriends();
        $fdao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($fdao->followExists(14834340, 930061, 'twitter'), 'ginatrapani friend loaded');
    }

    public function testFetchInstanceUserFollowersByIds() {
        self::setUpInstanceUserAnilDash();
        $this->api->available_api_calls_for_crawler = 2;
        $tc = new TwitterCrawler($this->instance, $this->api);
        $tc->fetchInstanceUserInfo();

        $tc->fetchInstanceUserFollowers();
        $fdao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($fdao->followExists(36823, 114811186, 'twitter'), 'new follow exists');
    }

    public function testFetchRetweetsOfInstanceuser() {
        self::setUpInstanceUserGinaTrapani();
        $tc = new TwitterCrawler($this->instance, $this->api);
        $tc->fetchInstanceUserInfo();

        //first, load retweeted tweet into db
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES (14947487415, 930061, 
        'ginatrapani', 'Gina Trapani', 'avatar.jpg', 
        '&quot;Wearing your new conference tee shirt does NOT count as dressing up.&quot;', 'web', 
        '2006-01-01 00:00:00', ".rand(0, 4).", 0);";
        $this->db->exec($q);

        $pdao = DAOFactory::getDAO('PostDAO');
        $tc->fetchRetweetsOfInstanceUser();
        $post = $pdao->getPost(14947487415, 'twitter');
        $this->assertEqual($post->retweet_count_cache, 3, '3 retweets loaded');
        $retweets = $pdao->getRetweetsOfPost(14947487415, 'twitter', true);
        $this->assertEqual(sizeof($retweets), 3, '3 retweets loaded');

        //make sure duplicate posts aren't going into the db on next crawler run
        self::setUpInstanceUserGinaTrapani();
        $tc = new TwitterCrawler($this->instance, $this->api);
        $tc->fetchInstanceUserInfo();

        $tc->fetchRetweetsOfInstanceUser();
        $post = $pdao->getPost(14947487415, 'twitter');
        $this->assertEqual($post->retweet_count_cache, 3, '3 retweets loaded');
        $retweets = $pdao->getRetweetsOfPost(14947487415, 'twitter', true);
        $this->assertEqual(sizeof($retweets), 3, '3 retweets loaded');
    }

    public function testFetchStrayRepliedToTweets() {
        self::setUpInstanceUserAnilDash();
        $this->api->available_api_calls_for_crawler = 4;
        $tc = new TwitterCrawler($this->instance, $this->api);
        $tc->fetchInstanceUserInfo();
        $tc->fetchInstanceUserTweets();
        $pdao = DAOFactory::getDAO('PostDAO');
        $tweets = $pdao->getAllPostsByUsername('anildash', 'twitter');

        $tc->fetchStrayRepliedToTweets();
        $post = $pdao->getPost(15752814831, 'twitter');
        $this->assertTrue(isset($post));
        $this->assertEqual($post->reply_count_cache, 1);
    }

}