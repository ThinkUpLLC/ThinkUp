<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterCrawler.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 * @copyright 2009-2013 Gina Trapani
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
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterAPIEndpoint.php';

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
        'last_seen'=>'-730d'));

        $this->builders[] = FixtureBuilder::build('groups', array('group_id'=>'19994710',
        'group_name'=>'@userx/anotherlist', 'network' => 'twitter', 'is_active'=>1));

        // stale group membership
        $this->builders[] = FixtureBuilder::build('group_members', array('member_user_id'=>'36823',
        'group_id'=>'19994710', 'is_active' => 1, 'network'=>'twitter', 'last_seen' => '-3d'));

        // insert hashtags
        $this->builders[] = FixtureBuilder::build('hashtags', array('id' => 1, 'hashtag' => '#mwc2013',
        'network' => 'twitter', 'count_cache' => 0));

        // insert instances_hashtags
        $this->builders[] = FixtureBuilder::build('instances_hashtags', array('id' => 1, 'instance_id' => 1,
        'hashtag_id' => 1, 'last_post_id' => '0', 'earliest_post_id' => '0'));
    }

    public function tearDown() {
        $this->builders = null;
        $this->logger->close();
        $this->instance = null;
        $this->api = null;
        parent::tearDown();
    }

    private function setUpInstanceUserAnilDash() {
        $r = array('id'=>1, 'network_username'=>'anildash', 'network_user_id'=>'36823', 'network_viewer_id'=>'36823',
        'last_post_id'=>'0', 'last_reply_id'=>'1001',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0',
        'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'total_posts_by_owner'=>1,
        'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',  'avg_replies_per_day'=>'2', 'is_public'=>'0',
        'is_active'=>'0', 'network'=>'twitter', 'last_favorite_id' => '0', 'favorites_profile' => '0',
        'owner_favs_in_system' => '0', 'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50,
        'percentage_links'=>50, 'earliest_post_in_system'=>'2009-01-01 13:48:05'
        );
        $this->instance = new TwitterInstance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth($oauth_token='111', $oauth_token_secret='222',
        $oauth_consumer_key = 'fake_key', $oauth_consumer_secret ='fake_secret', $archive_limit= 3200,
        $num_twitter_errors=2);

        $this->instance->is_archive_loaded_follows = true;
    }

    private function setUpInstanceUserAnilDashDelete() {
        $r = array('id'=>1, 'network_username'=>'anildash', 'network_user_id'=>'36825', 'network_viewer_id'=>'36823',
        'last_post_id'=>'0', 'last_reply_id'=>'10001',
        'total_posts_in_system'=>'21', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0',
        'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'total_posts_by_owner'=>1,
        'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',  'avg_replies_per_day'=>'2', 'is_public'=>'0',
        'is_active'=>'0', 'network'=>'twitter', 'last_favorite_id' => '0', 'favorites_profile' => '0',
        'owner_favs_in_system' => '0', 'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50,
        'percentage_links'=>50, 'earliest_post_in_system'=>'2009-01-01 13:48:05'
        );
        $this->instance = new TwitterInstance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth($oauth_token='111', $oauth_token_secret='222',
        $oauth_consumer_key = 'fake_key', $oauth_consumer_secret ='fake_secret', $archive_limit= 3200,
        $num_twitter_errors=2);

        $this->instance->is_archive_loaded_follows = true;
    }

    private function setUpInstanceUserAnilDashUsernameChange() {
        $r = array('id'=>1, 'network_username'=>'anildash', 'network_user_id'=>'36824', 'network_viewer_id'=>'36824',
        'last_post_id'=>'0', 'last_reply_id'=>'1001',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0',
        'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'total_posts_by_owner'=>1,
        'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',  'avg_replies_per_day'=>'2', 'is_public'=>'0',
        'is_active'=>'0', 'network'=>'twitter', 'last_favorite_id' => '0', 'favorites_profile' => '0',
        'owner_favs_in_system' => '0', 'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50,
        'percentage_links'=>50, 'earliest_post_in_system'=>'2009-01-01 13:48:05'
        );
        $this->instance = new TwitterInstance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth($oauth_token='111', $oauth_token_secret='222',
        $oauth_consumer_key = 'fake_key', $oauth_consumer_secret ='fake_secret', $archive_limit= 3200,
        $num_twitter_errors=2);

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
        'last_post_id'=>'0', 'last_reply_id'=>'1001',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 'total_follows_in_system'=>'0',
        'is_archive_loaded_replies'=>'0', 'is_archive_loaded_follows'=>'0', 'total_posts_by_owner'=>1,
        'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',  'avg_replies_per_day'=>'2', 'is_public'=>'0',
        'is_active'=>'0', 'network'=>'twitter', 'last_favorite_id' => '0', 'favorites_profile' => '0',
        'owner_favs_in_system' => '0', 'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50,
        'percentage_links'=>50, 'earliest_post_in_system'=>'2009-01-01 13:48:05'
        );
        $this->instance = new TwitterInstance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth($oauth_token='111', $oauth_token_secret='222',
        $oauth_consumer_key = 'fake_key', $oauth_consumer_secret ='fake_secret', $archive_limit= 3200,
        $num_twitter_errors=2);

        $this->instance->is_archive_loaded_follows = true;
    }

    private function setUpInstanceUserGinaTrapani() {
        $r = array('id'=>1, 'network_username'=>'ginatrapani', 'network_user_id'=>'930061',
        'network_viewer_id'=>'930061', 'last_post_id'=>'0', 'last_reply_id'=>'10001',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'twitter',
        'last_favorite_id' => '0', 'favorites_profile' => '0', 'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'2009-01-01 13:48:05'
        );
        $this->instance = new TwitterInstance($r);

        $this->api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 'fake_key', 'fake_secret', 2, 1234, 5, 350);
        $this->instance->is_archive_loaded_follows = true;
    }

    private function setUpInstanceUserAmygdala() {
        $instd = DAOFactory::getDAO('TwitterInstanceDAO');
        $iid = $instd->insert('2768241', 'amygdala', 'twitter');
        $this->instance = $instd->getByUsernameOnNetwork("amygdala", "twitter");

        $this->api = new CrawlerTwitterAPIAccessorOAuth($oauth_token='111', $oauth_token_secret='222',
        $oauth_consumer_key = 'fake_key', $oauth_consumer_secret ='fake_secret', $archive_limit= 3200,
        $num_twitter_errors=2);
    }

    public function testConstructor() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserAnilDash();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);

        $this->assertTrue($twitter_crawler != null);
    }

    public function testFetchInstanceUserInfo() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserAnilDash();

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/anildash/');

        $twitter_crawler->fetchInstanceUserInfo();

        $user_dao = DAOFactory::getDAO('UserDAO');
        $user = $user_dao->getDetails('36823', 'twitter');
        $this->assertTrue($user->id == 1);
        $this->assertTrue($user->user_id == '36823');
        $this->assertTrue($user->username == 'anildash');
        $this->assertTrue($user->found_in == 'Owner Status');
    }

    public function testFetchInstanceUserTweets() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserAnilDash();

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/anildash/');
        $twitter_crawler->fetchInstanceUserTweets();

        //Test post with location has location set
        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->assertTrue($post_dao->isPostInDB('15680112737', 'twitter'));

        $post = $post_dao->getPost('15680112737', 'twitter');
        $this->assertEqual($post->location, "NYC: 40.739069,-73.987082");
        $this->assertEqual($post->place, "Stuyvesant Town, New York");
        $this->assertEqual($post->geo, "40.73410845 -73.97885982");

        //Test post without location doesn't have it set
        $post = $post_dao->getPost('300434349633970176', 'twitter');
        $this->assertEqual($post->location, "NYC: 40.739069,-73.987082");
        $this->assertEqual($post->place, "");
        $this->assertEqual($post->geo, "");
    }

    public function testFetchInstanceUserTweetsEscapeHTML() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserAnilDash();

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/anildash/');
        $twitter_crawler->fetchInstanceUserTweets();

        //Test post with location has location set
        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->assertTrue($post_dao->isPostInDB('300395334222368769', 'twitter'));
        $post = $post_dao->getPost('300395334222368769', 'twitter');
        $this->assertEqual($post->post_text, "@rebeccablood it totally does!  > & < /");
    }

    public function testFetchInstanceUserTweetsUsernameChange() {
        $this->debug(__METHOD__);
        $post_builder = self::setUpInstanceUserAnilDashUsernameChange();

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>17,
        'network_username'=>'anildash2', 'network'=>'twitter', 'network_viewer_id'=>15,
        'crawler_last_run'=>'2010-01-01 12:00:01', 'is_active'=>1));

        $post_dao = DAOFactory::getDAO('PostDAO');

        // old post before crawl
        $post = $post_dao->getPost(1, 'twitter');
        $this->assertEqual($post->author_username, "anildash");

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/namechange/');
        $twitter_crawler->fetchInstanceUserTweets();

        // old post after crawl
        $post = $post_dao->getPost(1, 'twitter');
        $this->assertEqual($post->author_username, "anildash2");

        // new post have the new username as well...
        $post = $post_dao->getPost('300395334222368769', 'twitter');
        $this->assertEqual($post->author_username, "anildash2");

        // instace has the new username as well...
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
        $instance = $instance_dao->getByUsername("anildash");
        $this->assertNull($instance);

        $instance = $instance_dao->getByUsername("anildash2");
        $this->assertNotNull($instance);
    }

    public function testDeletedTweet() {
        $this->debug(__METHOD__);
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
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/deletedtweet/');
        $twitter_crawler->fetchInstanceUserTweets();

        // should be deleted, not  found on twitter...
        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->assertNull($post_dao->getPost('12345', 'twitter'));

        // found on twitter, so don't delete
        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->assertNotNull($post_dao->getPost('123456', 'twitter'));
    }

    public function testFetchPrivateInstanceUserTweets() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserPrivateMcPrivate();

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/privatetweets/');
        $twitter_crawler->fetchInstanceUserTweets();

        //Test post is set as protected
        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->assertTrue($post_dao->isPostInDB('300434349633970176', 'twitter'));

        $post = $post_dao->getPost('300434349633970176', 'twitter');
        $this->debug(Utils::varDumpToString($post));
        $this->assertTrue($post->is_protected);
    }

    public function testFetchInstanceUserTweetsRetweets() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserAmygdala();
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'2768241', 'network'=>'twitter'));

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/retweets/');
        $twitter_crawler->fetchInstanceUserTweets();

        $post_dao = DAOFactory::getDAO('PostDAO');
        $post = $post_dao->getPost('297179577304875011', 'twitter');
        // the JSON retweeted_status for the original post (that 'amygdala' retweeted) includes a native
        // retweet_count of 114. In our database we only have 1 of those RTs stored/processed.
        $this->assertNotNull($post);
        $this->assertIsA($post, 'Post');
        $this->assertEqual($post->retweet_count_api, 114);
        $this->assertEqual($post->retweet_count_cache, 1);
        $this->assertEqual($post->old_retweet_count_cache, 0);
        $retweets = $post_dao->getRetweetsOfPost('297179577304875011', 'twitter', true);
        $this->assertEqual(sizeof($retweets), 1);

        $post = $post_dao->getPost('300464193944055808', 'twitter');
        $this->assertEqual($post->in_retweet_of_post_id, '297179577304875011');
        $this->assertEqual($post->in_rt_of_user_id, '14248315');

        $twitter_crawler->fetchInstanceUserMentions();
        // old-style RT
        $post = $post_dao->getPost('298872594713673728', 'twitter');
        $this->assertEqual($post->in_rt_of_user_id, '2768241');
        $this->assertEqual($post->in_retweet_of_post_id, '298865318707752960');
        $post_orig = $post_dao->getPost('298865318707752960', 'twitter');
        $this->assertEqual($post_orig->old_retweet_count_cache, 1);
        $this->assertEqual($post_orig->retweet_count_cache, 0);
        $this->assertEqual($post_orig->retweet_count_api, 1);

        $mention_dao = DAOFactory::getDAO('MentionDAO');
        $mentions = $mention_dao->getMentionInfoUserName('amygdala', 'twitter');
        $this->assertEqual($mentions['count_cache'], 10);
    }

    public function testFetchInstanceUserFollowers() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserAnilDash();
        $this->instance->is_archive_loaded_follows = false;
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/anildash/');

        $twitter_crawler->fetchInstanceUserFollowers();
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($follow_dao->followExists('36823', '867231067', 'twitter'));

        $user_dao = DAOFactory::getDAO('UserDAO');
        $updated_user = $user_dao->getUserByName('m7md_3ssaam', 'twitter');
        $this->assertEqual($updated_user->full_name, 'm7md');
        $this->assertEqual($updated_user->location, 'egypt');
    }

    public function testFetchInstanceUserGroups() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserAnilDash();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/anildash/');

        $twitter_crawler->fetchInstanceUserGroups();
        $group_dao = DAOFactory::getDAO('GroupDAO');
        $this->assertTrue($group_dao->isGroupInStorage($group = '79854285', 'twitter'));

        $group_member_dao = DAOFactory::getDAO('GroupMemberDAO');
        $this->assertTrue($group_member_dao->isGroupMemberInStorage($user = '36823', $group = '79854285', 'twitter'));
        $this->assertFalse($group_member_dao->isGroupMemberInStorage($user = '930061', $group = '79854285', 'twitter'));

        $count_history_dao = DAOFactory::getDAO('CountHistoryDAO');
        $history = $count_history_dao->getHistory($user = '36823', 'twitter', 'DAYS', null, 'group_memberships');
        $this->assertEqual(count($history['history']), 1);
    }

    public function testUpdateStaleGroupMemberships() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserAnilDash();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/anildash/');

        $group_member_dao = DAOFactory::getDAO('GroupMemberDAO');
        $this->assertTrue($group_member_dao->isGroupMemberInStorage($user = '36823', $group = '19994710', 'twitter',
        $active = true));

        $twitter_crawler->updateStaleGroupMemberships();
        $this->assertFalse($group_member_dao->isGroupMemberInStorage($user = '36823', $group = '19994710', 'twitter',
        $active = true));
    }

    public function testFetchInstanceUserFriends() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserAnilDash();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/anildash/');

        $twitter_crawler->fetchInstanceUserFriends();

        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($follow_dao->followExists('47462001', '36823', 'twitter'));

        $user_dao = DAOFactory::getDAO('UserDAO');
        $updated_user = $user_dao->getUserByName('teddygoff', 'twitter');
        $this->assertEqual($updated_user->full_name, 'Teddy Goff');
        $this->assertEqual($updated_user->description,
        "Digital Director, Obama for America // Teddy dot goff at gmail dot com");
    }

    public function testUpdateFriendsProfiles() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserAnilDash();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/anildash/');

        $twitter_crawler->updateFriendsProfiles();

        $user_dao = DAOFactory::getDAO('UserDAO');
        $updated_user = $user_dao->getUserByName('ginatrapani', 'twitter');
        $this->debug(Utils::varDumpToString($updated_user));
        $this->assertEqual($updated_user->full_name, 'Gina Trapani');
        $this->assertEqual($updated_user->description,
            "I make @ThinkUp & @todotxt. Back in the day I started @Lifehacker.");
    }

    public function testFetchInstanceUserFollowersByIds() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserAnilDash();
        $this->instance->is_archive_loaded_follows = true; //force fetch by IDs
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/anildash/');

        $twitter_crawler->fetchInstanceUserFollowers();
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($follow_dao->followExists('36823', '166290287', 'twitter'));
    }

    public function testFetchRetweetsOfInstanceUser() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserGinaTrapani();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/ginatrapani/');

        //first, load retweeted tweet into db
        // we now get the 'new-style' retweet count from the retweet_count field in the xml,
        // which is parsed into 'retweet_count_cache' in the post vals.  This will not necessarily match
        // the number of retweets in the database any more (but does in this test case).
        $builder = FixtureBuilder::build('posts', array('post_id'=>'300000912989118466', 'author_user_id'=>'930061',
        'author_username'=>'ginatrapani', 'author_fullname'=>'Gina Trapani', 'post_text'=>
        '@jjg unsurprisingly Dykes Lumber in Brooklyn has a thriving t-shirt business', 'pub_date'=>'-1d',
        // start w/ the RT counts zeroed out, let the processing populate them
        'reply_count_cache'=>1, 'old_retweet_count_cache'=>0, 'retweet_count_cache'=>0, 'retweet_count_api' => 0));

        $post_dao = DAOFactory::getDAO('PostDAO');
        $twitter_crawler->fetchRetweetsOfInstanceUser();

        $post = $post_dao->getPost('300000912989118466', 'twitter');
        $this->assertEqual($post->retweet_count_cache, 1, '1 new-style retweet from count cache');
        // in processing the retweets of the post, if they contain a <retweeted_status> element pointing
        // to the original post, and that original post information includes a retweet count, we will update the
        // original post in the db with that count.  In this test data that count is 2, 'behind' the database info.
        $this->assertEqual($post->retweet_count_api, 1, '1 new-style retweet count from API');
        // should not have processed any old-style retweets here
        $this->assertEqual($post->old_retweet_count_cache, 0, '0 old-style retweets count from API');

        $retweets = $post_dao->getRetweetsOfPost('300000311127457792', 'twitter', true);
        $this->assertEqual(sizeof($retweets), 0, '0 retweets loaded');

        //make sure duplicate posts aren't going into the db on next crawler run
        self::setUpInstanceUserGinaTrapani();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/ginatrapani/');
        $twitter_crawler->fetchInstanceUserInfo();

        $twitter_crawler->fetchRetweetsOfInstanceUser();
        $post = $post_dao->getPost('300000912989118466', 'twitter');
        $this->assertEqual($post->retweet_count_cache, 1, '1 new-style retweet from count cache');
        $this->assertEqual($post->retweet_count_api, 1, '1 new-style retweet count from API');
        $retweets = $post_dao->getRetweetsOfPost('300000912989118466', 'twitter', true);
        $this->assertEqual(sizeof($retweets), 0, '0 retweets loaded');

        $post = $post_dao->getPost('300000311127457792', 'twitter');
        $rts2 = $post_dao->getRetweetsOfPost('300000311127457792', 'twitter', true);
        $this->assertEqual(sizeof($rts2), 0, '0 retweets loaded');
        //$this->assertEqual($rts2[0]->in_rt_of_user_id, '930061');
    }

    public function testFetchStrayRepliedToTweets() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserAnilDash();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/anildash/');

        $twitter_crawler->fetchInstanceUserTweets();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $tweets = $post_dao->getAllPostsByUsername('anildash', 'twitter');
        $this->debug(Utils::varDumpToString($tweets));

        $twitter_crawler->fetchStrayRepliedToTweets();
        $post = $post_dao->getPost('300389504274022400', 'twitter');
        $this->assertTrue(isset($post));
        $this->debug(Utils::varDumpToString($post));
        $this->assertEqual($post->reply_count_cache, 2);
    }

    public function testFetchFavoritesOfInstanceuser() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserAmygdala();
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/amygdalafavs/');
        $twitter_crawler->fetchInstanceUserFavorites();
        // Save instance
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
        if (isset($twitter_crawler->user)) {
            $instance_dao->save($this->instance, $twitter_crawler->user->post_count, $this->logger);
        }
        $this->instance = $instance_dao->getByUsernameOnNetwork("amygdala", "twitter");
        $this->assertEqual($this->instance->owner_favs_in_system, 20);
    }

    private function setUpInstanceUserEduardCucurella() {
        $r = array('id'=>1, 'network_username'=>'ecucurella', 'network_user_id'=>'13771532',
            'network_viewer_id'=>'13771532', 'last_post_id'=>'0', 'last_reply_id'=>'10001',
            'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
            'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
            'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
            'avg_replies_per_day'=>'0', 'is_public'=>'0', 'is_active'=>'1', 'network'=>'twitter',
            'last_favorite_id' => '0', 'favorites_profile' => '0', 'owner_favs_in_system' => '0',
            'total_posts_by_owner'=>0, 'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50,
            'percentage_links'=>50, 'earliest_post_in_system'=>'2009-01-01 13:48:05');
        $this->instance = new TwitterInstance($r);
        $this->api = new CrawlerTwitterAPIAccessorOAuth($oauth_token='111', $oauth_token_secret='222',
        $oauth_consumer_key = 'fake_key', $oauth_consumer_secret ='fake_secret', $archive_limit= 3200,
        $num_twitter_errors=2);
        $this->instance->is_archive_loaded_follows = true;
    }

    public function testFetchInstanceHashtagTweets () {
        $this->debug(__METHOD__);
        self::setUpInstanceUserEduardCucurella();

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/searchtweets/');
        $instance_hashtag_dao = DAOFactory::getDAO('InstanceHashtagDAO');
        $instances_hashtags = $instance_hashtag_dao->getByInstance(1);
        $twitter_crawler->fetchInstanceHashtagTweets($instances_hashtags[0]);

        $post_dao = DAOFactory::getDAO('PostDAO');
        $this->assertTrue($post_dao->isPostInDB('307436813180616704', 'twitter'));
        $this->assertTrue($post_dao->isPostInDB('307436651154665473', 'twitter'));
        $user_dao = DAOFactory::getDAO('UserDAO');
        $this->assertTrue($user_dao->isUserInDB('2485041', 'twitter'));
        $this->assertTrue($user_dao->isUserInDB('251219944', 'twitter'));
        $hashtag_dao = DAOFactory::getDAO('HashtagDAO');
        $res = $hashtag_dao->getHashtagByID(1);
        $this->assertEqual($res->hashtag, '#mwc2013');
        $this->assertEqual($res->network, 'twitter');
        $this->assertEqual($res->count_cache, 2); //2 tweets
        $hashtagpost_dao = DAOFactory::getDAO('HashtagPostDAO');
        $res = $hashtagpost_dao->getHashtagPostsByHashtagID(1);
        $this->assertEqual(sizeof($res), 2); //2 tweets
        $link_dao = DAOFactory::getDAO('LinkDAO');
        $res = $link_dao->getLinksForPost('307436813180616704','twitter');
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]->url, 'http://t.co/yPMZd3eTNb');
        $res = $link_dao->getLinksForPost('307436651154665473','twitter');
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]->url, 'http://t.co/8yet1gjfDm');
    }

    public function testFetchInstanceHashtagTweetsPostAndUserExistInDB () {
        $this->debug(__METHOD__);
        self::setUpInstanceUserEduardCucurella();

        $post_dao = DAOFactory::getDAO('PostDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');
        $hashtag_dao = DAOFactory::getDAO('HashtagDAO');
        $hashtagpost_dao = DAOFactory::getDAO('HashtagPostDAO');
        $link_dao = DAOFactory::getDAO('LinkDAO');

        //Post and User NOT exist
        $this->assertFalse($post_dao->isPostInDB('307436813180616704', 'twitter'));
        $this->assertFalse($user_dao->isUserInDB('2485041', 'twitter'));
        $this->assertEqual($post_dao->getTotalPostsByUser('GinaTost','twitter'),0);

        $builder = $this->buildDataPostUser();

        //Post and User exist
        $this->assertTrue($post_dao->isPostInDB('307436813180616704', 'twitter'));
        $this->assertTrue($user_dao->isUserInDB('2485041', 'twitter'));
        $this->assertEqual($post_dao->getTotalPostsByUser('GinaTost','twitter'),1);

        //crawls
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/searchtweets/');
        $instance_hashtag_dao = DAOFactory::getDAO('InstanceHashtagDAO');
        $instances_hashtags = $instance_hashtag_dao->getByInstance(1);
        $twitter_crawler->fetchInstanceHashtagTweets($instances_hashtags[0]);

        //Post exist
        $this->assertTrue($post_dao->isPostInDB('307436813180616704', 'twitter'));
        $this->assertTrue($user_dao->isUserInDB('2485041', 'twitter'));
        $this->assertEqual($post_dao->getTotalPostsByUser('GinaTost','twitter'),1);
        $this->assertTrue($post_dao->isPostInDB('307436651154665473', 'twitter'));
        $this->assertTrue($user_dao->isUserInDB('251219944', 'twitter'));
        $this->assertEqual($post_dao->getTotalPostsByUser('XerpaC','twitter'),1);

        //Hashtag
        $res = $hashtag_dao->getHashtagByID(1);
        $this->assertEqual($res->hashtag, '#mwc2013');
        $this->assertEqual($res->network, 'twitter');
        $this->assertEqual($res->count_cache, 2);

        //How many posts for hashtag_id 1 in tu_hashtags_posts
        $res = $hashtagpost_dao->getHashtagPostsByHashtagID(1);
        $this->assertEqual(sizeof($res), 2);
        $this->assertEqual($res[0]['post_id'],307436651154665473);
        $this->assertEqual($res[1]['post_id'],307436813180616704);


        $res = $link_dao->getLinksForPost('307436813180616704','twitter');
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]->url, 'http://t.co/yPMZd3eTNb');
        $res = $link_dao->getLinksForPost('307436651154665473','twitter');
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]->url, 'http://t.co/8yet1gjfDm');
    }

    public function testFetchInstanceHashtagTweetsOnlyPostExistInDB () {
        $this->debug(__METHOD__);
        self::setUpInstanceUserEduardCucurella();

        $post_dao = DAOFactory::getDAO('PostDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');
        $hashtag_dao = DAOFactory::getDAO('HashtagDAO');
        $link_dao = DAOFactory::getDAO('LinkDAO');
        $hashtagpost_dao = DAOFactory::getDAO('HashtagPostDAO');

        //Post and User NOT exist
        $this->assertFalse($post_dao->isPostInDB('307436813180616704', 'twitter'));
        $this->assertFalse($user_dao->isUserInDB('2485041', 'twitter'));
        $this->assertEqual($post_dao->getTotalPostsByUser('GinaTost','twitter'),0);

        $builder = $this->buildDataPost();

        //Post exist and User NOT exist
        $this->assertTrue($post_dao->isPostInDB('307436813180616704', 'twitter'));
        $this->assertFalse($user_dao->isUserInDB('2485041', 'twitter'));
        $this->assertEqual($post_dao->getTotalPostsByUser('GinaTost','twitter'),1);

        //crawls
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/searchtweets/');
        $instance_hashtag_dao = DAOFactory::getDAO('InstanceHashtagDAO');
        $instances_hashtags = $instance_hashtag_dao->getByInstance(1);
        $twitter_crawler->fetchInstanceHashtagTweets($instances_hashtags[0]);

        //Post exist
        $this->assertTrue($post_dao->isPostInDB('307436813180616704', 'twitter'));
        $this->assertTrue($user_dao->isUserInDB('2485041', 'twitter'));
        $this->assertEqual($post_dao->getTotalPostsByUser('GinaTost','twitter'),1);
        $this->assertTrue($post_dao->isPostInDB('307436651154665473', 'twitter'));
        $this->assertTrue($user_dao->isUserInDB('251219944', 'twitter'));
        $this->assertEqual($post_dao->getTotalPostsByUser('XerpaC','twitter'),1);

        //Hashtag
        $res = $hashtag_dao->getHashtagByID(1);
        $this->assertEqual($res->hashtag, '#mwc2013');
        $this->assertEqual($res->network, 'twitter');
        $this->assertEqual($res->count_cache, 2);

        //How many posts for hashtag_id 1 in tu_hashtags_posts
        $res = $hashtagpost_dao->getHashtagPostsByHashtagID(1);
        $this->assertEqual(sizeof($res), 2);
        $this->assertEqual($res[0]['post_id'],307436651154665473);
        $this->assertEqual($res[1]['post_id'],307436813180616704);

        $res = $link_dao->getLinksForPost('307436813180616704','twitter');
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]->url, 'http://t.co/yPMZd3eTNb');
        $res = $link_dao->getLinksForPost('307436651154665473','twitter');
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]->url, 'http://t.co/8yet1gjfDm');
    }

    public function testFetchInstanceHashtagTweetsOnlyUserExistInDB () {
        $this->debug(__METHOD__);
        self::setUpInstanceUserEduardCucurella();

        $post_dao = DAOFactory::getDAO('PostDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');
        $hashtag_dao = DAOFactory::getDAO('HashtagDAO');
        $hashtagpost_dao = DAOFactory::getDAO('HashtagPostDAO');
        $link_dao = DAOFactory::getDAO('LinkDAO');

        //Post and User NOT exist
        $this->assertFalse($post_dao->isPostInDB('307436813180616704', 'twitter'));
        $this->assertFalse($user_dao->isUserInDB('2485041', 'twitter'));
        $this->assertEqual($post_dao->getTotalPostsByUser('GinaTost','twitter'),0);

        $builder = $this->buildDataUser();

        //Post NOT exist and User exist
        $this->assertFalse($post_dao->isPostInDB('307436813180616704', 'twitter'));
        $this->assertTrue($user_dao->isUserInDB('2485041', 'twitter'));
        $this->assertEqual($post_dao->getTotalPostsByUser('GinaTost','twitter'),0);

        //crawls
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/searchtweets/');
        $instance_hashtag_dao = DAOFactory::getDAO('InstanceHashtagDAO');
        $instances_hashtags = $instance_hashtag_dao->getByInstance(1);
        $twitter_crawler->fetchInstanceHashtagTweets($instances_hashtags[0]);

        //Post exist
        $this->assertTrue($post_dao->isPostInDB('307436813180616704', 'twitter'));
        $this->assertTrue($user_dao->isUserInDB('2485041', 'twitter'));
        $this->assertEqual($post_dao->getTotalPostsByUser('GinaTost','twitter'),1);
        $this->assertTrue($post_dao->isPostInDB('307436651154665473', 'twitter'));
        $this->assertTrue($user_dao->isUserInDB('251219944', 'twitter'));
        $this->assertEqual($post_dao->getTotalPostsByUser('XerpaC','twitter'),1);

        //Hashtag
        $res = $hashtag_dao->getHashtagByID(1);
        $this->assertEqual($res->hashtag, '#mwc2013');
        $this->assertEqual($res->network, 'twitter');
        $this->assertEqual($res->count_cache, 2);

        //How many posts for hashtag_id 1 in tu_hashtags_posts
        $res = $hashtagpost_dao->getHashtagPostsByHashtagID(1);
        $this->assertEqual(sizeof($res), 2);
        $this->assertEqual($res[0]['post_id'], '307436651154665473');
        $this->assertEqual($res[1]['post_id'], '307436813180616704');


        $res = $link_dao->getLinksForPost('307436813180616704','twitter');
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]->url, 'http://t.co/yPMZd3eTNb');
        $res = $link_dao->getLinksForPost('307436651154665473','twitter');
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]->url, 'http://t.co/8yet1gjfDm');
    }

    public function testCleanUpFollowsDeactivateDueToError163() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserEduardCucurella();
        // First test that the existing data is correct
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $this->assertTrue($follow_dao->followExists('930061', '36823', 'twitter', true));
        // Set up a Twitter Crawler to get the mocked Error 403 & API Error 163
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/ecucurella-163/');
        // Call cleanUpFollows which should set the follow to inactive
        $twitter_crawler->cleanUpFollows();
        // Now check the data is as expected
        $this->assertFalse($follow_dao->followExists('930061', '36823', 'twitter', true));
    }

    public function testCleanUpFollowsReactivate() {
        $this->debug(__METHOD__);
        self::setUpInstanceUserEduardCucurella();
        // First test that the existing data is correct
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $follow_dao->deactivate('930061', '36823', 'twitter');
        $this->assertFalse($follow_dao->followExists('930061', '36823', 'twitter', true));
        // Set up a Twitter crawler
        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/ecucurella/');
        // Call cleanUpFollows which should set the follow to inactive
        $twitter_crawler->cleanUpFollows();
        // Now check the data is as expected
        $this->assertFalse($follow_dao->followExists('930061', '36823', 'twitter', true));
    }

    public function testMediaHandling() {
        $r = array('id'=>1, 'network_username'=>'ecucurella', 'network_user_id'=>'13771532',
            'network_viewer_id'=>'13771532', 'last_post_id'=>'0', 'last_reply_id'=>'10001',
            'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
            'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
            'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
            'avg_replies_per_day'=>'0', 'is_public'=>'0', 'is_active'=>'1', 'network'=>'twitter',
            'last_favorite_id' => '0', 'favorites_profile' => '0', 'owner_favs_in_system' => '0',
            'total_posts_by_owner'=>0, 'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50,
            'percentage_links'=>50, 'earliest_post_in_system'=>'2009-01-01 13:48:05');
        $this->instance = new TwitterInstance($r);
        $this->api = new CrawlerTwitterAPIAccessorOAuth($oauth_token='111', $oauth_token_secret='222',
            $oauth_consumer_key = 'fake_key', $oauth_consumer_secret ='fake_secret', $archive_limit= 3200,
            $num_twitter_errors=2);
        $this->instance->is_archive_loaded_follows = true;

        $post_dao = DAOFactory::getDAO('PostDAO');
        $link_dao = DAOFactory::getDAO('LinkDAO');

        $twitter_crawler = new TwitterCrawler($this->instance, $this->api);
        $twitter_crawler->api->to->setDataPathFolder('testoftwittercrawler/cdmoyer/');
        $twitter_crawler->fetchInstanceUserTweets();

        $post_dao = DAOFactory::getDAO('PostDAO');

        // NOrmal photo
        $post = $post_dao->getPost("471310898695794688", 'twitter');
        $this->assertNotNull($post);
        $this->assertEqual("Just hanging out under the couch, like you do. http://t.co/1z8GGl5Zrv", $post->post_text);
        $this->assertEqual(1, count($post->links));
        $this->assertEqual('http://pbs.twimg.com/media/Bopuw9BIEAAAYVN.jpg', $post->links[0]->image_src);
        $this->assertEqual('http://t.co/1z8GGl5Zrv', $post->links[0]->url);
        $this->assertEqual('http://twitter.com/CDMoyer/status/471310898695794688/photo/1',
            $post->links[0]->expanded_url);


        // Normal Photo
        $post = $post_dao->getPost("458015480960532480", 'twitter');
        $this->assertEqual('http://pbs.twimg.com/media/BlsypxzIIAAwgnw.jpg', $post->links[0]->image_src);
        $this->assertEqual('http://t.co/gELt0NIzCx', $post->links[0]->url);
        $this->assertEqual('http://twitter.com/CDMoyer/status/458015480960532480/photo/1',
            $post->links[0]->expanded_url);

        // Photo and link in retweet
        $post = $post_dao->getPost("462748167357091840", 'twitter');
        $this->assertEqual(2, count($post->links));
        $this->assertEqual('http://t.co/uGLsKU8Qkc', $post->links[0]->url);
        $this->assertEqual('',$post->links[0]->expanded_url);
        $this->assertEqual('http://t.co/wpdAD4iQzB', $post->links[1]->url);
        $this->assertEqual('http://twitter.com/pourmecoffee/status/462748167357091840/photo/1',
            $post->links[1]->expanded_url);
        $this->assertEqual('http://pbs.twimg.com/media/BmwDAUoCIAAM_H4.jpg',$post->links[1]->image_src);

        // Nothing
        $post = $post_dao->getPost("454606554801524736", 'twitter');
        $this->assertEqual(0, count($post->links));
    }

    public function buildDataPostUser() {
        $builders = array();
        $builders[] = FixtureBuilder::build('posts', array(
            'post_id' => '307436813180616704',
            'author_user_id' => '2485041',
            'author_username' => 'GinaTost',
            'author_fullname' => 'Gina Tost',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 20166,
            'post_text' => 'Resumen del #MWC2013 http://t.co/yPMZd3eTNb',
            'is_protected' => 0,
            'source' => 'web',
            'location' => 'Barcelona',
            'place' => '',
            'place_id' => '',
            'geo' => '',
            'pub_date' => '2013-02-28 11:02:34',
            'in_reply_to_user_id' => '',
            'in_reply_to_post_id' => '',
            'reply_count_cache' => 1,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => '',
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'twitter',
            'is_geo_encoded' => 0,
            'in_rt_of_user_id' => '',
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0));
        $builders[] = FixtureBuilder::build('users', array(
            'user_id'=>2485041,
            'user_name'=>'GinaTost',
            'full_name'=>'Gina Tost'));
        return $builders;
    }

    public function buildDataPost() {
        $builders = array();
        $builders[] = FixtureBuilder::build('posts', array(
            'post_id' => '307436813180616704',
            'author_user_id' => '2485041',
            'author_username' => 'GinaTost',
            'author_fullname' => 'Gina Tost',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 20166,
            'post_text' => 'Resumen del #MWC2013 http://t.co/yPMZd3eTNb',
            'is_protected' => 0,
            'source' => 'web',
            'location' => 'Barcelona',
            'place' => '',
            'place_id' => '',
            'geo' => '',
            'pub_date' => '2013-02-28 11:02:34',
            'in_reply_to_user_id' => '',
            'in_reply_to_post_id' => '',
            'reply_count_cache' => 1,
            'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => '',
            'old_retweet_count_cache' => 0,
            'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0,
            'network' => 'twitter',
            'is_geo_encoded' => 0,
            'in_rt_of_user_id' => '',
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
            'favlike_count_cache' => 0));
        return $builders;
    }

    public function buildDataUser() {
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array(
            'user_id'=>'2485041',
            'user_name'=>'GinaTost',
            'full_name'=>'Gina Tost'));
        return $builders;
    }
}
