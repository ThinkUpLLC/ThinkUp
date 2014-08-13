<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfFollowerMapInsight.php
 *
 * Copyright (c) 2014 Gareth Brady
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
 * Test of Follower Map Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Gareth Brady
 * @author Gareth Brady <gareth.brady92[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/followermap.php';

class TestOfFollowerMapInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new FollowerMapInsight();
        $this->assertIsA($insight_plugin, 'FollowerMapInsight' );
    }

    public function testNoFollowersWithLocation() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 9654000768;
        $instance->network_username = 'twitteruser';
        $instance->network = 'twitter';
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();
        $insight_plugin = new FollowerMapInsight();
        
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'twitteruser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter User'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'twitterfollower1',
        'full_name'=>'Twitter Follower One', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>''));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000770', 'user_name'=>'twitterfollower2',
        'full_name'=>'Twitter Follower Two', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>''));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000771', 'user_name'=>'twitterfollower3',
        'full_name'=>'Twitter Follower Three', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>''));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-8d', 'network'=>'twitter','active'=>1));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000770',
        'last_seen'=>'-0d', 'first_seen'=>'-1d', 'network'=>'twitter','active'=>1));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000771',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('follower_map_insight', $instance->id, $today); 
        $this->assertNull($result);
    }

    public function testFollowersWithLocation() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 9654000768;
        $instance->network_username = 'twitteruser';
        $instance->network = 'twitter';
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();
        $insight_plugin = new FollowerMapInsight();

        TimeHelper::setTime(3);
        
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'twitteruser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter User'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'twitterfollower1',
        'full_name'=>'Twitter Follower One', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Francisco, CA'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000770', 'user_name'=>'twitterfollower2',
        'full_name'=>'Twitter Follower Two', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Francisco, CA'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000771', 'user_name'=>'twitterfollower3',
        'full_name'=>'Twitter Follower Three', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Diego, CA'));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-40d', 'network'=>'twitter','active'=>1));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000770',
        'last_seen'=>'-0d', 'first_seen'=>'-1d', 'network'=>'twitter','active'=>1));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000771',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        $builders[] = FixtureBuilder::build('encoded_locations',array('id'=>'96','short_name'=>'San Diego, CA',
        'full_name'=>'San Diego, CA', 'latlng'=>'53.3498053,-6.2603097'));

        $builders[] = FixtureBuilder::build('encoded_locations',array('id'=>'97','short_name'=>'San Francisco, CA',
        'full_name'=>'San Francisco, CA', 'latlng'=>'53.3498053,-6.2603097'));

        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('follower_map_insight', $instance->id, $today);
        $this->assertEqual(false, strpos($result->related_data, 'twitterfollower1'));
        $this->assertNotEqual(false, strpos($result->related_data, 'twitterfollower2'));
        $this->assertNotEqual(false, strpos($result->related_data, 'twitterfollower3'));
        $this->assertNotEqual(false, strpos($result->related_data, 'San Francisco, CA'));
        $this->assertNotEqual(false, strpos($result->related_data, 'San Diego, CA'));
        $this->assertEqual($result->text, "Here are @twitteruser's new followers from the last month on a map.");
        $this->assertEqual($result->headline, "Want to know where @twitteruser's new followers are from ?");
    }

    public function testFriendsWithLocation() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 9654000768;
        $instance->network_username = 'twitteruser';
        $instance->network = 'facebook';
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();
        $insight_plugin = new FollowerMapInsight();

        TimeHelper::setTime(2);
        
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'twitteruser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'facebook', 'description'=>'A test Facebook Friend'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'facebookfriend1',
        'full_name'=>'Twitter Follower One', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'facebook', 'description'=>'A test Facebook Friend', 'location'=>'San Francisco, CA'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000770', 'user_name'=>'facebookfriend2',
        'full_name'=>'Twitter Follower Two', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'facebook', 'description'=>'A test Facebook Friend', 'location'=>'San Francisco, CA'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000771', 'user_name'=>'facebookfriend3',
        'full_name'=>'Twitter Follower Three', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'facebook', 'description'=>'A test Facebook Friend', 'location'=>'San Diego, CA'));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-40d', 'network'=>'facebook','active'=>1));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000770',
        'last_seen'=>'-0d', 'first_seen'=>'-1d', 'network'=>'facebook','active'=>1));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000771',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'facebook','active'=>1));

        $builders[] = FixtureBuilder::build('encoded_locations',array('id'=>'96','short_name'=>'San Diego, CA',
        'full_name'=>'San Diego, CA', 'latlng'=>'53.3498053,-6.2603097'));

        $builders[] = FixtureBuilder::build('encoded_locations',array('id'=>'97','short_name'=>'San Francisco, CA',
        'full_name'=>'San Francisco, CA', 'latlng'=>'53.3498053,-6.2603097'));

        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('follower_map_insight', $instance->id, $today);
        $this->assertEqual(false, strpos($result->related_data, 'facebookfriend1'));
        $this->assertNotEqual(false, strpos($result->related_data, 'facebookfriend2'));
        $this->assertNotEqual(false, strpos($result->related_data, 'facebookfriend3'));
        $this->assertNotEqual(false, strpos($result->related_data, 'San Francisco, CA'));
        $this->assertNotEqual(false, strpos($result->related_data, 'San Diego, CA'));
        $this->assertEqual($result->text, "Here are twitteruser's new friends from the last month on a map.");
        $this->assertEqual($result->headline, "facebook is a global community.");
    }

    public function testFollowersWithLocationHeadline() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 9654000768;
        $instance->network_username = 'twitteruser';
        $instance->network = 'twitter';
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();
        $insight_plugin = new FollowerMapInsight();

        TimeHelper::setTime(1);
        
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'twitteruser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter User'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'twitterfollower1',
        'full_name'=>'Twitter Follower One', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Francisco, CA'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000770', 'user_name'=>'twitterfollower2',
        'full_name'=>'Twitter Follower Two', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Francisco, CA'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000771', 'user_name'=>'twitterfollower3',
        'full_name'=>'Twitter Follower Three', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Diego, CA'));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-40d', 'network'=>'twitter','active'=>1));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000770',
        'last_seen'=>'-0d', 'first_seen'=>'-1d', 'network'=>'twitter','active'=>1));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000771',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        $builders[] = FixtureBuilder::build('encoded_locations',array('id'=>'96','short_name'=>'San Diego, CA',
        'full_name'=>'San Diego, CA', 'latlng'=>'53.3498053,-6.2603097'));

        $builders[] = FixtureBuilder::build('encoded_locations',array('id'=>'97','short_name'=>'San Francisco, CA',
        'full_name'=>'San Francisco, CA', 'latlng'=>'53.3498053,-6.2603097'));

        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('follower_map_insight', $instance->id, $today);
        $this->assertEqual($result->headline, "Location, Location, Location");
    }

    public function testOneFollowerWithLocation() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 9654000768;
        $instance->network_username = 'twitteruser';
        $instance->network = 'twitter';
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();
        $insight_plugin = new FollowerMapInsight();

        TimeHelper::setTime(3);
        
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'twitteruser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter User'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'twitterfollower1',
        'full_name'=>'Twitter Follower One', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Francisco, CA'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000771', 'user_name'=>'twitterfollower3',
        'full_name'=>'Twitter Follower Three', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower'));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-5d', 'network'=>'twitter','active'=>1));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000771',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        $builders[] = FixtureBuilder::build('encoded_locations',array('id'=>'97','short_name'=>'San Francisco, CA',
        'full_name'=>'San Francisco, CA', 'latlng'=>'53.3498053,-6.2603097'));

        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('follower_map_insight', $instance->id, $today);
        $this->assertEqual($result->text, "Here is @twitteruser's new follower from the last month on a map.");
        $this->assertEqual($result->headline, "Want to know where @twitteruser's new follower is from ?");
    }
}
