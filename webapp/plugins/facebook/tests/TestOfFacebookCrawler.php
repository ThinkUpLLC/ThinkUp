<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/TestOfFacebookCrawler.php
 *
 * Copyright (c) 2009-2016 Gina Trapani
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
 *
 * Test of FacebookCrawler
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2016 Gina Trapani
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/model/class.FacebookCrawler.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/model/class.FacebookInstance.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/model/class.FacebookInstanceMySQLDAO.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/tests/classes/mock.FacebookGraphAPIAccessor.php';

class TestOfFacebookCrawler extends ThinkUpUnitTestCase {
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
        $r = array('id'=>1, 'network_username'=>'Gina Trapani', 'network_user_id'=>'606837591',
        'network_viewer_id'=>'606837591', 'last_post_id'=>'0',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'facebook',
        'last_favorite_id' => '0', 'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'2009-01-01 13:48:05', 'favorites_profile' => '0'
        );
        $this->profile1_instance = new Instance($r);

        $r = array('id'=>3, 'network_username'=>'Lifehacker', 'network_user_id'=>'7568536355',
        'network_viewer_id'=>'729597743', 'last_post_id'=>'0',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'facebook page',
        'last_favorite_id' => '0', 'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'2009-01-01 13:48:05', 'favorites_profile' => '0'
        );
        $this->page1_instance = new Instance($r);

        $r = array('id'=>5, 'network_username'=>'Liz Lemon', 'network_user_id'=>'123456-session-expired',
        'network_viewer_id'=>'123456-session-expired', 'last_post_id'=>'0',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'facebook',
        'last_favorite_id' => '0', 'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'2009-01-01 13:48:05', 'favorites_profile' => '0'
        );
        $this->profile3_instance = new Instance($r);
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    public function testConstructor() {
        $fbc = new FacebookCrawler($this->profile1_instance, 'abc', 10);
        $this->assertEqual($fbc->access_token, 'abc');
    }

    public function testConstructorExtendedInitialMaxCrawlTime() {
        $fbc = new FacebookCrawler($this->profile1_instance, 'abc', 10);
        //Assert max_crawl_time gets uppped for initial crawl
        $this->assertEqual($fbc->max_crawl_time, FacebookCrawler::MAX_CRAWL_TIME_EXTENDED);

        //Put user in DB
        $fbc->fetchUser($this->profile1_instance->network_user_id, $this->profile1_instance->network, "Owner Status");
        $fbc = new FacebookCrawler($this->profile1_instance, 'abc', 10);
        //Assert max_crawl_time does not get upped
        $this->assertEqual($fbc->max_crawl_time, 10);
    }

    public function testFetchUser() {
        $fbc = new FacebookCrawler($this->profile1_instance, 'abc', 10);
        $fbc->fetchUser($this->profile1_instance->network_user_id, $this->profile1_instance->network, "Owner Status");
        $user_dao = new UserMySQLDAO();
        $user = $user_dao->getUserByName('Gina Trapani', 'facebook');

        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'Gina Trapani');
        $this->assertEqual($user->full_name, 'Gina Trapani');
        $this->assertEqual($user->user_id, 606837591);
        $this->assertEqual($user->url, '');
        $this->assertTrue($user->is_protected);
        $this->assertNotNull($user->joined);
    }

    public function testFetchPostsAndRepliesForProfile1() {
        $fbc = new FacebookCrawler($this->profile1_instance, 'abc', 120);

        $config = Config::getInstance();
        $facebook_crawler_log = $config->getValue('log_location');
        // prepare log for reading after fetchPostsAndReplies
        $log_reader_handle = fopen($facebook_crawler_log, 'r');
        fseek($log_reader_handle, 0, SEEK_END);

        $fbc->fetchPostsAndReplies();

        fflush($this->logger->log);
        $log_written = stream_get_contents($log_reader_handle);
        fclose($log_reader_handle);
        $this->assertFalse(preg_match('/FacebookCrawler::fetchPostsAndReplies,\d+ \| 0 Facebook posts found on page 2/',
        $log_written));

        $post_dao = new PostMySQLDAO();
        $post = $post_dao->getPost('10152701402567592', 'facebook');
        $this->assertEqual($post->post_text, 'I hate email, but I love this smart daily email about the day\'s most '.
            'interesting news.');
        $this->assertEqual($post->reply_count_cache, 0);
        $this->assertTrue($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 4);

        $post = $post_dao->getPost('10152788568177592', 'facebook');
        $this->assertPattern('/EqualPayDay/', $post->post_text);
        $this->assertEqual($post->reply_count_cache, 42);
        $this->assertTrue($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 88);

        //Wall post
        $post = $post_dao->getPost('10103607414559137', 'facebook');
        $this->assertEqual($post->author_user_id, '3601796');
        $this->assertEqual($post->in_reply_to_user_id, $this->profile1_instance->network_user_id);

        //Comment
        $post = $post_dao->getPost('10152788614032592', 'facebook');
        $this->assertPattern('/thank you for reporting that/', $post->post_text);
        $this->assertEqual($post->reply_count_cache, 0);
        $this->assertEqual($post->in_reply_to_post_id, '10152788568177592');
        $this->assertTrue($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 0);

        //Author user object
        $user_dao = new UserMySQLDAO();
        $user = $user_dao->getUserByName('Gina Trapani', 'facebook');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'Gina Trapani');
        $this->assertEqual($user->full_name, 'Gina Trapani');
        $this->assertEqual($user->user_id, '606837591');
        $this->assertEqual($user->avatar, 'https://graph.facebook.com/606837591/picture');
        $this->assertTrue($user->is_protected);

        //Wall poster user object
        $user = $user_dao->getUserByName('Francisco Zamudio', 'facebook');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->user_id, '3601796');
        $this->assertEqual($user->avatar, 'https://graph.facebook.com/3601796/picture');
        $this->assertTrue($user->is_protected);

        //Test post with a link to a video
        $post = $post_dao->getPost('10152432436257592', 'facebook');
        $this->assertEqual($post->post_text,
            'Uggh: 10 Hours of Walking in NYC as a Woman: http://youtu.be/b1XGPvbWn0A');
        $this->assertEqual(sizeof($post->links), 1);
        $this->assertEqual($post->links[0]->url, 'http://youtu.be/b1XGPvbWn0A');
        $this->assertEqual($post->links[0]->expanded_url,'');
        $this->assertEqual($post->links[0]->caption, 'youtube.com');
        $this->assertEqual($post->links[0]->description,
        'Donate to Hollaback! https://donatenow.networkforgood.org/hollaback Director/Producer/Creator: '.
        'Rob Bliss Creative - http://robblisscreative.com/ Media Contac...');
        $this->assertEqual($post->links[0]->title, '10 Hours of Walking in NYC as a Woman');
        $this->assertEqual($post->links[0]->post_key, 97);

        // Test Facebook paging by confirming post on second "page"
        $post = $post_dao->getPost('10152420491407592', 'facebook');
        $this->assertNotNull($post);
        $this->assertEqual($post->author_user_id, '606837591');
    }

    public function testFetchPostsAndRepliesForProfile3Error() {
        $fbc = new FacebookCrawler($this->profile3_instance, 'abc', 10);

        $this->expectException('APIOAuthException',
        'Error validating access token: Session has expired at unix time SOME_TIME. The current unix time is '.
        'SOME_TIME.');

        $fbc->fetchPostsAndReplies();
    }

    public function testFetchPostsAndRepliesForPage() {
        $fbc = new FacebookCrawler($this->page1_instance, 'abc', 10);

        $fbc->fetchPostsAndReplies();

        $post_dao = new PostMySQLDAO();
        $post = $post_dao->getPost('10152714332426356', 'facebook page');
        $this->assertEqual($post->post_text, 'Looks like a very serene place to work:');
        $this->assertFalse($post->is_protected);
        $this->assertEqual($post->reply_count_cache, 4);

        //test link with image
        $this->assertEqual(sizeof($post->links), 1);
        $this->assertEqual($post->links[0]->url, 'http://lifehac.kr/Jg0mR3E');
        $this->assertEqual($post->links[0]->expanded_url, '');
        $this->assertEqual($post->links[0]->image_src,
        'https://fbexternal-a.akamaihd.net/safe_image.php?d=AQDJyDKV3JDoU0-y&w=130&h=130&url=http%3A%2F%2'.
        'Fi.kinja-img.com%2Fgawker-media%2Fimage%2Fupload%2Fs--G2ekOhHZ--%2Fgobnypf78nigkkl7cwpl.png&cfs=1');
        $this->assertEqual($post->links[0]->description,
        'Today’s featured workspace looks immaculate, with its pristine white walls, simple white desk, and '
        .'wood accents. Those skylights help too.');

        //assert user network is set to Facebook, not Facebook Page
        $ud = new UserMySQLDAO();
        $user = $ud->getUserByName('Gregory Robert Dumas', 'facebook');
        $this->assertEqual($user->full_name, 'Gregory Robert Dumas');
        $this->assertEqual($user->network, 'facebook');
        $this->assertTrue($user->is_protected);
        $user = $ud->getUserByName('Matthew Fleisher', 'facebook page');
        $this->assertEqual($user, null);

        $fav_dao = new FavoritePostMySQLDAO();
        $favs = $fav_dao->getUsersWhoFavedPost('10152714332426356', 'facebook page');
        $this->assertEqual($favs[0]['user_name'], 'Peter-Sarah Crofton');
        $this->assertEqual($favs[0]['user_id'], '25913266');

        // Test Facebook paging by confirming post on second "page" was captured
        $post = $post_dao->getPost('10152712264921356', 'facebook page');
        $this->assertNotNull($post);
        $this->assertEqual($post->author_user_id, '7568536355');
    }
}
