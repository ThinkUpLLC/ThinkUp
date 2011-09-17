<?php
/**
 *
 * ThinkUp/webapp/plugins/googleplus/tests/TestOfFacebookCrawler.php
 *
 * Copyright (c) 2011 Henri Watson
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
 * Test of GooglePlusCrawler
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 Henri Watson
 */
require_once 'tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/googleplus/model/class.GooglePlusCrawler.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/googleplus/tests/classes/mock.GooglePlusAPIAccessor.php';
//require_once THINKUP_ROOT_PATH.'webapp/plugins/googleplus/tests/classes/mock.googleplus.php';

class TestOfGooglePlusCrawler extends ThinkUpUnitTestCase {
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
        $r = array('id'=>1, 'network_username'=>'Gina Trapani', 'network_user_id'=>'113612142759476883204',
        'network_viewer_id'=>'113612142759476883204', 'last_post_id'=>'0', 'last_page_fetched_replies'=>0, 
        'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0', 
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'', 
        'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'googleplus',
        'last_favorite_id' => '0', 'last_unfav_page_checked' => '0', 'last_page_fetched_favorites' => '0',
        'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>1, 'posts_per_week'=>1, 'percentage_replies'=>50, 'percentage_links'=>50,
        'earliest_post_in_system'=>'01-01-2009', 'favorites_profile' => '0'
        );
        $this->profile1_instance = new Instance($r);
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    public function testConstructor() {
        $gpc = new GooglePlusCrawler($this->profile1_instance, 'fauxaccesstoken', 10);
        $this->assertEqual($gpc->access_token, 'fauxaccesstoken');
    }

    public function testFetchUser() {
        $gpc = new GooglePlusCrawler($this->profile1_instance, 'fauxaccesstoken', 10);
        $gpc->fetchUser($this->profile1_instance->network_user_id, $this->profile1_instance->network, true);
        $user_dao = new UserMySQLDAO();
        $user = $user_dao->getUserByName('Gina Trapani', 'google+');

        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'Gina Trapani');
        $this->assertEqual($user->full_name, 'Gina Trapani');
        $this->assertEqual($user->user_id, 1136121427);
        $this->assertEqual($user->location, "San Diego");
        $this->assertEqual($user->description,
        'ThinkUp lead developer, This Week in Google co-host, Todo.txt apps creator, founding editor of Lifehacker');
        $this->assertEqual($user->url, '');
        $this->assertTrue($user->is_protected);
    }
}
