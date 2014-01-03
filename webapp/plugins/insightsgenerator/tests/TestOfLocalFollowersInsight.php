<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfLocalFollowersInsight.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
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
 * Test of LocalFollowersInsight
 *
 * Test for the LocalFollowersInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/localfollowers.php';

class TestOfLocalFollowersInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testLocalFollowersInsightWithLocation() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 9654000768;
        $instance->network_username = 'testuser';
        $instance->network = 'twitter';

        $builders = array();

        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'testuser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>1,
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'testfollower1',
        'full_name'=>'Twitter Follower One', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Francisco, CA'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000770', 'user_name'=>'testfollower2',
        'full_name'=>'Twitter Follower Two', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Francisco, CA'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000771', 'user_name'=>'testfollower3',
        'full_name'=>'Twitter Follower Three', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Diego, CA'));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000770',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000771',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter'));

        // Initialize and run the insight
        $insight_plugin = new LocalFollowersInsight();
        $insight_plugin->generateInsight($instance, $posts=array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('local_followers', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/<strong>2 people<\/strong> in San Francisco, CA followed \@testuser./',
            $result->headline);
        $this->assertNoPattern('/avatar.jpg/', $result->header_image);
    }

    public function testLocalFollowersInsightWithHeaderImage() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 9654000768;
        $instance->network_username = 'testuser';
        $instance->network = 'twitter';

        $builders = array();

        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'testuser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>1,
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'testfollower1',
        'full_name'=>'Twitter Follower One', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Francisco, CA'));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter'));

        // Initialize and run the insight
        $insight_plugin = new LocalFollowersInsight();
        $insight_plugin->generateInsight($instance, $posts=array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('local_followers', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/<strong>1 person<\/strong> in San Francisco, CA followed \@testuser./',
            $result->headline);
        $this->assertPattern('/avatar.jpg/', $result->header_image);
    }

    public function testLocalFollowersInsightWithoutLocation() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 9654000768;
        $instance->network_username = 'testuser';
        $instance->network = 'twitter';

        $builders = array();

        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'testuser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>1,
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>''));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'testfollower1',
        'full_name'=>'Twitter Follower One', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Francisco, CA'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000770', 'user_name'=>'testfollower2',
        'full_name'=>'Twitter Follower Two', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>'San Francisco, CA'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000771', 'user_name'=>'testfollower3',
        'full_name'=>'Twitter Follower Three', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Folower', 'location'=>''));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000770',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000771',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter'));

        // Initialize and run the insight
        $insight_plugin = new LocalFollowersInsight();
        $insight_plugin->generateInsight($instance, $posts=array(), 3);

        // Assert that insight did NOT get inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('local_followers', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNull($result);
    }

}
