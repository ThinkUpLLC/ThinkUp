<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfMostFollowedFolloweesInsight.php
 *
 * Copyright (c) Gareth Brady
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
 * Test of Most Followed Followees Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Gareth Brady
 * @author Gareth Brady <gareth.brady92[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/mostfollowedfollowees.php';

class TestOfMostFollowedFolloweesInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'janesmith';
        $instance->network = 'twitter';
        $instance->crawler_last_run = '2014-05-27 15:33:07';
        $this->instance = $instance;
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new MostFollowedFolloweesInsight();
        $this->assertIsA($insight_plugin, 'MostFollowedFolloweesInsight');
    }

    public function testNotFollowingEnoughUsers() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'993', 'user_name'=>'v5',
        'full_name'=>'Ron Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>400, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'994', 'user_name'=>'v6',
        'full_name'=>'Jim Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>500, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'995', 'user_name'=>'v7',
        'full_name'=>'Steve Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>600, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>993, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'twitter','active' => 1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>994, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'twitter','active' => 1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>995, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'twitter','active' => 1));

        $insight_plugin = new MostFollowedFolloweesInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);
        $today = date('Y-m-d');
        $result = $insight_dao->getInsight("most_followed_followees", 1, $today);
        $this->assertNull($result);
    }

    public function testTop5PopularFollowees() {
        TimeHelper::setTime(3);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'42', 'user_name'=>'janesmith',
        'full_name'=>'jane smith', 'avatar'=>'avatar.jpg', 'follower_count'=>11, 'friend_count'=>11,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'999', 'user_name'=>'v11',
        'full_name'=>'Ron Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>70, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1000', 'user_name'=>'v12',
        'full_name'=>'Jim Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>80, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1001', 'user_name'=>'v13',
        'full_name'=>'Steve Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>90, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1002', 'user_name'=>'v14',
        'full_name'=>'Steve Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>100, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1003', 'user_name'=>'v15',
        'full_name'=>'Steve Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>110, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>999, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'twitter','active' => 1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1000, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'twitter','active' => 1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1001, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'twitter','active' => 1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1002, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'twitter','active' => 1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1003, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'twitter','active' => 1));

        $insight_plugin = new MostFollowedFolloweesInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);
        $today = date('Y-m-d');
        $result = $insight_dao->getInsight("most_followed_followees", 1, $today);
        $this->assertNotEqual(false, strpos($result->text,'@v15, @v14, @v13, @v12 and @v11'));
        $this->assertNotEqual(false, strpos($result->text,'@janesmith'));
        $this->assertNotEqual(false, strpos($result->headline,'Twitter'));
        $this->assertNotNull($result->related_data);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testTop5PopularFolloweesWithUser() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'42', 'user_name'=>'janesmith',
        'full_name'=>'jane smith', 'avatar'=>'avatar.jpg', 'follower_count'=>1000, 'friend_count'=>1000,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1000', 'user_name'=>'v12',
        'full_name'=>'Jim Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>80, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1001', 'user_name'=>'v13',
        'full_name'=>'Steve Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>90, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1002', 'user_name'=>'v14',
        'full_name'=>'Steve Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>100, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1003', 'user_name'=>'v15',
        'full_name'=>'Steve Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>110, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1000, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'twitter','active' => 1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1001, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'twitter','active' => 1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1002, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'twitter','active' => 1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1003, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'twitter','active' => 1));

        $insight_plugin = new MostFollowedFolloweesInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 7);
        $today = date('Y-m-d');
        $result = $insight_dao->getInsight("most_followed_followees", 1, $today);
        $text = "Whoa! The five most popular people in @janesmith's stream includes...";
        $text .=" @janesmith! Beyond that,  @v15, @v14, @v13, @v12 an have more followers";
        $text .=" than anyone else @janesmith follows.";
        $this->assertEqual($result->text, $text);
        $this->assertNotEqual(false, strpos($result->headline,'Twitter'));
        $this->assertNotNull($result->related_data);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testInstagramCopy() {
        TimeHelper::setTime(3);
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'janesmith';
        $instance->network = 'instagram';
        $instance->crawler_last_run = '2014-05-27 15:33:07';
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'42', 'user_name'=>'janesmith',
        'full_name'=>'jane smith', 'avatar'=>'avatar.jpg', 'follower_count'=>11, 'friend_count'=>11,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'instagram', 'description'=>'Test'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'999', 'user_name'=>'v11',
        'full_name'=>'Ron Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>70, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'instagram', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1000', 'user_name'=>'v12',
        'full_name'=>'Jim Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>80, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'instagram', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1001', 'user_name'=>'v13',
        'full_name'=>'Steve Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>90, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'instagram', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1002', 'user_name'=>'v14',
        'full_name'=>'Steve Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>100, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'instagram', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1003', 'user_name'=>'v15',
        'full_name'=>'Steve Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>110, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'instagram', 'description'=>'Test'));
        
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>999, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'instagram','active' => 1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1000, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'instagram','active' => 1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1001, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'instagram','active' => 1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1002, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'instagram','active' => 1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>1003, 'follower_id'=>42,
        'last_seen'=>'-1d', 'network'=>'instagram','active' => 1));

        $insight_plugin = new MostFollowedFolloweesInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 7);
        $today = date('Y-m-d');
        $result = $insight_dao->getInsight("most_followed_followees", 1, $today);
        $this->assertNotEqual(false, strpos($result->text,'v15, v14, v13, v12 and v11'));
        $this->assertNotEqual(false, strpos($result->headline,'janesmith'));
        $this->assertNotEqual(false, strpos($result->headline,'Instagram'));
        $this->assertNotNull($result->related_data);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }
}