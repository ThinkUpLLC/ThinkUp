<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfWeeklyBestsInsight.php
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
 * Test of WeeklyBestsInsight
 *
 * Test for the WeeklyBestsInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/weeklybests.php';

class TestOfWeeklyBestsInsight extends ThinkUpUnitTestCase {
    var $sample_hot_posts_data;

    public function setUp(){
        parent::setUp();
        $this->sample_hot_posts_data = 's:1610:"{"rows":[{"c":[{"v":'.
        '"Check out our friends\' apps: @romantimatic to stay in love http:\/\/t.co\/KGyh2iL9uI & Kidpost to '.
        'share..."},{"v":0},{"v":0},{"v":2}]},{"c":[{"v":"ThinkUp and Privacy: What we\'ve heard - Phew! '.
        'We\u2019re through the initial launch rush of ThinkUp and..."},{"v":0},{"v":2},{"v":2}]},{"c":[{"v"'.
        ':"One request we\'ve heard consistently is better privacy controls for insights in ThinkUp. Got ideas '.
        'o..."},{"v":1},{"v":3},{"v":0}]},{"c":[{"v":"A number of you told us your credit card has changed (sigh, '.
        'Target) since you signed up. We\'ll show ..."},{"v":3},{"v":0},{"v":2}]},{"c":[{"v":"Members: You should '.
        'start to see your Amazon account charged over the next few hours if you backed o..."},{"v":2},{"v":0},'.
        '{"v":0}]},{"c":[{"v":"We\'re busy faxing things over here at ThinkUp HQ. Didja know you have to fax things '.
        'if you want to m..."},{"v":2},{"v":5},{"v":8}]},{"c":[{"v":"Now that many early members have had a chance '.
        'to check out ThinkUp, we wanted to pause & offer a few..."},{"v":1},{"v":1},{"v":5}]},{"c":[{"v":"Also, '.
        'a little tip: You gotta be logged in to see your Facebook insights. (Some good stuff in there...."},'.
        '{"v":0},{"v":1},{"v":1}]},{"c":[{"v":"A number of users were seeing fewer insights when logged in. '.
        'That was a bug, boooo! But now it\'s fix..."},{"v":0},{"v":0},{"v":2}]},{"c":[{"v":"Very nice article '.
        'from @elongreen about the unfortunate necessity of blocking other users on network..."},{"v":0},{"v":0},'.
        '{"v":1}]}],"cols":[{"type":"string","label":"Tweet"},{"type":"number","label":"Replies"},{"type":"number",'.
        '"label":"Retweets"},{"type":"number","label":"Favorites"}]}";';
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testWeeklyBestsInsightForTwitter() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>10,
        'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-1d', 'related_data'=> $this->sample_hot_posts_data ));

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 5,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 34
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 15,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 33
        $posts[] = new Post(array(
            'reply_count_cache' => 2,
            'retweet_count_cache' => 5,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 27

        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/This was \@testeriffic\'s tweet of the week./', $result->headline);
        $this->assertPattern('/5 replies/', $result->text);
        $this->assertPattern('/1 retweet/', $result->text);
        $this->assertPattern('/3 favorites/', $result->text);
    }

    public function testWeeklyBestsInsightForFacebook() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'tester_fb';
        $instance->network = 'facebook';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>31, 'instance_id'=>10,
        'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-1d', 'related_data'=> $this->sample_hot_posts_data ));

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 8,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 46
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'favlike_count_cache' => 15,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 30
        $posts[] = new Post(array(
            'reply_count_cache' => 2,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 12

        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/This was tester_fb\'s status update of the week/', $result->headline);
        $this->assertPattern('/8 comments/', $result->text);
        $this->assertPattern('/3 likes/', $result->text);
    }

    public function testWeeklyBestsInsightWithOneReply() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>32, 'instance_id'=>10,
        'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-1d', 'related_data'=> $this->sample_hot_posts_data ));

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 1,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 5

        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/This was \@testeriffic\'s tweet of the week/', $result->headline);
        $this->assertPattern('/1 reply/', $result->text);
        $this->assertNoPattern('/and/', $result->text);
    }

    public function testWeeklyBestsInsightWithFavorites() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>33, 'instance_id'=>10,
        'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-1d', 'related_data'=> $this->sample_hot_posts_data ));

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 6

        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/This was \@testeriffic\'s tweet of the week/', $result->headline);
        $this->assertPattern('/3 favorites/', $result->text);
        $this->assertNoPattern('/reply/', $result->text);
        $this->assertNoPattern('/retweet/', $result->text);
        $this->assertNoPattern('/and/', $result->text);
    }

    public function testWeeklyBestsInsightWithRepliesAndFavorites() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>34, 'instance_id'=>10,
        'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-1d', 'related_data'=> $this->sample_hot_posts_data ));

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 4,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 5,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 30

        $insight_plugin = new WeeklyBestsInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_best', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/This was \@testeriffic\'s tweet of the week/', $result->headline);
        $this->assertPattern('/4 replies/', $result->text);
        $this->assertPattern('/5 favorites/', $result->text);
        $this->assertPattern('/and/', $result->text);
    }
}