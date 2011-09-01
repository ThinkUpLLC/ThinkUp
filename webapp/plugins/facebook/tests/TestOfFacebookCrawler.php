<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/TestOfFacebookCrawler.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 *
 * Test of FacebookCrawler
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 */
require_once 'tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/model/class.FacebookCrawler.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/tests/classes/mock.FacebookGraphAPIAccessor.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/tests/classes/mock.facebook.php';

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
        'network_viewer_id'=>'606837591', 'last_post_id'=>'0', 'last_page_fetched_replies'=>0, 
        'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0', 
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'', 
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'facebook',
        'last_favorite_id' => '0', 'last_unfav_page_checked' => '0', 'last_page_fetched_favorites' => '0',
        'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'01-01-2009', 'favorites_profile' => '0'
        );
        $this->instance = new Instance($r);

        $r2 = array('id'=>2, 'network_username'=>'Mark Linford', 'network_user_id'=>'729597743',
        'network_viewer_id'=>'729597743', 'last_post_id'=>'0', 'last_page_fetched_replies'=>0, 
        'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0', 
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'', 
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'facebook',
        'last_favorite_id' => '0', 'last_unfav_page_checked' => '0', 'last_page_fetched_favorites' => '0',
        'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'01-01-2009', 'favorites_profile' => '0'
        );
        $this->instance2 = new Instance($r2);

    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    public function testConstructor() {
        $fbc = new FacebookCrawler($this->instance, 'fauxaccesstoken');
        $this->assertEqual($fbc->access_token, 'fauxaccesstoken');
    }

    public function testFetchInstanceUserInfo() {
        $fbc = new FacebookCrawler($this->instance, 'fauxaccesstoken');
        $fbc->fetchInstanceUserInfo();
        $user_dao = new UserMySQLDAO();
        $user = $user_dao->getUserByName('Gina Trapani', 'facebook');

        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'Gina Trapani');
        $this->assertEqual($user->full_name, 'Gina Trapani');
        $this->assertEqual($user->user_id, 606837591);
        $this->assertEqual($user->location, "San Diego, California");
        $this->assertEqual($user->description,
        'Blogger and software developer. Project Director at Expert Labs. Co-host of This Week in Google.');
        $this->assertEqual($user->url, '');
        $this->assertTrue($user->is_protected);
    }

    public function testFetchPostsAndReplies() {
        $fbc = new FacebookCrawler($this->instance, 'fauxaccesstoken');

        $fbc->fetchPostsAndReplies($this->instance->network_user_id, false);

        $post_dao = new PostMySQLDAO();
        $post = $post_dao->getPost('158944054123704', 'facebook');
        $this->assertEqual($post->post_text, 'that movie made me want to build things');
        $this->assertEqual($post->reply_count_cache, 0);
        $this->assertTrue($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 0);
        $this->assertEqual($post->location, 'San Diego, California');

        $post = $post_dao->getPost('153956564638648', 'facebook');
        $this->assertEqual($post->post_text,
        'Britney Glee episode tonight. I may explode into a million pieces, splattered all over my living room walls.');
        $this->assertEqual($post->reply_count_cache, 19);
        $this->assertTrue($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 3);
        $this->assertEqual($post->location, 'San Diego, California');

        $post = $post_dao->getPost('1546020', 'facebook');
        $this->assertPattern('/not the target demographic/', $post->post_text);
        $this->assertEqual($post->reply_count_cache, 0);
        $this->assertEqual($post->in_reply_to_post_id, '153956564638648');
        $this->assertTrue($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 0);
        $this->assertEqual($post->location, 'La Mesa, California');

        $user_dao = new UserMySQLDAO();
        $user = $user_dao->getUserByName('Gina Trapani', 'facebook');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'Gina Trapani');
        $this->assertEqual($user->full_name, 'Gina Trapani');
        $this->assertEqual($user->user_id, '606837591');
        $this->assertEqual($user->avatar, 'https://graph.facebook.com/606837591/picture');
        $this->assertTrue($user->is_protected);
        $this->assertEqual($user->location, 'San Diego, California');
//sleep(1000);
        $user = $user_dao->getUserByName('Mitch Wagner', 'facebook');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->user_id, '697015835');
        $this->assertEqual($user->avatar, 'https://graph.facebook.com/697015835/picture');
        $this->assertTrue($user->is_protected);
        $this->assertEqual($user->location, 'La Mesa, California');

        $user = $user_dao->getUserByName('Jeffrey McManus', 'facebook');
        $this->assertTrue(isset($user));
        $this->assertEqual($user->user_id, '691270740');
        $this->assertEqual($user->avatar, 'https://graph.facebook.com/691270740/picture');
        $this->assertTrue($user->is_protected);
        $this->assertEqual($user->location, '');

        //Test post with a link to a video
        $fbc2 = new FacebookCrawler($this->instance2, 'fauxaccesstoken');
        $fbc2->fetchPostsAndReplies($this->instance2->network_user_id, false);
        $post = $post_dao->getPost('10150328374252744', 'facebook');
        $this->assertEqual($post->post_text, '');
        $this->assertNotNull($post->link);
        $this->assertEqual($post->link->url,
        'http://www.youtube.com/v/DC1g_Aq3dUc?feature=autoshare&version=3&autohide=1&autoplay=1');
        $this->assertEqual($post->link->expanded_url,
        'http://www.youtube.com/v/DC1g_Aq3dUc?feature=autoshare&version=3&autohide=1&autoplay=1');
        $this->assertEqual($post->link->caption, 'Liked on www.youtube.com');
        $this->assertEqual($post->link->description,
        'A fan made trailer for the Warner Bros. production of Superman Returns. Fan trailer produced and edited by '.
        'Julian Francis Adderley.');
        $this->assertEqual($post->link->title, 'Superman Restored (Theatrical Trailer)');
        $this->assertEqual($post->link->network, 'facebook');
		
		// Test Facebook paging by confirming post on second "page"
		$post = $post_dao->getPost('10150357566827744', 'facebook');
		$this->assertNotNull($post);
		$this->assertEqual($post->author_user_id, '729597743');
    }

    public function testFetchPageStream() {
        $fbc = new FacebookCrawler($this->instance, 'fauxaccesstoken');

        $fbc->fetchPostsAndReplies('7568536355', true);

        $post_dao = new PostMySQLDAO();
        $post = $post_dao->getPost('437900891355', 'facebook page');
        $this->assertEqual($post->post_text, 'Top 10 iOS Jailbreak Hacks');
        $this->assertFalse($post->is_protected);
        $this->assertEqual($post->reply_count_cache, 8);

        //test link with image
        $this->assertNotNull($post->link);
        $this->assertEqual($post->link->url,
        'http://lifehacker.com/5653429/top-10-ios-jailbreak-hacks');
        $this->assertEqual($post->link->expanded_url,
        'http://lifehacker.com/5653429/top-10-ios-jailbreak-hacks');
        $this->assertEqual($post->link->image_src,
        'http://platform.ak.fbcdn.net/www/app_full_proxy.php?app=45439413586&v=1&size=z&cksum=7de062ac249fe7caef80f66'.
        'f49a38818&src=http%3A%2F%2Fcache-02.gawkerassets.com%2Fassets%2Fimages%2F17%2F2010%2F10%2F160x120_jailbreak-'.
        'top-10.jpg');
        $this->assertEqual($post->link->description,
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
    }

    public function testPostReplyPaging() {
        $fbc = new FacebookCrawler($this->instance, 'fauxaccesstoken');

        $fbc->fetchPostsAndReplies('133954286636768', true);
        $post_dao = new PostMySQLDAO();
        $post = $post_dao->getPost('144568048938151', 'facebook page');
        $this->assertEqual($post->reply_count_cache, 70);
    }
}
