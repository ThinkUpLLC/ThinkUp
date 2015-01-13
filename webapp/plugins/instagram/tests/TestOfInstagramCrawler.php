<?php
/**
 *
 * ThinkUp/webapp/plugins/instagram/tests/TestOfInstagramCrawler.php
 *
 * Copyright (c) 2009-2013 Dimosthenis Nikoudis
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
 * Test of InstagramCrawler
 *
 * @author Dimosthenis Nikoudis <dnna[at]dnna[dot]gr>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Dimosthenis Nikoudis
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_WEBAPP_PATH.'plugins/instagram/model/class.InstagramCrawler.php';
require_once THINKUP_WEBAPP_PATH.'plugins/instagram/model/class.InstagramAPIAccessor.php';
require_once THINKUP_WEBAPP_PATH.'plugins/instagram/tests/classes/mock.Proxy.php';

class TestOfInstagramCrawler extends ThinkUpUnitTestCase {
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
        $r = array('id'=>1, 'network_username'=>'snoopdogg', 'network_user_id'=>'494785218',
        'network_viewer_id'=>'494785218', 'last_post_id'=>'0', 'last_page_fetched_replies'=>0,
        'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'instagram',
        'last_favorite_id' => '0', 'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'2009-01-01 13:48:05', 'favorites_profile' => '0'
        );
        $this->profile1_instance = new Instance($r);

        $r = array('id'=>5, 'network_username'=>'ni_ato', 'network_user_id'=>'502993749',
        'network_viewer_id'=>'502993749', 'last_post_id'=>'0', 'last_page_fetched_replies'=>0,
        'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'instagram',
        'last_favorite_id' => '0', 'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'2009-01-01 13:48:05', 'favorites_profile' => '0'
        );
        $this->profile2_instance = new Instance($r);

        $r = array('id'=>5, 'network_username'=>'ni_ato', 'network_user_id'=>'502993749',
        'network_viewer_id'=>'502993749', 'last_post_id'=>'0', 'last_page_fetched_replies'=>0,
        'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'7', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'2014-01-01 13:48:05-0000', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'instagram',
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
        $ic = new InstagramCrawler($this->profile1_instance, 'fauxaccesstoken', 10);
        $this->assertEqual($ic->access_token, 'fauxaccesstoken');
    }

    public function testFetchUser() {
        $ic = new InstagramCrawler($this->profile1_instance, 'fauxaccesstoken', 10);
        $ic->fetchUser($this->profile1_instance->network_user_id, $this->profile1_instance->network, "Owner Status");
        $user_dao = new UserMySQLDAO();
        $user = $user_dao->getUserByName('snoopdogg', 'instagram');

        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'snoopdogg');
        $this->assertEqual($user->full_name, 'Snoop Dogg');
        $this->assertEqual($user->user_id, 494785218);
        $this->assertEqual($user->description, 'This is my bio');
        $this->assertEqual($user->url, 'http://snoopdogg.com');
        $this->assertFalse($user->is_protected);
    }

    // Test that the crawler can capture photos, replies and likes correctly for an authenticated user
    public function testFetchPostsAndRepliesForProfile1() {
        $user_dao = new UserMySQLDAO();
        $photo_dao = new PhotoMySQLDAO();
        $count_history_dao = new CountHistoryMySQLDAO();
        $favorite_dao = new FavoritePostMySQLDAO();
        $follow_dao = new FollowMySQLDAO();
        $ic = new InstagramCrawler($this->profile1_instance, 'fauxaccesstoken', 120);

        $config = Config::getInstance();
        $instagram_crawler_log = $config->getValue('log_location');
        // prepare log for reading after fetchPostsAndReplies
        $log_reader_handle = fopen($instagram_crawler_log, 'r');
        fseek($log_reader_handle, 0, SEEK_END);
        // Fetch all of our mock testing data
        $ic->fetchPostsAndReplies();

        fflush($this->logger->log);
        $log_written = stream_get_contents($log_reader_handle);
        fclose($log_reader_handle);
        $this->assertFalse(preg_match(
        '/InstagramCrawler::fetchPostsAndReplies,\d+ \| 0 Instagram posts found on page 2/', $log_written));

        // Check all of our testing data is stored in the database

        // This picture has 1 reply and no likes
        $post = $photo_dao->getPhoto('519644594447805677_494785218', 'instagram');
        $this->assertEqual($post->post_id, '519644594447805677_494785218' );
        $this->assertEqual($post->author_user_id, '494785218' );
        $this->assertEqual($post->author_username, 'snoopdogg' );
        $this->assertEqual($post->author_fullname, 'Snoop Dogg' );
        $avatar = 'http://distillery.s3.amazonaws.com/profiles/profile_1574083_75sq_1295469061.jpg';
        $this->assertEqual($post->author_avatar, $avatar);
        $this->assertEqual($post->post_text, 'Pwpw katapiesh...');
        $this->assertEqual($post->reply_count_cache, 1);
        $this->assertFalse($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 0);
        $this->assertEqual($post->pub_date, '2013-08-10 20:28:00');
        $this->assertEqual($post->network, 'instagram');
        $permalink = 'http://instagram.com/p/c2JgFlg2zt/';
        $this->assertEqual($post->permalink, $permalink);
        $this->assertEqual($post->filter, 'Valencia');
        $sru = 'http://distilleryimage5.s3.amazonaws.com/5c3132b801fb11e38a2722000a9f1925_7.jpg';
        $this->assertEqual($post->standard_resolution_url, $sru);
        $lru = 'http://distilleryimage5.s3.amazonaws.com/5c3132b801fb11e38a2722000a9f1925_6.jpg';
        $this->assertEqual($post->low_resolution_url, $lru);
        $tnu = 'http://distilleryimage5.s3.amazonaws.com/5c3132b801fb11e38a2722000a9f1925_5.jpg';
        $this->assertEqual($post->thumbnail_url, $tnu);
        // Check the reply was added
        $post = $photo_dao->getPhoto('519671854563291086', 'instagram');
        $this->assertEqual($post->post_id, '519671854563291086' );
        $this->assertEqual($post->author_user_id, '502993749' );
        $this->assertEqual($post->author_username, 'ni_ato' );
        $this->assertEqual($post->author_fullname, 'niki' );
        $avatar = 'http://images.ak.instagram.com/profiles/anonymousUser.jpg';
        $this->assertEqual($post->author_avatar, $avatar);
        $this->assertEqual($post->post_text, 'Epikinduna paixnidia');
        $this->assertFalse($post->is_protected);
        $this->assertEqual($post->pub_date, '2013-08-10 20:28:00');
        $this->assertEqual($post->network, 'instagram');
        $this->assertEqual($post->in_reply_to_user_id, '494785218');
        $this->assertEqual($post->in_reply_to_post_id, '519644594447805677_494785218');
        // Check that the author of the reply was added to the users table
        $user = $user_dao->getUserByName('ni_ato', 'instagram');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'ni_ato');
        $this->assertEqual($user->full_name, 'niki');
        $this->assertEqual($user->user_id, '502993749');
        $this->assertEqual($user->avatar, 'http://images.ak.instagram.com/profiles/anonymousUser.jpg');
        $this->assertFalse($user->is_protected);
        $this->assertEqual($user->network, 'instagram');
        // Check the second photo was added, it has no comments and two likes
        $post = $photo_dao->getPhoto('519642461157682352_494785218', 'instagram');
        $this->assertEqual($post->post_id, '519642461157682352_494785218' );
        $this->assertEqual($post->author_user_id, '494785218' );
        $this->assertEqual($post->author_username, 'afokou' );
        $this->assertEqual($post->author_fullname, 'Aggeliki Fokou' );
        $avatar = 'http://images.ak.instagram.com/profiles/anonymousUser.jpg';
        $this->assertEqual($post->author_avatar, $avatar);
        $this->assertEqual($post->post_text, 'H diaskedastiki mou arxi ;(');
        $this->assertEqual($post->reply_count_cache, 0);
        $this->assertFalse($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 2);
        $this->assertEqual($post->pub_date, '2013-08-10 20:23:00');
        $this->assertEqual($post->network, 'instagram');
        $permalink = 'http://instagram.com/p/c2JBCzg2yw/';
        $this->assertEqual($post->permalink, $permalink);
        $this->assertEqual($post->filter, 'X-Pro II');
        $sru = 'http://distilleryimage8.s3.amazonaws.com/c49be7f401fa11e3bcc122000a1fa49d_7.jpg';
        $this->assertEqual($post->standard_resolution_url, $sru);
        $lru = 'http://distilleryimage8.s3.amazonaws.com/c49be7f401fa11e3bcc122000a1fa49d_6.jpg';
        $this->assertEqual($post->low_resolution_url, $lru);
        $tnu = 'http://distilleryimage8.s3.amazonaws.com/c49be7f401fa11e3bcc122000a1fa49d_5.jpg';
        $this->assertEqual($post->thumbnail_url, $tnu);
        // Check we stored the details of the users who liked our photo (ni_ato was the second person to like it and
        // we checked he was in the DB above)
        $user = $user_dao->getUserByName('louk_as', 'instagram');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'louk_as');
        $this->assertEqual($user->full_name, 'lucas asimo');
        $this->assertEqual($user->user_id, '20065178');
        $this->assertEqual($user->avatar,
        'http://images.ak.instagram.com/profiles/profile_20065178_75sq_1335521050.jpg');
        $this->assertFalse($user->is_protected);
        // Check we stored the number of followers our owner has
        $number_of_followers = $count_history_dao->getLatestCountByNetworkUserIDAndType('494785218','instagram',
        'followers');
        $this->assertEqual($number_of_followers['count'], 3);
        // Also check the relationship is in the follows table
        $this->assertTrue($follow_dao->followExists('494785218', '6623', 'instagram'));
        $this->assertTrue($follow_dao->followExists('494785218', '29648', 'instagram'));
        $this->assertTrue($follow_dao->followExists('494785218', '13096', 'instagram'));
        // Check we stored the two likes in the favorites table
        $fav_posts = $favorite_dao->getUsersWhoFavedPost('519642461157682352_494785218', 'instagram', true);
        $this->assertEqual($fav_posts[0]['user_id'], '20065178');
        $this->assertEqual($fav_posts[1]['user_id'], '502993749');
    }

    // Check the correct exception is raised when the wrong access tokens are provided
    public function testFetchPostsAndRepliesForProfile2Error() {
        $ic = new InstagramCrawler($this->profile2_instance, 'fauxaccesstokeninvalid', 10);
        $this->expectException('Instagram\Core\ApiAuthException', 'The "access_token" provided is invalid.');
        $ic->fetchPostsAndReplies();
    }

    public function testFetchNewestPosts() {
        $user_dao = new UserMySQLDAO();
        $photo_dao = new PhotoMySQLDAO();
        $count_history_dao = new CountHistoryMySQLDAO();
        $favorite_dao = new FavoritePostMySQLDAO();
        $follow_dao = new FollowMySQLDAO();
        $ic = new InstagramCrawler($this->profile3_instance, 'fauxaccesstoken', 120);

        $config = Config::getInstance();
        $instagram_crawler_log = $config->getValue('log_location');
        // prepare log for reading after fetchPostsAndReplies.
        $log_reader_handle = fopen($instagram_crawler_log, 'r');
        fseek($log_reader_handle, 0, SEEK_END);
        //Check if newest posts are returned.
        try {
        $ic->fetchPostsAndReplies();
        } catch (Exception $e) {
            //print_r($e);
        }

        $post = $photo_dao->getPhoto('519671854563291086', 'instagram');
        $this->assertEqual($post->post_id, '519671854563291086' );
        $this->assertEqual($post->author_user_id, '502993749' );
        $this->assertEqual($post->author_username, 'ni_ato' );
        $this->assertEqual($post->author_fullname, 'niki' );
        $avatar = 'http://images.ak.instagram.com/profiles/anonymousUser.jpg';
        $this->assertEqual($post->author_avatar, $avatar);
        $this->assertEqual($post->post_text, 'Epikinduna paixnidia');
        $this->assertFalse($post->is_protected);
        $this->assertEqual($post->pub_date, '2013-08-10 20:28:00');
        $this->assertEqual($post->network, 'instagram');
        $this->assertEqual($post->in_reply_to_user_id, '494785218');
        $this->assertEqual($post->in_reply_to_post_id, '519644594447805677_494785218');
    }

    public function testFetchFriendsAfterTwoDays() {
        $plugin_dao = new PluginMySQLDAO();
        $plugin_id = $plugin_dao->getPluginId('instagram');
        $namespace = OptionDAO::PLUGIN_OPTIONS.'-'.$plugin_id;
        $option_dao = new OptionMySQLDAO();
        $ic = new InstagramCrawler($this->profile3_instance, 'fauxaccesstoken', 120);
        $ic->fetchPostsAndReplies();
        //Checks to see if date value has been inserted into table after first crawl.
        $select_insert = $option_dao->getOptionByName($namespace,'last_crawled_friends');
        $this->assertNotNull($select_insert->option_value);
        //Checks to see if date value hasn't changed after a crawl within two days of the last.
        $ic->fetchPostsAndReplies();
        $select_under_two_days = $option_dao->getOptionByName($namespace,'last_crawled_friends');
        $this->assertEqual($select_insert->option_value, $select_under_two_days->option_value);
        //Checks to see if date value has changed after a crawl 3 days after last crawl.
        $option_dao->updateOptionByName($namespace,'last_crawled_friends', '1396566000');
        $ic->fetchPostsAndReplies();
        $select_over_two_days = $option_dao->getOptionByName($namespace,'last_crawled_friends');
        $this->assertNotEqual($select_insert->option_value, $select_over_two_days->option_value);
    }
}
