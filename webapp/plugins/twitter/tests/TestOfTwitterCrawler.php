<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterCrawler.php
 *
 * Copyright (c) 2009-2012 Gina Trapani
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
 *
 * Test of TwitterCrawler
 *
 * @TODO Test the rest of the TwitterCrawler methods
 * @TODO Add testFetchTweetsWithLinks, assert Links and images get inserted
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';

require_once THINKUP_WEBAPP_PATH.'plugins/twitter/tests/classes/mock.TwitterOAuth.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.CrawlerTwitterAPIAccessorOAuth.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterCrawler.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.RetweetDetector.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterInstance.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterInstanceMySQLDAO.php';

class TestOfTwitterCrawler extends ThinkUpUnitTestCase {
    /**
     * @var CrawlerTwitterAPIAccessorOAuth API accessor object
     */
    var $api;
    /**
     *
     * @var Instance
     */
    var $instance;
    /**
     *
     * @var Logger
     */
    var $logger;

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();

        //insert test users
        $this->builders[] = FixtureBuilder::build('users', array('user_id'=>'36823', 'user_name'=>'anildash',
        'full_name'=>'Anil Dash', 'last_updated'=>'2007-01-01 20:34:13', 'network'=>'twitter', 'is_protected'=>0,
        'last_post_id'=>''));

        $this->builders[] = FixtureBuilder::build('users', array('user_id'=>'930061', 'user_name'=>'ginatrapani',
        'full_name'=>'Gina Trapani', 'last_updated'=>'2007-01-01 20:34:13', 'network'=>'twitter', 'is_protected'=>0,
        'last_post_id'=>''));

        // insert test follow
        $this->builders[] = FixtureBuilder::build('follows', array('user_id'=>'930061', 'follower_id'=>'36823',
        'last_seen'=>'-2y'));

        $this->builders[] = FixtureBuilder::build('groups', array('group_id'=>'19994710',
        'group_name'=>'@userx/anotherlist', 'network' => 'twitter', 'is_active'=>1));

        // stale group membership
        $this->builders[] = FixtureBuilder::build('group_members', array('member_user_id'=>'36823',
        'group_id'=>'19994710', 'is_active' => 1, 'network'=>'twitter', 'last_seen' => '-3d'));
    }

    public function tearDown() {
        $this->builders = null;
        $this->logger->close();
        parent::tearDown();
    }

    private function setUpInstanceUserAnilDash() {
        $r = array('id'=>1, 'network_username'=>'anildash', 'network_user_id'=>'36823', 'network_viewer_id'=>'36823',
        'last_post_id'=>'0', 'last_page_fetched_replies'=>0, 'last_page_fetched_tweets'=>'17',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0',
        'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'total_posts_by_owner'=>1,
        'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',  'avg_replies_per_day'=>'2', 'is_public'=>'0',
        'is_active'=>'0', 'network'=>'twitter', 'last_favorite_id' => '0', 'last_unfav_page_checked' => '0',
        'last_page_fetched_favorites' => '0', 'favorites_profile' => '0', 'owner_favs_in_system' => '0',
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'01-01-2009'
        );
        $this->instance = new TwitterInstance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 'fake_key', 'fake_secret', 2,
        1234, 5, 350);

        $this->api->available = true;
        $this->api->available_api_calls_for_crawler = 20;
        $this->instance->is_archive_loaded_follows = true;
    }

    private function setUpInstanceUserAnilDashDelete() {
        $r = array('id'=>1, 'network_username'=>'anildash', 'network_user_id'=>'36825', 'network_viewer_id'=>'36823',
        'last_post_id'=>'0', 'last_page_fetched_replies'=>0, 'last_page_fetched_tweets'=>'17',
        'total_posts_in_system'=>'21', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0',
        'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'total_posts_by_owner'=>1,
        'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',  'avg_replies_per_day'=>'2', 'is_public'=>'0',
        'is_active'=>'0', 'network'=>'twitter', 'last_favorite_id' => '0', 'last_unfav_page_checked' => '0',
        'last_page_fetched_favorites' => '0', 'favorites_profile' => '0', 'owner_favs_in_system' => '0',
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'01-01-2009'
        );
        $this->instance = new TwitterInstance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 'fake_key', 'fake_secret', 2,
        1234, 5, 350);

        $this->api->available = true;
        $this->api->available_api_calls_for_crawler = 20;
        $this->instance->is_archive_loaded_follows = true;
    }

    private function setUpInstanceUserAnilDashUsernameChange() {
        $r = array('id'=>1, 'network_username'=>'anildash', 'network_user_id'=>'36824', 'network_viewer_id'=>'36824',
        'last_post_id'=>'0', 'last_page_fetched_replies'=>0, 'last_page_fetched_tweets'=>'17',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0',
        'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'total_posts_by_owner'=>1,
        'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',  'avg_replies_per_day'=>'2', 'is_public'=>'0',
        'is_active'=>'0', 'network'=>'twitter', 'last_favorite_id' => '0', 'last_unfav_page_checked' => '0',
        'last_page_fetched_favorites' => '0', 'favorites_profile' => '0', 'owner_favs_in_system' => '0',
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'01-01-2009'
        );
        $this->instance = new TwitterInstance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 'fake_key', 'fake_secret', 2,
        1234, 5, 350);

        $this->api->available = true;
        $this->api->available_api_calls_for_crawler = 20;
        $this->instance->is_archive_loaded_follows = true;

        // add post to backfill
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'1', 'author_user_id'=>'36824',
        'author_username'=>'anildash', 'author_fullname'=>'Anil Dash', 'author_avatar'=>'avatar.jpg',
        'post_text'=>'This is a great post', 'network'=>'twitter','in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'36824', 'network'=>'twitter'));
        return $builders;
    }

    private function setUpInstanceUserPrivateMcprivate() {
        $this->builders[] = FixtureBuilder::build('users', array('user_id'=>'123456', 'user_name'=>'mcprivate',
        'full_name'=>'Private McPrivate', 'last_updated'=>'2007-01-01 20:34:13', 'network'=>'twitter',
        'is_protected'=>1, 'last_post_id'=>''));

        $r = array('id'=>1, 'network_username'=>'mcprivate', 'network_user_id'=>'123456', 'network_viewer_id'=>'123456',
        'last_post_id'=>'0', 'last_page_fetched_replies'=>0, 'last_page_fetched_tweets'=>'17',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0',
        'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'total_posts_by_owner'=>1,
        'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',  'avg_replies_per_day'=>'2', 'is_public'=>'0',
        'is_active'=>'0', 'network'=>'twitter', 'last_favorite_id' => '0', 'last_unfav_page_checked' => '0',
        'last_page_fetched_favorites' => '0', 'favorites_profile' => '0', 'owner_favs_in_system' => '0',
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'01-01-2009'
        );
        $this->instance = new TwitterInstance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 'fake_key', 'fake_secret', 2,
        1234, 5, 350);

        $this->api->available = true;
        $this->api->available_api_calls_for_crawler = 20;
        $this->instance->is_archive_loaded_follows = true;
    }


    private function setUpInstanceUserGinaTrapani() {
        $r = array('id'=>1, 'network_username'=>'ginatrapani', 'network_user_id'=>'930061',
        'network_viewer_id'=>'930061', 'last_post_id'=>'0', 'last_page_fetched_replies'=>0,
        'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'twitter',
        'last_favorite_id' => '0', 'last_unfav_page_checked' => '0', 'last_page_fetched_favorites' => '0',
        'favorites_profile' => '0',  'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'01-01-2009'
        );
        $this->instance = new TwitterInstance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 'fake_key', 'fake_secret', 2, 1234, 5, 350);
        $this->api->available = true;
        $this->api->available_api_calls_for_crawler = 20;
        $this->instance->is_archive_loaded_follows = true;
    }

    private function setUpInstanceUserAmygdala() {
        $instd = DAOFactory::getDAO('TwitterInstanceDAO');
        $iid = $instd->insert('2768241', 'amygdala', 'twitter');
        $this->instance = $instd->getByUsernameOnNetwork("amygdala", "twitter");

        $this->api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 'fake_key', 'fake_secret',2, 1234,
        5, 350);
        $this->api->available = true;
        $this->api->available_api_calls_for_crawler = 20;
    }

    public function testConstructor() {
        self::setUpInstanceUserAnilDash();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);

        $this->assertTrue($twitter_crawler != null);
    }

    public function testFetchInstanceUserInfo() {
        self::setUpInstanceUserAnilDash();

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);

        $twitter_crawler->fetchInstanceUserInfo();

        $user_dao = DAOFactory::getDAO('UserDAO');
        $user = $user_dao->getDetails('36823', 'twitter');
        $this->assertTrue($user->id == 1);
        $this->assertTrue($user->user_id == '36823');
        $this->assertTrue($user->username == 'anildash');
        $this->assertTrue($user->found_in == 'Owner Status');
    }

    public function testFetchInstanceUserTweets() {
        self::setUpInstanceUserAnilDash();

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $twitter_crawler->fetchInstanceUserTweets();

        //Test post with location has location set
        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->assertTrue($post_dao->isPostInDB('15680112737', 'twitter'));

        $post = $post_dao->getPost('15680112737', 'twitter');
        $this->assertEqual($post->location, "NYC: 40.739069,-73.987082");
        $this->assertEqual($post->place, "Stuyvesant Town, New York");
        $this->assertEqual($post->geo, "40.73410845 -73.97885982");

        //Test post without location doesn't have it set
        $post = $post_dao->getPost('15660552927', 'twitter');
        $this->assertEqual($post->location, "NYC: 40.739069,-73.987082");
        $this->assertEqual($post->place, "");
        $this->assertEqual($post->geo, "");
    }

    public function testFetchInstanceUserTweetsEscapeHTML() {
        self::setUpInstanceUserAnilDash();

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $twitter_crawler->fetchInstanceUserTweets();

        //Test post with location has location set
        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->assertTrue($post_dao->isPostInDB('15660373190', 'twitter'));
        $post = $post_dao->getPost('15660373190', 'twitter');
        $this->assertEqual($post->post_text, "@nicknotned NYC isn't a rival, it's just a better evolution of the " .
        "concept of a locale where innovation happens. > & <");

    }

    public function testFetchInstanceUserTweetsBudgeted() {
        self::setUpInstanceUserAnilDash();

        // set up crawl limit budget
        $crawl_limit = array('fetchInstanceUserTweets' => array('count' => 2, 'remaining' => 2) );
        $this->api->setCallerLimits($crawl_limit);
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $twitter_crawler->fetchInstanceUserTweets();

        //Test post with location has location set
        $pdao = DAOFactory::getDAO('PostDAO');
        $this->assertTrue($pdao->isPostInDB(15660373190, 'twitter'));
        $post = $pdao->getPost(15660373190, 'twitter');
        $this->assertEqual($post->post_text, "@nicknotned NYC isn't a rival, it's just a better evolution of the " .
        "concept of a locale where innovation happens. > & <");

        $crawl_limit = $this->api->getCallerLimit('fetchInstanceUserTweets');
        $this->assertIsA($crawl_limit,'Array');
        $this->assertEqual($crawl_limit['remaining'], 0);
    }

    public function testFetchInstanceUserTweetsUsernameChange() {
        $post_builder = self::setUpInstanceUserAnilDashUsernameChange();

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>17,
        'network_username'=>'anildash2', 'network'=>'twitter', 'network_viewer_id'=>15,
        'crawler_last_run'=>'2010-01-01 12:00:01', 'is_active'=>1));

        $post_dao = DAOFactory::getDAO('PostDAO');

        // old post before crawl
        $post = $post_dao->getPost(1, 'twitter');
        $this->assertEqual($post->author_username, "anildash");

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $twitter_crawler->fetchInstanceUserTweets();

        // old post after crawl
        $post = $post_dao->getPost(1, 'twitter');
        $this->assertEqual($post->author_username, "anildash2");

        // new post have the new username as well...
        $post = $post_dao->getPost('15660310954', 'twitter');
        $this->assertEqual($post->author_username, "anildash2");

        // instace has the new username as well...
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
        $instance = $instance_dao->getByUsername("anildash");
        $this->assertNull($instance);

        $instance = $instance_dao->getByUsername("anildash2");
        $this->assertNotNull($instance);

    }

    public function testDeletedTweet() {
        $post_builder = self::setUpInstanceUserAnilDashDelete();

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>'36825',
        'network_username'=>'anildash', 'network'=>'twitter', 'network_viewer_id'=>'36825',
        'crawler_last_run'=>'2010-01-01 12:00:01', 'is_active'=>1));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'36825', 'network'=>'twitter'));

        // some tweets...
        $builder = FixtureBuilder::build('posts', array('id' => 1, 'post_id' => '12345',
        'author_user_id' => '36825', 'pub_date' => '2010-06-08 04:45:16'));
        $builder2 = FixtureBuilder::build('posts', array('id' => 2, 'post_id' => '123456',
        'author_user_id' => '36825', 'pub_date' => '2010-06-08 04:45:16'));
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserTweets();

        // should be deleted, not  found on twitter...
        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->assertNull($post_dao->getPost('12345', 'twitter'));

        // found on twitter, so don't delete
        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->assertNotNull($post_dao->getPost('123456', 'twitter'));
    }

    public function testFetchPrivateInstanceUserTweets() {
        self::setUpInstanceUserPrivateMcPrivate();

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserTweets();

        //Test post is set as protected
        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->assertTrue($post_dao->isPostInDB('14846078418', 'twitter'));

        $post = $post_dao->getPost('14846078418', 'twitter');
        $this->debug(Utils::varDumpToString($post));
        $this->assertTrue($post->is_protected);
    }

    public function testFetchInstanceUserTweetsRetweets() {
        self::setUpInstanceUserAmygdala();
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'2768241', 'network'=>'twitter'));

        $this->instance->last_page_fetched_tweets = 17;

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $twitter_crawler->fetchInstanceUserTweets();

        $post_dao = DAOFactory::getDAO('PostDAO');
        $post = $post_dao->getPost('13708601491193856', 'twitter');
        // the xml <retweeted_status> for the original post (that 'amygdala' retweeted) includes a native
        // <retweet_count> of 8. In our database we only have 1 of those RTs stored/processed.
        $this->assertEqual($post->retweet_count_api, 8);
        $this->assertEqual($post->retweet_count_cache, 1);
        $this->assertEqual($post->old_retweet_count_cache, 0);
        $retweets = $post_dao->getRetweetsOfPost('13708601491193856', 'twitter', true);
        $this->assertEqual(sizeof($retweets), 1);
        $this->assertEqual($post->links[0]->url, "http://is.gd/izUl5");
        $this->assertNotEqual($post->links[0]->expanded_url, "http://is.gd/izUl5");

        $post = $post_dao->getPost('13960125416996864', 'twitter');
        $this->assertEqual($post->in_retweet_of_post_id, '13708601491193856');
        $this->assertEqual($post->in_rt_of_user_id, '20542737');
        $this->assertEqual($post->links[0]->url, "http://is.gd/izUl5");

        $twitter_crawler->fetchInstanceUserMentions();
        // old-style RT
        $post = $post_dao->getPost('8957053141778432', 'twitter');
        $this->assertEqual($post->in_rt_of_user_id, '2768241');
        $this->assertEqual($post->in_retweet_of_post_id, '8927196122972160');
        $post_orig = $post_dao->getPost('8927196122972160', 'twitter');
        $this->assertEqual($post_orig->old_retweet_count_cache, 1);
        $this->assertEqual($post_orig->retweet_count_cache, 0);
        $this->assertEqual($post_orig->retweet_count_api, 0);

        $mention_dao = DAOFactory::getDAO('MentionDAO');
        $mentions = $mention_dao->getMentionInfoUserName('amygdala', 'twitter');
        $this->assertEqual($mentions['count_cache'], 1);
    }

    public function testFetchInstanceUserTweetsRetweetsBudget() {
        self::setUpInstanceUserAmygdala();
        // set up crawl limit budget
        $crawl_limit = array('fetchInstanceUserMentions' => array('count' => 2, 'remaining' => 0) );
        $this->api->setCallerLimits($crawl_limit);

        $this->instance->last_page_fetched_tweets = 17;

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $twitter_crawler->fetchInstanceUserTweets();

        $pdao = DAOFactory::getDAO('PostDAO');

        $twitter_crawler->fetchInstanceUserMentions();
        // old-style RT
        $post = $pdao->getPost('8957053141778432', 'twitter');
        $this->assertNull($post);
    }

    public function testFetchSearchResults() {
        self::setUpInstanceUserAnilDash();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);

        $twitter_crawler->fetchInstanceUserInfo();
        $twitter_crawler->fetchSearchResults('@whitehouse');
        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->assertTrue($post_dao->isPostInDB('11837263794', 'twitter'));

        $post = $post_dao->getPost('11837263794', 'twitter');
        $this->assertEqual($post->post_text,
        "RT @whitehouse: The New Start Treaty: Read the text and remarks by President Obama &amp; ".
        'President Medvedev http://bit.ly/cAm9hF');
    }

    public function testFetchSearchResultsBudget() {
        self::setUpInstanceUserAnilDash();
        // set up crawl limit budget
        $crawl_limit = array('fetchSearchResults' => array('count' => 2, 'remaining' => 0) );
        $this->api->setCallerLimits($crawl_limit);
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);

        $twitter_crawler->fetchInstanceUserInfo();
        $twitter_crawler->fetchSearchResults('@whitehouse');
        $pdao = DAOFactory::getDAO('PostDAO');
        $this->assertFalse($pdao->isPostInDB('11837263794', 'twitter'));

    }

    public function testFetchInstanceUserFollowers() {
        self::setUpInstanceUserAnilDash();
        $this->instance->is_archive_loaded_follows = false;
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);

        $twitter_crawler->fetchInstanceUserFollowers();
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($follow_dao->followExists('36823', '119950880', 'twitter'), 'new follow exists');

        $user_dao = DAOFactory::getDAO('UserDAO');
        $updated_user = $user_dao->getUserByName('meatballhat', 'twitter');
        $this->assertEqual($updated_user->full_name, 'Dan Buch', 'follower full name set to '.
        $updated_user->full_name);
        $this->assertEqual($updated_user->location, 'Bedford, OH', 'follower location set to '.
        $updated_user->location);
    }

    public function testFetchInstanceUserGroups() {
        self::setUpInstanceUserAnilDash();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);

        $twitter_crawler->fetchInstanceUserGroups();
        $group_dao = DAOFactory::getDAO('GroupDAO');
        $this->assertTrue($group_dao->isGroupInStorage($group = '1234566', 'twitter'), 'group does not exist');

        $group_member_dao = DAOFactory::getDAO('GroupMemberDAO');
        $this->assertTrue($group_member_dao->isGroupMemberInStorage($user = '36823', $group = '1234566', 'twitter'),
        'group member does not exist');
        $this->assertFalse($group_member_dao->isGroupMemberInStorage($user = '930061', $group = '1234566', 'twitter'),
        'group member exists');

        $group_membership_count_dao = DAOFactory::getDAO('GroupMembershipCountDAO');
        $history = $group_membership_count_dao->getHistory($user = '36823', 'twitter', 'DAYS');
        $this->assertEqual(count($history['history']), 1);
    }

    public function testFetchInstanceUserGroupsBudget() {
        self::setUpInstanceUserAnilDash();
        // set up crawl limit budget
        $crawl_limit = array('fetchInstanceUserGroups' => array('count' => 2, 'remaining' => 0) );
        $this->api->setCallerLimits($crawl_limit);
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);

        $twitter_crawler->fetchInstanceUserGroups();
        $group_dao = DAOFactory::getDAO('GroupDAO');
        $this->assertFalse($group_dao->isGroupInStorage($group = '1234566', 'twitter'), 'group does not exist');

        $group_member_dao = DAOFactory::getDAO('GroupMemberDAO');
        $this->assertFalse($group_member_dao->isGroupMemberInStorage($user = '36823', $group = '1234566', 'twitter'),
        'group member does not exists');

    }

    public function testUpdateStaleGroupMemberships() {
        self::setUpInstanceUserAnilDash();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);

        $group_member_dao = DAOFactory::getDAO('GroupMemberDAO');
        $this->assertTrue($group_member_dao->isGroupMemberInStorage($user = '36823', $group = '19994710', 'twitter',
        $active = true), 'group member does not exist');

        $twitter_crawler->updateStaleGroupMemberships();
        $this->assertFalse($group_member_dao->isGroupMemberInStorage($user = '36823', $group = '19994710', 'twitter',
        $active = true), 'group member active');
    }

    public function testFetchInstanceUserFriends() {
        self::setUpInstanceUserAnilDash();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();

        $twitter_crawler->fetchInstanceUserFriends();
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($follow_dao->followExists('14834340', '36823', 'twitter'), 'new friend exists');

        $user_dao = DAOFactory::getDAO('UserDAO');
        $updated_user = $user_dao->getUserByName('jayrosen_nyu', 'twitter');
        $this->assertEqual($updated_user->full_name, 'Jay Rosen', 'friend full name set');
        $this->assertEqual($updated_user->location, 'New York City', 'friend location set');
    }

    public function testFetchInstanceUserFriendsBudget() {
        self::setUpInstanceUserAnilDash();
        // set up crawl limit budget
        $crawl_limit = array('fetchInstanceUserFriends' => array('count' => 2, 'remaining' => 0) );
        $this->api->setCallerLimits($crawl_limit);
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();

        $twitter_crawler->fetchInstanceUserFriends();
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->assertFalse($follow_dao->followExists(14834340, 36823, 'twitter'), 'new friend doesn\'t exists');

    }

    public function testFetchInstanceUserFriendsByIds() {
        self::setUpInstanceUserAnilDash();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();

        $fd = DAOFactory::getDAO('FollowDAO');
        $stale_friend = $fd->getStalestFriend($this->instance->network_user_id, $this->instance->network);
        $this->assertTrue(isset($stale_friend), 'there is a stale friend');
        $this->assertEqual($stale_friend->user_id, '930061', 'stale friend is ginatrapani');
        $this->assertEqual($stale_friend->username, 'ginatrapani', 'stale friend is ginatrapani');

        $twitter_crawler->fetchFriendTweetsAndFriends();
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($follow_dao->followExists('14834340', '930061', 'twitter'), 'ginatrapani friend loaded');
    }

    public function testFetchInstanceUserFriendsByIdsBudget() {
        self::setUpInstanceUserAnilDash();
        // set up crawl limit budget
        $crawl_limit = array('fetchFriendTweetsAndFriends' => array('count' => 2, 'remaining' => 0) );
        $this->api->setCallerLimits($crawl_limit);

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();

        $fd = DAOFactory::getDAO('FollowDAO');
        $stale_friend = $fd->getStalestFriend($this->instance->network_user_id, $this->instance->network);
        $this->assertTrue(isset($stale_friend), 'there is a stale friend');
        $this->assertEqual($stale_friend->user_id, 930061, 'stale friend is ginatrapani');
        $this->assertEqual($stale_friend->username, 'ginatrapani', 'stale friend is ginatrapani');

        $twitter_crawler->fetchFriendTweetsAndFriends();
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->assertFalse($follow_dao->followExists(14834340, 930061, 'twitter'), 'ginatrapani notfriend loaded');
    }

    public function testFetchInstanceUserFollowersByIds() {
        self::setUpInstanceUserAnilDash();
        $this->api->available_api_calls_for_crawler = 2;
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();

        $twitter_crawler->fetchInstanceUserFollowers();
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($follow_dao->followExists('36823', '114811186', 'twitter'), 'new follow exists');
    }

    public function testFetchInstanceUserFollowersByIdsBudget() {
        self::setUpInstanceUserAnilDash();
        // set up crawl limit budget
        $crawl_limit = array('fetchInstanceUserFollowersByIDs' => array('count' => 2, 'remaining' => 0) );
        $this->api->setCallerLimits($crawl_limit);
        $this->api->available_api_calls_for_crawler = 2;
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();

        $twitter_crawler->fetchInstanceUserFollowers();
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->assertFalse($follow_dao->followExists(36823, 114811186, 'twitter'), 'new does not exists');
    }

    public function testFetchRetweetsOfInstanceuser() {
        self::setUpInstanceUserGinaTrapani();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();

        //first, load retweeted tweet into db
        // we now get the 'new-style' retweet count from the retweet_count field in the xml,
        // which is parsed into 'retweet_count_cache' in the post vals.  This will not necessarily match
        // the number of retweets in the database any more (but does in this test case).
        $builder = FixtureBuilder::build('posts', array('post_id'=>'14947487415', 'author_user_id'=>'930061',
        'author_username'=>'ginatrapani', 'author_fullname'=>'Gina Trapani', 'post_text'=>
        '&quot;Wearing your new conference tee shirt does NOT count as dressing up.&quot;', 'pub_date'=>'-1d',
        // start w/ the RT counts zeroed out, let the processing populate them
        'reply_count_cache'=>1, 'old_retweet_count_cache'=>0, 'retweet_count_cache'=>0, 'retweet_count_api' => 0));

        $post_dao = DAOFactory::getDAO('PostDAO');
        $twitter_crawler->fetchRetweetsOfInstanceUser();
        $post = $post_dao->getPost('14947487415', 'twitter');
        $this->assertEqual($post->retweet_count_cache, 3, '3 new-style retweets from cache count');
        // in processing the retweets of the post, if they contain a <retweeted_status> element pointing
        // to the original post, and that original post information includes a retweet count, we will update the
        // original post in the db with that count.  In this test data that count is 2, 'behind' the database info.
        $this->assertEqual($post->retweet_count_api, 2, '2 new-style retweets count from API');
        // should not have processed any old-style retweets here
        $this->assertEqual($post->old_retweet_count_cache, 0, '0 old-style retweets count from API');
        $retweets = $post_dao->getRetweetsOfPost('14947487415', 'twitter', true);
        $this->assertEqual(sizeof($retweets), 3, '3 retweets loaded');

        //make sure duplicate posts aren't going into the db on next crawler run
        self::setUpInstanceUserGinaTrapani();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();

        $twitter_crawler->fetchRetweetsOfInstanceUser();
        $post = $post_dao->getPost('14947487415', 'twitter');
        $this->assertEqual($post->retweet_count_cache, 3, '3 new-style retweets detected');
        $this->assertEqual($post->retweet_count_api, 2, '2 new-style retweets count from API');
        $retweets = $post_dao->getRetweetsOfPost('14947487415', 'twitter', true);
        $this->assertEqual(sizeof($retweets), 3, '3 retweets loaded');

        $post = $post_dao->getPost('12722783896', 'twitter');
        $rts2 = $post_dao->getRetweetsOfPost('12722783896', 'twitter', true);
        $this->assertEqual(sizeof($rts2), 1, '1 retweet loaded');
        $this->assertEqual($rts2[0]->in_rt_of_user_id, '930061');
    }

    public function testFetchRetweetsOfInstanceuserBudget() {
        self::setUpInstanceUserGinaTrapani();
        // set up crawl limit budget
        $crawl_limit = array('fetchUserTimelineForRetweet' => array('count' => 2, 'remaining' => 0) );
        $this->api->setCallerLimits($crawl_limit);

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();

        $builder = FixtureBuilder::build('posts', array('post_id'=>'14947487415', 'author_user_id'=>'930061',
        'author_username'=>'ginatrapani', 'author_fullname'=>'Gina Trapani', 'post_text'=>
        '&quot;Wearing your new conference tee shirt does NOT count as dressing up.&quot;', 'pub_date'=>'-1d',
        // start w/ the RT counts zeroed out, let the processing populate them
        'reply_count_cache'=>1, 'old_retweet_count_cache'=>0, 'retweet_count_cache'=>0, 'retweet_count_api' => 0));

        $post_dao = DAOFactory::getDAO('PostDAO');
        $twitter_crawler->fetchRetweetsOfInstanceUser();
        $post = $post_dao->getPost('14947487415', 'twitter');
        $this->assertEqual($post->retweet_count_cache, 0, '0 new-style retweets from cache count');
    }

    public function testFetchStrayRepliedToTweets() {
        self::setUpInstanceUserAnilDash();
        $this->api->available_api_calls_for_crawler = 4;
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $twitter_crawler->fetchInstanceUserTweets();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $tweets = $post_dao->getAllPostsByUsername('anildash', 'twitter');

        $twitter_crawler->fetchStrayRepliedToTweets();
        $post = $post_dao->getPost('15752814831', 'twitter');
        $this->assertTrue(isset($post));
        $this->assertEqual($post->reply_count_cache, 1);
    }

    public function testFetchStrayRepliedToTweetsBudget() {
        self::setUpInstanceUserAnilDash();
        // set up crawl limit budget
        $crawl_limit = array('fetchAndAddTweetRepliedTo' => array('count' => 2, 'remaining' => 0) );

        $this->api->setCallerLimits($crawl_limit);
        $this->api->available_api_calls_for_crawler = 4;
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $twitter_crawler->fetchInstanceUserTweets();
        $pdao = DAOFactory::getDAO('PostDAO');
        $tweets = $pdao->getAllPostsByUsername('anildash', 'twitter');

        $twitter_crawler->fetchStrayRepliedToTweets();
        $post = $pdao->getPost(15752814831, 'twitter');
        $this->assertTrue(isset($post));
        $this->assertEqual($post->reply_count_cache, 0);
    }

    public function testFetchFavoritesOfInstanceuser() {
        self::setUpInstanceUserAmygdala();
        $this->api->available_api_calls_for_crawler = 3;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage1/');
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $twitter_crawler->fetchInstanceFavorites();
        // Save instance
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }
        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 22);
        $this->assertEqual($this->instance->last_page_fetched_favorites, 4);
        $this->assertEqual($this->instance->favorites_profile, 82);

        $this->logger->logInfo("second round of archiving", __METHOD__.','.__LINE__);
        $this->api->available_api_calls_for_crawler = 10;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage2/');
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $twitter_crawler->fetchInstanceFavorites();
        // Save instance
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }
        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 84);
        $this->assertEqual($this->instance->last_page_fetched_favorites, 1);

        $this->logger->logInfo("now in maintenance mode", __METHOD__.','.__LINE__);
        $this->api->available_api_calls_for_crawler = 4;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage3/');
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $retval = $twitter_crawler->fetchInstanceFavorites();
        // Save instance
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }
        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 87);
        $this->assertEqual($this->instance->last_page_fetched_favorites, 1);
        $this->assertEqual($retval, true);

        // now test case where there are 'extra' favs being reported by twitter,
        // not findable via the N pages searched back through, with existing pages < N
        // override a cfg value
        $this->logger->logInfo("now in maintenance mode, second pass", __METHOD__.','.__LINE__);
        $this->api->available_api_calls_for_crawler = 10;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage5/');
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $retval = $twitter_crawler->fetchInstanceFavorites();
        // Save instance
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }
        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 88);
        $this->assertEqual($this->instance->last_page_fetched_favorites, 1);
        $this->assertEqual($retval, true);
        $builder2 = null;

        //Assert links got saved
        $post_dao = new PostMySQLDAO();
        $post = $post_dao->getPost('25138632577', 'twitter');
        $this->assertIsA($post->links[0], "Link");
        $this->assertEqual($post->post_text, "Raw RSS feed of independent neuroblogs ".
        "http://friendfeed.com/neuroghetto Now also listed at scienceblogging.org Yay!");
        $this->assertEqual($post->links[0]->url, "http://friendfeed.com/neuroghetto");
        $this->assertEqual($post->links[0]->expanded_url, '');

        $post = $post_dao->getPost('25598018110', 'twitter');
        $this->assertIsA($post->links[0], "Link");
        $this->assertEqual($post->post_text, "Wal-mart: People line up at midnight to buy baby formula, waiting for ".
        "monthly govt checks to hit accounts http://bit.ly/aK5pCQ");
        $this->assertEqual($post->links[0]->url, "http://bit.ly/aK5pCQ");
        $this->assertEqual($post->links[0]->expanded_url, '');
    }
    public function testFetchFavoritesOfInstanceuserBudget() {
        self::setUpInstanceUserAmygdala();
        // set up crawl limit budget
        $crawl_limit = array('getFavsPage' => array('count' => 2, 'remaining' => 0) );
        $this->api->setCallerLimits($crawl_limit);

        $this->api->available_api_calls_for_crawler = 3;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage1/');
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $twitter_crawler->fetchInstanceFavorites();
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }
        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 0);
    }

    public function testFetchFavoritesOfInstanceuserBadResponse() {
        $this->logger->logInfo("in testFetchFavoritesOfInstanceuserBadResponse", __METHOD__.','.__LINE__);
        self::setUpInstanceUserAmygdala();
        $this->api->available_api_calls_for_crawler = 10;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage4/');
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $retval = $twitter_crawler->fetchInstanceFavorites();
        // Save instance
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }

        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 0);
        $this->assertEqual($this->instance->last_page_fetched_favorites, 1);
    }

    public function testNoFavorites() {
        $this->logger->logInfo("in testNoFavorites", __METHOD__.','.__LINE__);
        self::setUpInstanceUserAmygdala();
        $this->api->available_api_calls_for_crawler = 10;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage7/');
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $retval = $twitter_crawler->fetchInstanceFavorites();
        // Save instance
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }
        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 0);
        $this->assertEqual($this->instance->last_page_fetched_favorites, 1);

        $this->logger->logInfo("now in maintenance mode, second pass", __METHOD__.','.__LINE__);
        $this->api->available_api_calls_for_crawler = 10;
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $retval = $twitter_crawler->fetchInstanceFavorites();
        // Save instance
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }
        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 0);
        $this->assertEqual($this->instance->last_page_fetched_favorites, 1);
    }

    /**
     * necessary due to previously-existing bug- should not normally occur
     */
    public function testNegPageRecovery() {
        $this->logger->logInfo("in testNegPageRecovery", __METHOD__.','.__LINE__);
        self::setUpInstanceUserAmygdala();
        $this->api->available_api_calls_for_crawler = 10;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage8/');
        $this->instance->last_page_fetched_favorites = -20;
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $retval = $twitter_crawler->fetchInstanceFavorites();
        // Save instance
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }

        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 3);
        $this->assertEqual($this->instance->last_page_fetched_favorites, 1);
    }

    /**
     * the user has favs, but they're not indicated in the profile yet.
     */
    public function testNoReportedFavorites() {
        $this->logger->logInfo("in testNoReportedFavorites", __METHOD__.','.__LINE__);
        self::setUpInstanceUserAmygdala();
        $this->api->available_api_calls_for_crawler = 10;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage8/');
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $retval = $twitter_crawler->fetchInstanceFavorites();
        // Save instance
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }

        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 3);
        $this->assertEqual($this->instance->last_page_fetched_favorites, 1);

        $this->logger->logInfo("now in maintenance mode, second pass", __METHOD__.','.__LINE__);
        $this->api->available_api_calls_for_crawler = 10;
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $retval = $twitter_crawler->fetchInstanceFavorites();
        // Save instance
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }
        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 3);
        $this->assertEqual($this->instance->last_page_fetched_favorites, 1);
    }

    public function testFetchFavoritesOfInstanceuserNoAPICalls() {
        $this->logger->logInfo("in testFetchFavoritesOfInstanceuserNoAPICalls", __METHOD__.','.__LINE__);
        self::setUpInstanceUserAmygdala();
        $this->api->available_api_calls_for_crawler = 0;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage1/');
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $retval = $twitter_crawler->fetchInstanceFavorites();
        // Save instance
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }

        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 0);
        $this->assertEqual($this->instance->last_page_fetched_favorites, 0);
        $this->assertEqual($this->instance->favorites_profile, 0);
    }

    public function testCleanupMissedFavs() {
        $this->logger->logInfo("in testCleanupMissedFavs", __METHOD__.','.__LINE__);
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');

        self::setUpInstanceUserAmygdala();
        $this->instance->last_unfav_page_checked = 3;
        $this->api->available_api_calls_for_crawler = 10;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage3/');
        //set cfg value
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-1';
        $builder2 = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name'=>'favs_cleanup_pages', 'option_value'=>3));

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $retval = $twitter_crawler->cleanUpMissedFavsUnFavs();
        $this->assertEqual($retval, true);
        // check that the count 'rolled over'
        $this->assertEqual($this->instance->last_unfav_page_checked, 0);
        // Save instance
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }
        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 27);
        $builder2 = null;
    }

    public function testCleanupMissedFavsBudget() {
        $this->logger->logInfo("in testCleanupMissedFavs", __METHOD__.','.__LINE__);
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');

        self::setUpInstanceUserAmygdala();
        // set up crawl limit budget
        $crawl_limit = array('getFavsPage' => array('count' => 2, 'remaining' => 0) );
        $this->api->setCallerLimits($crawl_limit);
        $this->instance->last_unfav_page_checked = 3;
        $this->api->available_api_calls_for_crawler = 10;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage3/');
        //set cfg value
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-1';
        $builder2 = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name'=>'favs_cleanup_pages', 'option_value'=>3));

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $retval = $twitter_crawler->cleanUpMissedFavsUnFavs();
        $this->assertEqual($this->instance->owner_favs_in_system, 0);
    }

    public function testAddRmOldFavMaintSearch() {
        $this->logger->logInfo("in testAddRmOldFavMaintSearch", __METHOD__.','.__LINE__);
        //set plugin cfg values
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-1';
        $builder2 = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name'=>'favs_older_pages','option_value'=>1));
        $builder3 = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name'=>'favs_cleanup_pages','option_value'=>3));

        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');

        self::setUpInstanceUserAmygdala();
        $this->api->available_api_calls_for_crawler = 3;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage3/');

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();
        $retval = $twitter_crawler->cleanUpMissedFavsUnFavs();
        $this->assertEqual($retval, true);
        $this->assertEqual($this->instance->last_unfav_page_checked, 3);
        // Save instance
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }
        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        // check fav count
        $this->assertEqual($this->instance->owner_favs_in_system, 40);

        $this->logger->logInfo("in testAddRmOldFavMaintSearch, second traversal", __METHOD__.','.__LINE__ );
        // now add an additional older fav , remove one, and traverse again
        $this->api->available_api_calls_for_crawler = 3;
        $this->instance->last_unfav_page_checked = 2;
        $this->api->to->setDataPath('webapp/plugins/twitter/tests/testdata/favs_tests/favs_stage6/');
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->fetchInstanceUserInfo();

        $retval = $twitter_crawler->cleanUpMissedFavsUnFavs();
        $this->assertEqual($retval, true);
        // Save instance
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }
        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        // check fav count- should have removed 2 and added 21...
        // update: due to issue with TwitterAPI, not currently removing un-favs from database
        // $this->assertEqual($this->instance->owner_favs_in_system, 59);
        $this->assertEqual($this->instance->owner_favs_in_system, 61);
        $builder2 = null; $builder3 = null;
    }

    public function testCleanUpFollows404() {
        self::setUpInstanceUserGinaTrapani();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);

        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($follow_dao->followExists('930061', '36823', 'twitter', true), 'Active follow exists');

        $twitter_crawler->cleanUpFollows();
        $this->assertFalse($follow_dao->followExists('930061', '36823', 'twitter', true), 'Follow marked inactive');
    }

    public function testCleanUpFollowsBudget() {
        self::setUpInstanceUserGinaTrapani();
        // set up crawl limit budget
        $crawl_limit = array('cleanUpFollows' => array('count' => 2, 'remaining' => 0) );
        $this->api->setCallerLimits($crawl_limit);
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->cleanUpFollows();
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($follow_dao->followExists(930061, 36823, 'twitter', true), 'Follow not marked inactive');
    }

    public function testLoggingErrorOutput() {
        self::setUpInstanceUserGinaTrapani();
        foreach ($this->api->getTwitterErrorCodes() as $error_code => $explanation) {
            $this->api->apiRequest($error_code, array(), true);
        }

        $logfile = file_get_contents(Config::getInstance()->getValue('log_location'));

        foreach ($this->api->getTwitterErrorCodes() as $error_code => $explanation) {
            $this->assertPattern("/{$explanation}/", $logfile);
        }
    }

    public function testGenerateInsightBaselines() {
        self::setUpInstanceUserGinaTrapani();
        // set up crawl limit budget
        $crawl_limit = array('cleanUpFollows' => array('count' => 2, 'remaining' => 0) );
        $this->api->setCallerLimits($crawl_limit);
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);

        $builders = array();
        //Add straight text posts
        $counter = 1;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            if ($counter % 3 == 0) {
                $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
            } else if ($counter % 3 == 1) {
                $source = '<a href="http://twitter.com/tweetbutton" rel="nofollow">Tweet Button</a>';
            } else {
                $source = 'web';
            }
            $builders[] = FixtureBuilder::build('posts', array('id'=>$counter+256, 'post_id'=>$counter+256,
            'author_user_id'=>'930061', 'author_username'=>'ginatrapani', 'author_fullname'=>'Gina Trapani',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>$source, 'pub_date'=>'-'.$counter.'d', 'in_reply_to_user_id'=>null,
            'reply_count_cache'=>($counter==10)?0:rand(0, 4), 'is_protected'=>0,
            'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter',
            'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }

        $twitter_crawler->generateInsightBaselines();
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $avg_rt_count_7_days = $insight_baseline_dao->getInsightBaseline('avg_retweet_count_last_7_days', 1);
        $this->assertEqual($avg_rt_count_7_days->value, 3);

        $avg_rt_count_30_days = $insight_baseline_dao->getInsightBaseline('avg_retweet_count_last_30_days', 1);
        $this->assertEqual($avg_rt_count_30_days->value, 12);
    }

    public function testGenerateInsights() {
        self::setUpInstanceUserGinaTrapani();
        // set up crawl limit budget
        $crawl_limit = array('cleanUpFollows' => array('count' => 2, 'remaining' => 0) );
        $this->api->setCallerLimits($crawl_limit);
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);

        $builders = array();
        //Add straight text posts
        $counter = 1;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            if ($counter % 3 == 0) {
                $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
            } else if ($counter % 3 == 1) {
                $source = '<a href="http://twitter.com/tweetbutton" rel="nofollow">Tweet Button</a>';
            } else {
                $source = 'web';
            }
            $builders[] = FixtureBuilder::build('posts', array('id'=>$counter+256, 'post_id'=>$counter+256,
            'author_user_id'=>'930061', 'author_username'=>'ginatrapani', 'author_fullname'=>'Gina Trapani',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>$source, 'pub_date'=>'-'.$counter.'d', 'in_reply_to_user_id'=>null,
            'reply_count_cache'=>($counter==10)?0:rand(0, 4), 'is_protected'=>0,
            'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter',
            'old_retweet_count_cache' => floor($counter/4), 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }
        //spike
        $builders[] = FixtureBuilder::build('posts', array('id'=>$counter+256, 'post_id'=>$counter+256,
        'author_user_id'=>'930061', 'author_username'=>'ginatrapani', 'author_fullname'=>'Gina Trapani',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
        'source'=>$source, 'pub_date'=>'-2d', 'in_reply_to_user_id'=>null,
        'reply_count_cache'=>($counter==10)?0:rand(0, 4), 'is_protected'=>0,
        'retweet_count_cache'=>200, 'network'=>'twitter',
        'old_retweet_count_cache' => floor($counter/4), 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        //spike
        $builders[] = FixtureBuilder::build('posts', array('id'=>$counter+257, 'post_id'=>$counter+257,
        'author_user_id'=>'930061', 'author_username'=>'ginatrapani', 'author_fullname'=>'Gina Trapani',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
        'source'=>$source, 'pub_date'=>'-2d', 'in_reply_to_user_id'=>null,
        'reply_count_cache'=>($counter==10)?0:rand(0, 4), 'is_protected'=>0,
        'retweet_count_cache'=>40, 'network'=>'twitter',
        'old_retweet_count_cache' => floor($counter/4), 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        $twitter_crawler->generateInsights();
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $insights = $insight_dao->getInsights( 1);
        //sleep(1000);
        print_r($insights);
    }
}
