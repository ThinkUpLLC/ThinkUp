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
$version = explode('.', PHP_VERSION); //dont run redis or instagram test for php less than 5.3
if ($version[0] >= 5 && $version[1] >= 3) { //only run Instagram tests if PHP 5.3
	require_once THINKUP_WEBAPP_PATH.'plugins/instagram/model/class.InstagramCrawler.php';
	require_once THINKUP_WEBAPP_PATH.'plugins/instagram/model/class.InstagramGraphAPIAccessor.php';
	require_once THINKUP_WEBAPP_PATH.'plugins/instagram/tests/classes/mock.Proxy.php';
} else {
	class TestOfInstagramCrawler extends ThinkUpUnitTestCase {
		public function testUnsupportedPHP() {}
	}
    return;
}

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
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    public function testConstructor() {
        $fbc = new InstagramCrawler($this->profile1_instance, 'fauxaccesstoken', 10);
        $this->assertEqual($fbc->access_token, 'fauxaccesstoken');
    }

    public function testFetchUser() {
        $fbc = new InstagramCrawler($this->profile1_instance, 'fauxaccesstoken', 10);
        $fbc->fetchUser($this->profile1_instance->network_user_id, $this->profile1_instance->network, "Owner Status");
        $user_dao = new UserMySQLDAO();
        $user = $user_dao->getUserByName('snoopdogg', 'instagram');

        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'snoopdogg');
        $this->assertEqual($user->full_name, 'Snoop Dogg');
        $this->assertEqual($user->user_id, 494785218);
        $this->assertEqual($user->description, 'This is my bio');
        $this->assertEqual($user->url, 'http://snoopdogg.com');
        $this->assertTrue($user->is_protected);
    }

    public function testFetchPostsAndRepliesForProfile1() {
        $fbc = new InstagramCrawler($this->profile1_instance, 'fauxaccesstoken', 120);

        $config = Config::getInstance();
        $instagram_crawler_log = $config->getValue('log_location');
        // prepare log for reading after fetchPostsAndReplies
        $log_reader_handle = fopen($instagram_crawler_log, 'r');
        fseek($log_reader_handle, 0, SEEK_END);

        $fbc->fetchPostsAndReplies();

        fflush($this->logger->log);
        $log_written = stream_get_contents($log_reader_handle);
        fclose($log_reader_handle);
        $this->assertFalse(preg_match('/InstagramCrawler::fetchPostsAndReplies,\d+ \| 0 Instagram posts found on page 2/',
        $log_written));

        $post_dao = new PostMySQLDAO();
        $post = $post_dao->getPost('519644594447805677_494785218', 'instagram');
        $this->assertEqual($post->post_text, 'Pwpw katapiesh...');
        $this->assertEqual($post->reply_count_cache, 1);
        $this->assertTrue($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 0);

        $post = $post_dao->getPost('519642461157682352_494785218', 'instagram');
        $this->assertEqual($post->post_text, 'H diaskedastiki mou arxi ;(');
        $this->assertEqual($post->reply_count_cache, 0);
        $this->assertTrue($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 2);

        // comment
        $post = $post_dao->getPost('519671854563291086', 'instagram');
        $this->assertEqual($post->post_text, 'Epikinduna paixnidia');
        $this->assertEqual($post->author_user_id, '502993749');
        $this->assertEqual($post->in_reply_to_user_id, $this->profile1_instance->network_user_id);

        $user_dao = new UserMySQLDAO();
        $user = $user_dao->getUserByName('snoopdogg', 'instagram');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'snoopdogg');
        $this->assertEqual($user->full_name, 'Snoop Dogg');
        $this->assertEqual($user->user_id, '494785218');
        $this->assertEqual($user->avatar, 'http://distillery.s3.amazonaws.com/profiles/profile_1574083_75sq_1295469061.jpg');
        $this->assertTrue($user->is_protected);
        //sleep(1000);
        $user = $user_dao->getUserByName('ni_ato', 'instagram');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->user_id, '502993749');
        $this->assertEqual($user->avatar, 'http://images.ak.instagram.com/profiles/anonymousUser.jpg');
        $this->assertTrue($user->is_protected);

        $user = $user_dao->getUserByName('louk_as', 'instagram');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->user_id, '20065178');
        $this->assertEqual($user->avatar, 'http://images.ak.instagram.com/profiles/profile_20065178_75sq_1335521050.jpg');
        $this->assertTrue($user->is_protected);
    }

    public function testFetchPostsAndRepliesForProfile2Error() {
        $fbc = new InstagramCrawler($this->profile2_instance, 'fauxaccesstokeninvalid', 10);

        $this->expectException('Instagram\Core\ApiAuthException', 'The "access_token" provided is invalid.');

        $fbc->fetchPostsAndReplies();
    }
}
