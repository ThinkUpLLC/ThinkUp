<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/TestOfFacebookCrawler.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Dwi Widiastuti
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Dwi Widiastuti
*/
if (!isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/model/class.FacebookCrawler.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/tests/classes/mock.facebook.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/facebook/facebook.php';


class TestOfFacebookCrawler extends ThinkUpUnitTestCase {
    var $fb;
    var $instance;
    var $logger;

    public function __construct() {
        $this->UnitTestCase('FacebookCrawler test');
    }

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        global $THINKUP_CFG;
        $r = array('id'=>1, 'network_username'=>'Penelope Caridad', 'network_user_id'=>'606837591',
        'network_viewer_id'=>'606837591', 'last_status_id'=>'0', 'last_page_fetched_replies'=>0, 
        'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0', 
        'total_follows_in_system'=>'0', 'total_users_in_system'=>'0', 'is_archive_loaded_replies'=>'0', 
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'', 
        'api_calls_to_leave_unmade_per_minute'=>2, 'avg_replies_per_day'=>'2', 'is_public'=>'0', 'is_active'=>'0', 
        'network'=>'facebook');
        $this->instance = new Instance($r);

        $this->fb = new Facebook('dummykey', 'dummysecret');
        $this->fb->api_client = new MockFacebookRestClient();
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
    }

    public function testConstructor() {
        $fbc = new FacebookCrawler($this->instance, $this->fb);

        $this->assertTrue($fbc != null);
    }

    public function testFetchInstanceUserInfo() {
        $fbc = new FacebookCrawler($this->instance, $this->fb);

        $session_key = 'adsfasdfasdfasdf';
        $fbc->fetchInstanceUserInfo($this->instance->network_user_id, $session_key);
        $this->assertTrue(isset($fbc->owner_object));
        $this->assertEqual($fbc->owner_object->user_id, 606837591);

    }

    public function testFetchUserStreamWithTwoPostsNoComments() {
        $fbc = new FacebookCrawler($this->instance, $this->fb);

        $session_key = 'asdfasdfasdfafsd';
        $fbc->fetchUserPostsAndReplies($this->instance->network_user_id, $session_key);

        $pd = DAOFactory::getDAO('PostDAO');
        $this->assertTrue($pd->isPostInDB('108956622464235', 'facebook'));
        $this->assertTrue($pd->isPostInDB('107266209295210', 'facebook'));
    }

    public function testFetchUserStreamWithTwoPostsAndOneComment() {

        $this->instance->network_user_id = '6068375911';
        $fbc = new FacebookCrawler($this->instance, $this->fb);

        $session_key = 'adfasdfasdfasdf';
        $fbc->fetchUserPostsAndReplies($this->instance->network_user_id, $session_key);

        $pd = DAOFactory::getDAO('PostDAO');
        $p = $pd->getPost('108956622464235', 'facebook');
        $this->assertTrue($p->reply_count_cache == 1);

        $p = $pd->getPost('107266209295210', 'facebook');
        $this->assertTrue($p->reply_count_cache == 0);
    }


    public function testFetchUserPagesThatUserIsaFanOf() {

        $this->instance->network_user_id = '606837591';
        $fbc = new FacebookCrawler($this->instance, $this->fb);

        $session_key = 'asdfasdfasdfa';
        $pages = $fbc->fetchPagesUserIsFanOf($this->instance->network_user_id, $session_key);
        //print_r($pages);

        $this->assertEqual(sizeof($pages), 43);
        $this->assertEqual($pages[29]['page_id'], '63811549237');
        $this->assertEqual($pages[29]['name'], 'The White House');
        $this->assertEqual($pages[29]['page_url'], 'http://www.facebook.com/WhiteHouse');

        $this->assertEqual($pages[15]['page_id'], '110253595679921');
        $this->assertEqual($pages[15]['name'], 'The Shawshank Redemption (1994)');
        $this->assertEqual($pages[15]['page_url'], 'http://www.imdb.com/title/tt0111161/');
    }

    public function testFetchPageStream() {
        $this->instance->network_user_id = '606837591';
        $fbc = new FacebookCrawler($this->instance, $this->fb);

        $session_key = 'asdfasdfasdfadf';
        $page_id = '63811549237';
        $fbc->fetchPagePostsAndReplies($page_id, $this->instance->network_user_id, $session_key);

        $pd = DAOFactory::getDAO('PostDAO');
        $p = $pd->getPost('125634574117714', 'facebook page');
        $this->assertEqual($p->post_text,
        "Thanks for checking out the West Wing Week, your guide to everything that's happening at ".
        "1600 Pennsylvania Ave.");
    }
}
