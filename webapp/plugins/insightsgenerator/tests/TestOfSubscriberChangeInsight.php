<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfSubscriberChangeInsight.php
 *
 * Copyright (c) 2012-2013 Aaron Kalair
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
 * Test of SubscriberChangeInsight
 *
 * Test for the SubscriberChangeInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/subscriberchange.php';

class TestOfSubscriberChangeInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testHighIncrease() {
        $user = self::buildData();
        // Insert a video that would have raised the subscriber count by >= 50%
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'subscribers_gained'=>9,
        'subscribers_lost'=>0, 'average_view_percentage'=>10));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';
        $instance->network_user_id = 1;

        $insight = new SubscriberChangeInsight();
        $posts[] = new Post($post_builder->columns);
        $insight->generateInsight($instance, $posts, 7);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('subscriber_change1', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $headline = "My Great Video increased ev's subscriber count by <strong>81.82%</strong>.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 2);
    }

    public function testMediumIncrease() {
        $user = self::buildData();
        // Insert a video that would have raised the subscriber count by >= 25%
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'subscribers_gained'=>4,
        'subscribers_lost'=>0, 'average_view_percentage'=>10));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';
        $instance->network_user_id = 1;

        $insight = new SubscriberChangeInsight();
        $posts[] = new Post($post_builder->columns);
        $insight->generateInsight($instance, $posts, 7);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('subscriber_change1', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $headline = "My Great Video increased ev's subscriber count by <strong>25%</strong>.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 1);
    }

    public function testLowIncrease() {
        $user = self::buildData();
        // Insert a video that would have raised the subscriber count by >= 10%
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'subscribers_gained'=>3,
        'subscribers_lost'=>0, 'average_view_percentage'=>10));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';
        $instance->network_user_id = 1;

        $insight = new SubscriberChangeInsight();
        $posts[] = new Post($post_builder->columns);
        $insight->generateInsight($instance, $posts, 7);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('subscriber_change1', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $headline = "My Great Video increased ev's subscriber count by <strong>17.65%</strong>.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 0);
    }

    public function testHighDecrease() {
        $user = self::buildData();
        // Insert a video that would have lowered the subscriber count by >= 50%
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'subscribers_gained'=>0,
        'subscribers_lost'=>20, 'average_view_percentage'=>10));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';
        $instance->network_user_id = 1;

        $insight = new SubscriberChangeInsight();
        $posts[] = new Post($post_builder->columns);
        $insight->generateInsight($instance, $posts, 7);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('subscriber_change1', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $headline = "My Great Video decreased ev's subscriber count by <strong>50%</strong>.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 2);
    }

    public function testMediumDecrease() {
        $user = self::buildData();
        // Insert a video that would have lowered the subscriber count by >= 50%
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'subscribers_gained'=>0,
        'subscribers_lost'=>9, 'average_view_percentage'=>10));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';
        $instance->network_user_id = 1;

        $insight = new SubscriberChangeInsight();
        $posts[] = new Post($post_builder->columns);
        $insight->generateInsight($instance, $posts, 7);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('subscriber_change1', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $headline = "My Great Video decreased ev's subscriber count by <strong>31.03%</strong>.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 1);
    }

    public function testLowDecrease() {
        $user = self::buildData();
        // Insert a video that would have lowered the subscriber count by >= 50%
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-1d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'subscribers_gained'=>0,
        'subscribers_lost'=>4, 'average_view_percentage'=>10));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network = 'youtube';
        $instance->network_username = 'ev';
        $instance->network_user_id = 1;

        $insight = new SubscriberChangeInsight();
        $posts[] = new Post($post_builder->columns);
        $insight->generateInsight($instance, $posts, 7);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('subscriber_change1', 1, date('Y-m-d', strtotime('-1 day')));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $headline = "My Great Video decreased ev's subscriber count by <strong>16.67%</strong>.";
        $this->assertEqual($result->headline, $headline);
        $this->assertEqual($result->emphasis, 0);
    }

    private function buildData() {
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1', 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>20,
        'network'=>'youtube'));
        return $builders;
    }

}
