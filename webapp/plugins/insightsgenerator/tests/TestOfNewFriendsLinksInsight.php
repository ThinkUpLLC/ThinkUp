<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfNewFriendLinks.php
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
 * TestOfNewFriendsLinks
 *
 * Tests the new friends links Insight.
 *
 * Copyright (c) Gareth Brady
 *
 * @author Gareth Brady gareth.brady92@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Gareth Brady
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/newfriendlinks.php';

class TestOfNewFriendsLinksInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new NewFriendLinksInsight();
        $this->assertIsA($insight_plugin, 'NewFriendLinksInsight');
    }

    public function testNoNewFriendsWithLinks() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>20, 'user_name'=>'janesmith',
        'full_name'=>'Jane Smith', 'avatar'=>'avatar.jpg','url'=>'', 'is_protected'=>0, 'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>21, 'user_name'=>'joesmith',
        'full_name'=>'Joe Smith', 'avatar'=>'avatar.jpg','url'=>'', 'is_protected'=>0, 'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'20',
        'follower_id'=>'21', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'twitter',
        'debug_api_call'=>''));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'21',
        'follower_id'=>'20', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'twitter',
        'debug_api_call'=>''));

        $instance = new Instance();
        $instance->id = 20;
        $instance->network_user_id = 20;
        $instance->network_username = 'jamesmith';
        $instance->network = 'twitter';
        $insight_plugin = new NewFriendLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('new_friend_links', $instance->id, $today); 
        $this->assertNull($result);

    }

    public function testOneUserWithLink() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>20, 'user_name'=>'janesmith',
        'full_name'=>'Jane Smith', 'avatar'=>'avatar.jpg','url'=>'', 'is_protected'=>0, 'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>21, 'user_name'=>'joesmith',
        'full_name'=>'Joe Smith','avatar'=>'avatar.jpg','url'=>'www.example.com','is_protected'=>0,'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'20',
        'follower_id'=>'21', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'twitter',
        'debug_api_call'=>''));

        $instance = new Instance();
        $instance->id = 20;
        $instance->network_user_id = 20;
        $instance->network_username = 'jamesmith';
        $instance->network = 'twitter';
        $insight_plugin = new NewFriendLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('new_friend_links', $instance->id, $today);
        $this->assertEqual($result->header_image, 'avatar.jpg');
        $this->assertEqual($result->headline, "Did you see @joesmith's website?");
        $this->assertEqual($result->text, "This link was in @joesmith's bio.");
        $this->assertNotEqual(false, strpos($result->related_data, 'joesmith'));
        // $this->debug($this->getRenderedInsightInHTML($result));
        // $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFolloweesWithLinks() {
        TimeHelper::setTime(1);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>20, 'user_name'=>'janesmith',
        'full_name'=>'Jane Smith', 'avatar'=>'avatar.jpg','url'=>'', 'is_protected'=>0, 'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>21, 'user_name'=>'joesmith',
        'full_name'=>'Joe Smith','avatar'=>'avatar.jpg','url'=>'www.example.com','is_protected'=>0,'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>22, 'user_name'=>'joesmithie',
        'full_name'=>'Joe Smith','avatar'=>'avatar.jpg','url'=>'www.example.com','is_protected'=>0,'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>23, 'user_name'=>'joesmithy',
        'full_name'=>'Joe Smith','avatar'=>'avatar.jpg','url'=>'www.example.com','is_protected'=>0,'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>24, 'user_name'=>'joesmither',
        'full_name'=>'Joe Smith','avatar'=>'avatar.jpg','url'=>'www.example.com','is_protected'=>0,'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'21',
        'follower_id'=>'20', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'twitter',
        'debug_api_call'=>''));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'22',
        'follower_id'=>'20', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'twitter',
        'debug_api_call'=>''));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'23',
        'follower_id'=>'20', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'twitter',
        'debug_api_call'=>''));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'24',
        'follower_id'=>'20', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'twitter',
        'debug_api_call'=>''));

        $instance = new Instance();
        $instance->id = 20;
        $instance->network_user_id = 20;
        $instance->network_username = 'janesmith';
        $instance->network = 'twitter';
        $insight_plugin = new NewFriendLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('new_friend_links', $instance->id, $today);
        $this->assertEqual($result->headline, "Find out more about the people @janesmith followed this week.");
        $this->assertEqual($result->text,"The people @janesmith followed this week have these links in their bios.");
        $this->assertNotEqual(false, strpos($result->related_data, 'joesmith'));
        $this->assertNotEqual(false, strpos($result->related_data, 'joesmithie'));
        $this->assertNotEqual(false, strpos($result->related_data, 'joesmithy'));
        $this->assertNotEqual(false, strpos($result->related_data, 'joesmither'));
        // $this->debug($this->getRenderedInsightInHTML($result));
        // $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFollowersWithLinks() {
        TimeHelper::setTime(2);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>20, 'user_name'=>'janesmith',
        'full_name'=>'Jane Smith', 'avatar'=>'avatar.jpg','url'=>'', 'is_protected'=>0, 'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>21, 'user_name'=>'joesmith',
        'full_name'=>'Joe Smith','avatar'=>'avatar.jpg','url'=>'www.example.com','is_protected'=>0,'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>22, 'user_name'=>'joesmithie',
        'full_name'=>'Joe Smith','avatar'=>'avatar.jpg','url'=>'www.example.com','is_protected'=>0,'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>23, 'user_name'=>'joesmithy',
        'full_name'=>'Joe Smith','avatar'=>'avatar.jpg','url'=>'www.example.com','is_protected'=>0,'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>24, 'user_name'=>'joesmither',
        'full_name'=>'Joe Smith','avatar'=>'avatar.jpg','url'=>'www.example.com','is_protected'=>0,'follower_count'=>70,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'20',
        'follower_id'=>'21', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'twitter',
        'debug_api_call'=>''));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'20',
        'follower_id'=>'22', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'twitter',
        'debug_api_call'=>''));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'20',
        'follower_id'=>'23', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'twitter',
        'debug_api_call'=>''));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'20',
        'follower_id'=>'24', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'twitter',
        'debug_api_call'=>''));

        $instance = new Instance();
        $instance->id = 20;
        $instance->network_user_id = 20;
        $instance->network_username = 'janesmith';
        $instance->network = 'twitter';
        $insight_plugin = new NewFriendLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('new_friend_links', $instance->id, $today);
        $this->assertEqual($result->headline, "Check out the websites for @janesmith's new followers.");
        $this->assertEqual($result->text,"@janesmith's followers have these links in their bios.");
        $this->assertNotEqual(false, strpos($result->related_data, 'joesmith'));
        $this->assertNotEqual(false, strpos($result->related_data, 'joesmithie'));
        $this->assertNotEqual(false, strpos($result->related_data, 'joesmithy'));
        $this->assertNotEqual(false, strpos($result->related_data, 'joesmither'));
        // $this->debug($this->getRenderedInsightInHTML($result));
        // $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFacebookHeadlineAndTextManyLinks() {
        TimeHelper::setTime(1);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>20, 'user_name'=>'janesmith',
        'full_name'=>'Jane Smith', 'avatar'=>'avatar.jpg','url'=>'', 'is_protected'=>0, 'follower_count'=>70,
        'network'=>'facebook'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>21, 'user_name'=>'joesmith',
        'full_name'=>'Joe Smith','avatar'=>'avatar.jpg','url'=>'www.example.com','is_protected'=>0,'follower_count'=>70,
        'network'=>'facebook'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>22, 'user_name'=>'joesmithie',
        'full_name'=>'Joe Smith','avatar'=>'avatar.jpg','url'=>'www.example.com','is_protected'=>0,'follower_count'=>70,
        'network'=>'facebook'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>23, 'user_name'=>'joesmithy',
        'full_name'=>'Joe Smith','avatar'=>'avatar.jpg','url'=>'www.example.com','is_protected'=>0,'follower_count'=>70,
        'network'=>'facebook'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>24, 'user_name'=>'joesmither',
        'full_name'=>'Joe Smith','avatar'=>'avatar.jpg','url'=>'www.example.com','is_protected'=>0,'follower_count'=>70,
        'network'=>'facebook'));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'21',
        'follower_id'=>'20', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'facebook',
        'debug_api_call'=>''));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'22',
        'follower_id'=>'20', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'facebook',
        'debug_api_call'=>''));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'23',
        'follower_id'=>'20', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'facebook',
        'debug_api_call'=>''));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'24',
        'follower_id'=>'20', 'last_seen'=>$days_ago_3, 'first_seen'=>$days_ago_3, 'active'=>'1', 'network'=>'facebook',
        'debug_api_call'=>''));

        $instance = new Instance();
        $instance->id = 20;
        $instance->network_user_id = 20;
        $instance->network_username = 'janesmith';
        $instance->network = 'facebook';
        $insight_plugin = new NewFriendLinksInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('new_friend_links', $instance->id, $today);
        $text = "The people janesmith befriended this week have these links in their bios.";
        $this->assertEqual($result->headline, "Find out more about the people janesmith befriended this week.");
        $this->assertEqual($result->text, $text);
        $this->assertNotEqual(false, strpos($result->related_data, 'joesmith'));
        $this->assertNotEqual(false, strpos($result->related_data, 'joesmithie'));
        $this->assertNotEqual(false, strpos($result->related_data, 'joesmithy'));
        $this->assertNotEqual(false, strpos($result->related_data, 'joesmither'));
        // $this->debug($this->getRenderedInsightInHTML($result));
        // $this->debug($this->getRenderedInsightInEmail($result));
    }
}