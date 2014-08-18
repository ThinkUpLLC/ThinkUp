<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/TestOfFacebookCrawler.php
 *
 * Copyright (c) 2009-2014 Gina Trapani
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
 * @copyright 2009-2014 Gina Trapani
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/model/class.FacebookCrawler.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/model/class.FacebookInstance.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/model/class.FacebookInstanceMySQLDAO.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/tests/classes/mock.FacebookGraphAPIAccessor.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/tests/classes/mock.facebook.php';

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

        $r = array('id'=>2, 'network_username'=>'Mark Linford', 'network_user_id'=>'729597743',
        'network_viewer_id'=>'729597743', 'last_post_id'=>'0',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'facebook',
        'last_favorite_id' => '0', 'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'2009-01-01 13:48:05', 'favorites_profile' => '0'
        );
        $this->profile2_instance = new Instance($r);

        $r = array('id'=>3, 'network_username'=>'Mark Linford', 'network_user_id'=>'7568536355',
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

        $r = array('id'=>4, 'network_username'=>'Mark Linford', 'network_user_id'=>'133954286636768',
        'network_viewer_id'=>'729597743', 'last_post_id'=>'0',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'facebook page',
        'last_favorite_id' => '0', 'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'2009-01-01 13:48:05', 'favorites_profile' => '0'
        );
        $this->page2_instance = new Instance($r);

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

        $r['id'] = 6;
        $r['network_username'] = 'Chris Moyer';
        $r['network_user_id'] = '501771984';
        $r['network_viewer_id'] = '501771984';
        $this->profile5_instance = new Instance($r);
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    public function testConstructor() {
        $fbc = new FacebookCrawler($this->profile1_instance, 'fauxaccesstoken', 10);
        $this->assertEqual($fbc->access_token, 'fauxaccesstoken');
    }

    public function testFetchUser() {
        $fbc = new FacebookCrawler($this->profile1_instance, 'fauxaccesstoken', 10);
        $fbc->fetchUser($this->profile1_instance->network_user_id, $this->profile1_instance->network, "Owner Status");
        $user_dao = new UserMySQLDAO();
        $user = $user_dao->getUserByName('Gina Trapani', 'facebook');

        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'Gina Trapani');
        $this->assertEqual($user->full_name, 'Gina Trapani');
        $this->assertEqual($user->user_id, 606837591);
        $this->assertEqual($user->gender, "female");
        $this->assertEqual($user->location, "San Diego, California");
        $this->assertEqual($user->description,
        'Blogger and software developer. Project Director at Expert Labs. Co-host of This Week in Google.');
        $this->assertEqual($user->url, '');
        $this->assertTrue($user->is_protected);
        $this->assertNotNull($user->joined);
    }

    public function testFetchPostsAndRepliesForProfile1() {
        $fbc = new FacebookCrawler($this->profile1_instance, 'fauxaccesstoken', 120);

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
        $post = $post_dao->getPost('158944054123704', 'facebook');
        $this->assertEqual($post->post_text, 'that movie made me want to build things');
        $this->assertEqual($post->reply_count_cache, 0);
        $this->assertTrue($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 0);
        $this->assertEqual($post->location, 'San Diego, California');

        $post = $post_dao->getPost('10151848164181985', 'facebook');
        $this->assertEqual($post->post_text,
        'Britney Glee episode tonight. I may explode into a million pieces, splattered all over my living room walls.');
        $this->assertEqual($post->reply_count_cache, 31);
        $this->assertTrue($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 3);
        $this->assertEqual($post->location, 'San Diego, California');

        // wall post
        $post = $post_dao->getPost('10150414865507812', 'facebook');
        $this->assertEqual($post->author_user_id, '503315820');
        $this->assertEqual($post->location, 'Portland, Oregon');
        $this->assertEqual($post->in_reply_to_user_id, $this->profile1_instance->network_user_id);

        $post = $post_dao->getPost('29187893', 'facebook');
        $this->assertPattern('/to myself./', $post->post_text);
        $this->assertEqual($post->reply_count_cache, 0);
        $this->assertEqual($post->in_reply_to_post_id, '10151848164181985');
        $this->assertTrue($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 0);

        $user_dao = new UserMySQLDAO();
        $user = $user_dao->getUserByName('Gina Trapani', 'facebook');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'Gina Trapani');
        $this->assertEqual($user->full_name, 'Gina Trapani');
        $this->assertEqual($user->user_id, '606837591');
        $this->assertEqual($user->avatar, 'https://graph.facebook.com/606837591/picture');
        $this->assertTrue($user->is_protected);
        $this->assertEqual($user->gender, 'female');
        $this->assertEqual($user->location, 'San Diego, California');
        //sleep(1000);
        $user = $user_dao->getUserByName('Mitch Wagner', 'facebook');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->user_id, '697015835');
        $this->assertEqual($user->avatar, 'https://graph.facebook.com/697015835/picture');
        $this->assertTrue($user->is_protected);
        $this->assertEqual($user->gender, 'male');
        $this->assertEqual($user->location, 'La Mesa, California');

        $user = $user_dao->getUserByName('Jeffrey McManus', 'facebook');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->user_id, '691270740');
        $this->assertEqual($user->avatar, 'https://graph.facebook.com/691270740/picture');
        $this->assertTrue($user->is_protected);
        $this->assertEqual($user->gender, 'male');
        $this->assertEqual($user->location, '');
    }

    public function testFetchPostsAndRepliesForProfile2() {
        //Test post with a link to a video
        $fbc2 = new FacebookCrawler($this->profile2_instance, 'fauxaccesstoken', 10);

        $fbc2->fetchPostsAndReplies();

        $post_dao = new PostMySQLDAO();
        $user_dao = new UserMySQLDAO();

        $post = $post_dao->getPost('10150328374252744', 'facebook');
        $this->assertEqual($post->post_text, '');
        $this->assertEqual(sizeof($post->links), 1);
        $this->assertEqual($post->links[0]->url,
        'http://www.youtube.com/v/DC1g_Aq3dUc?feature=autoshare&version=3&autohide=1&autoplay=1');
        $this->assertEqual($post->links[0]->expanded_url,
        '');
        $this->assertEqual($post->links[0]->caption, 'Liked on www.youtube.com');
        $this->assertEqual($post->links[0]->description,
        'A fan made trailer for the Warner Bros. production of Superman Returns. Fan trailer produced and edited by '.
        'Julian Francis Adderley.');
        $this->assertEqual($post->links[0]->title, 'Superman Restored (Theatrical Trailer)');
        $this->assertEqual($post->links[0]->post_key, 1);

        // Test Facebook paging by confirming post on second "page"
        $post = $post_dao->getPost('10150357566827744', 'facebook');
        $this->assertNotNull($post);
        $this->assertEqual($post->author_user_id, '729597743');

        // Test Facebook subscribers. This user only exists in testing as a subscriber
        $user = $user_dao->getUserByName('Poppy Linford', 'facebook');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->user_id, '682523675');
        $this->assertTrue($user->is_verified);
        // Test follow is set
        $follow_dao = new FollowMySQLDAO();
        $this->assertTrue($follow_dao->followExists('729597743', '682523675', 'facebook'));

        // Test FollowerCount is set
        $sql = "SELECT * FROM ".$this->table_prefix.
        "count_history WHERE network='facebook' AND network_user_id='729597743' AND type='followers';";

        $stmt = CountHistoryMySQLDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();

        $this->assertEqual($data[0]['count'], 2);
    }

    public function testFetchPostsAndRepliesForProfile3Error() {
        $fbc = new FacebookCrawler($this->profile3_instance, 'fauxaccesstoken', 10);

        $this->expectException('APIOAuthException',
        'Error validating access token: Session has expired at unix time SOME_TIME. The current unix time is '.
        'SOME_TIME.');

        $fbc->fetchPostsAndReplies();
    }

    public function testFetchPostsAndRepliesForPage() {
        $fbc = new FacebookCrawler($this->page1_instance, 'fauxaccesstoken', 10);

        $fbc->fetchPostsAndReplies();

        $post_dao = new PostMySQLDAO();
        $post = $post_dao->getPost('437900891355', 'facebook page');
        $this->assertEqual($post->post_text, 'Top 10 iOS Jailbreak Hacks');
        $this->assertFalse($post->is_protected);
        $this->assertEqual($post->reply_count_cache, 45);

        //test link with image
        $this->assertEqual(sizeof($post->links), 1);
        $this->assertEqual($post->links[0]->url,
        'http://lifehacker.com/5653429/top-10-ios-jailbreak-hacks');
        $this->assertEqual($post->links[0]->expanded_url,
        '');
        $this->assertEqual($post->links[0]->image_src,
        'http://platform.ak.fbcdn.net/www/app_full_proxy.php?app=45439413586&v=1&size=z&cksum=7de062ac249fe7caef80f66'.
        'f49a38818&src=http%3A%2F%2Fcache-02.gawkerassets.com%2Fassets%2Fimages%2F17%2F2010%2F10%2F160x120_jailbreak-'.
        'top-10.jpg');
        $this->assertEqual($post->links[0]->description,
        'If you purchased an iOS device, you also signed up for its many limitations. Jailbreaking can put you back '.
        'in control. Here are ten great jailbreak hacks to help you customize and better utilize your iOS device...');

        //assert user network is set to Facebook, not Facebook Page
        $ud = new UserMySQLDAO();
        $user = $ud->getUserByName('Matthew Fleisher', 'facebook');
        $this->assertEqual($user->full_name, 'Matthew Fleisher');
        $this->assertEqual($user->network, 'facebook');
        $this->assertTrue($user->is_protected);

        $user = $ud->getUserByName('Matthew Fleisher', 'facebook page');
        $this->assertEqual($user, null);

        $fav_dao = new FavoritePostMySQLDAO();
        $favs = $fav_dao->getUsersWhoFavedPost('437894121355', 'facebook page');
        $this->assertEqual($favs[0]['user_name'], 'Tigger Pike');
        $this->assertEqual($favs[0]['user_id'], '641265671');

        // Test Facebook paging by confirming post on second "page" was captured
        $post = $post_dao->getPost('437660146355', 'facebook page');
        $this->assertNotNull($post);
        $this->assertEqual($post->author_user_id, '7568536355');

        // Test follower count is set
        $sql = "SELECT * FROM ".$this->table_prefix.
        "count_history WHERE network='facebook page' AND network_user_id='7568536355' AND type='followers';";

        $stmt = CountHistoryMySQLDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();
        $this->assertEqual($data[0]['count'], 307758);
    }

    public function testPostReplyPaging() {
        $fbc = new FacebookCrawler($this->page2_instance, 'fauxaccesstoken', 10);

        $fbc->fetchPostsAndReplies('133954286636768', 'facebook page');
        $post_dao = new PostMySQLDAO();
        $post = $post_dao->getPost('775180192497884', 'facebook page');
        $this->assertEqual($post->reply_count_cache, 51);
    }

    public function testPaginatedPostLikes() {
        $fbc = new FacebookCrawler($this->profile5_instance, 'fauxaccesstoken', 10);

        $fbc->fetchPostsAndReplies('501771984', 'facebook');
        $post_dao = new PostMySQLDAO();
        $post = $post_dao->getPost('10151734003261985', 'facebook');
        $this->assertEqual($post->favlike_count_cache, 27);
    }
}
