<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfInterestingFollowersInsight.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * Test of InterestingFollowersInsight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/interestingfollowers.php';

class TestOfInterestingFollowersInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 9654000768;
        $instance->network_username = 'testuser';
        $instance->network = 'twitter';
        $this->instance = $instance;
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testLeastLikely() {
        // Get data ready that insight requires
        $builders = array();

        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'testuser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>1, 'is_protected'=>1,
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'popular1',
        'post_count' => 101,
        'full_name'=>'Popular Gal','avatar'=>'avatar.jpg','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'description'=>'Twitter Folower', 'location'=>'San Francisco, CA','is_verified'=>0));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        // Initialize and run the insight
        $insight_plugin = new InterestingFollowersInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('least_likely_followers', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Hey, did you see that Popular Gal followed @testuser?', $result->headline);
        $related = unserialize($result->related_data);
        $this->assertIsA($related['people'], 'Array');
        $this->assertEqual($related['people'][0]->username,'popular1');
    }

    public function testLeastLikelyNoName() {
        // Get data ready that insight requires
        $builders = array();

        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'testuser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>1, 'is_protected'=>1,
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'popular1',
        'post_count' => 101,
        'full_name'=>null,'avatar'=>'avatar.jpg','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'description'=>'Twitter Folower', 'location'=>'San Francisco, CA','is_verified'=>0));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        // Initialize and run the insight
        $insight_plugin = new InterestingFollowersInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('least_likely_followers', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Hey, did you see that @popular1 followed @testuser?', $result->headline);
        $related = unserialize($result->related_data);
        $this->assertIsA($related['people'], 'Array');
        $this->assertEqual($related['people'][0]->username,'popular1');
        $rendered = $this->getRenderedInsightInHTML($result);
        $this->assertPattern('/<div class="user">.*popular1/ms', $rendered);
    }

    public function testLeastLikelyAllBadCriteria() {
        // Get data ready that insight requires
        $builders = array();

        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'testuser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>1, 'is_protected'=>1,
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'notenoughposts',
        'post_count' => 88,
        'full_name'=>'Popular Gal','avatar'=>'avatar.jpg','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'description'=>'Twitter Folower', 'location'=>'San Francisco, CA','is_verified'=>0));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000770', 'user_name'=>'nourl',
        'post_count' => 102, 'url'=>null,
        'full_name'=>'Popular Gal','avatar'=>'avatar.jpg','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'description'=>'Twitter Folower', 'location'=>'San Francisco, CA','is_verified'=>0));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000771', 'user_name'=>'nodescription',
        'post_count' => 102, 'description'=>null,
        'full_name'=>'Popular Gal','avatar'=>'avatar.jpg','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'location'=>'San Francisco, CA','is_verified'=>0));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000772', 'user_name'=>'defaultavatar',
        'post_count'=>102,'avatar'=>'https://abs.twimg.com/sticky/default_profile_images/default_profile_2_bigger.png',
        'full_name'=>'Popular Gal','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'description'=>'Twitter Folower', 'location'=>'San Francisco, CA','is_verified'=>0));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000770',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000771',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000772',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        // Initialize and run the insight
        $insight_plugin = new InterestingFollowersInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('least_likely_followers', 10, $today);
        $this->assertNull($result);
    }

    public function testLeastLikelyGoodAndBadUsers() {
        // Get data ready that insight requires
        $builders = array();

        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'testuser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>1, 'is_protected'=>1,
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'notenoughposts',
        'post_count' => 88,
        'full_name'=>'Popular Gal','avatar'=>'avatar.jpg','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'description'=>'Twitter Folower', 'location'=>'San Francisco, CA','is_verified'=>0));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000770', 'user_name'=>'nourl',
        'post_count' => 102, 'url'=>null,
        'full_name'=>'Popular Gal','avatar'=>'avatar.jpg','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'description'=>'Twitter Folower', 'location'=>'San Francisco, CA','is_verified'=>0));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000771', 'user_name'=>'nodescription',
        'post_count' => 102, 'description'=>null,
        'full_name'=>'Popular Gal','avatar'=>'avatar.jpg','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'location'=>'San Francisco, CA','is_verified'=>0));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000772', 'user_name'=>'defaultavatar',
        'post_count'=>102,'avatar'=>'https://abs.twimg.com/sticky/default_profile_images/default_profile_2_bigger.png',
        'full_name'=>'Popular Gal','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'description'=>'Twitter Folower', 'location'=>'San Francisco, CA','is_verified'=>0));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000773', 'user_name'=>'gooduser',
        'post_count'=>102,'avatar'=>'avatar.jpg', 'url' =>'http://google.com',
        'full_name'=>'Popular Gal','avatar'=>'avatar.jpg','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'description'=>'Twitter Folower', 'location'=>'San Francisco, CA','is_verified'=>0));


        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000770',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000771',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000772',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000773',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        // Initialize and run the insight
        $insight_plugin = new InterestingFollowersInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('least_likely_followers', 10, $today);
        $this->assertNotNull($result);
        $data = unserialize($result->related_data);
        $this->assertEqual(1, count($data['people']));
        $this->assertEqual('gooduser', $data['people'][0]->username);
    }

    public function testNewVerifiedFollower() {
        // Get data ready that insight requires
        $builders = array();

        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'testuser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>1,
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'testfollower1',
        'full_name'=>'Verified Dude', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'Twitter Folower', 'location'=>'San Francisco, CA','is_verified'=>1));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter'));

        // Initialize and run the insight
        $insight_plugin = new InterestingFollowersInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('verified_followers', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/Wow: <strong>Verified Dude<\/strong>, a verified user, followed \@testuser\./',
            $result->headline);
        $this->assertPattern('/avatar.jpg/', $result->header_image);
        $rendered = $this->getRenderedInsightInHTML($result);
        $this->assertPattern('/Twitter Folower/', $rendered);
        $this->assertEqual(1, substr_count($rendered, 'avatar.jpg'));
    }

    public function testLeastLikelyMutltiple() {
        // Get data ready that insight requires
        $builders = array();

        // User
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000768', 'user_name'=>'testuser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>1, 'is_protected'=>1,
        'network'=>'twitter', 'description'=>'A test Twitter User', 'location'=>'San Francisco, CA'));

        // Followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000769', 'user_name'=>'popular1',
        'post_count' => 101,
        'full_name'=>'Popular Gal','avatar'=>'avatar.jpg','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'description'=>'Twitter Folower', 'location'=>'San Francisco, CA','is_verified'=>0));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000770', 'user_name'=>'popular2',
        'post_count' => 101,
        'full_name'=>'Popular Gal 2','avatar'=>'avatar.jpg','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'description'=>'Twitter Folower', 'location'=>'San Francisco, CA','is_verified'=>0));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'9654000771', 'user_name'=>'popular3',
        'post_count' => 101,
        'full_name'=>'Popular Gal 3','avatar'=>'avatar.jpg','follower_count'=>36000,'is_protected'=>0,'friend_count'=>1,
        'network'=>'twitter', 'description'=>'Twitter Folower', 'location'=>'San Francisco, CA','is_verified'=>0));

        // Follows
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000769',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000770',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'9654000768', 'follower_id'=>'9654000771',
        'last_seen'=>'-0d', 'first_seen'=>'-0d', 'network'=>'twitter','active'=>1));

        // Initialize and run the insight
        $insight_plugin = new InterestingFollowersInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('least_likely_followers', 10, $today);
        $rendered = $this->getRenderedInsightInHTML($result);
        $this->debug($rendered);

        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('<strong>3 interesting people</strong> followed @testuser.', $result->headline);
        $this->assertIsA($result->related_data['people'], 'Array');
        $this->assertEqual($result->related_data['people'][0]->username,'popular1');
    }
}
