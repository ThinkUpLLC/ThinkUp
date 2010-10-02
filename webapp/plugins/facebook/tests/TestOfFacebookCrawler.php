<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/TestOfFacebookCrawler.php
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
 *
 *
 * Test of FacebookCrawler
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 */
if (!isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/model/class.FacebookCrawler.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/tests/classes/mock.FacebookGraphAPIAccessor.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/facebook/facebook.php';


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

    public function __construct() {
        $this->UnitTestCase('FacebookCrawler test');
    }

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $r = array('id'=>1, 'network_username'=>'Gina Trapani', 'network_user_id'=>'606837591',
        'network_viewer_id'=>'606837591', 'last_status_id'=>'0', 'last_page_fetched_replies'=>0, 
        'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 
        'total_follows_in_system'=>'0', 'total_users_in_system'=>'0', 'is_archive_loaded_replies'=>'0', 
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'', 
        'api_calls_to_leave_unmade_per_minute'=>2, 'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 
        'network'=>'facebook');
        $this->instance = new Instance($r);
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
    }

    public function testFetchUserPostsAndReplies() {
        $fbc = new FacebookCrawler($this->instance, 'fauxaccesstoken');

        $fbc->fetchUserPostsAndReplies($this->instance->network_user_id);

        $pd = new PostMySQLDAO();
        $post = $pd->getPost('158944054123704', 'facebook');
        $this->assertEqual($post->post_text, 'that movie made me want to build things');
        $this->assertEqual($post->reply_count_cache, 0);

        $post = $pd->getPost('153956564638648', 'facebook');
        $this->assertEqual($post->post_text,
        'Britney Glee episode tonight. I may explode into a million pieces, splattered all over my living room walls.');
        $this->assertEqual($post->reply_count_cache, 19);

        $post = $pd->getPost('1546020', 'facebook');
        $this->assertPattern('/not the target demographic/', $post->post_text);
        $this->assertEqual($post->reply_count_cache, 0);
        $this->assertEqual($post->in_reply_to_post_id, '153956564638648');
    }

    public function testFetchPageStream() {
        $fbc = new FacebookCrawler($this->instance, 'fauxaccesstoken');

        $fbc->fetchPagePostsAndReplies(7568536355);

        $pd = new PostMySQLDAO();
        $post = $pd->getPost('437900891355', 'facebook page');
        $this->assertEqual($post->post_text, 'Top 10 iOS Jailbreak Hacks');
        $this->assertEqual($post->reply_count_cache, 8);
    }
}
