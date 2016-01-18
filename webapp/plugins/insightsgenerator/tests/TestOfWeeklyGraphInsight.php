<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfWeeklyGraphInsight.php
 *
 * Copyright (c) 2013-2016 Nilaksh Das, Gina Trapani
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
 * Test of WeeklyGraphInsight
 *
 * Test for the WeeklyGraphInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013-2016 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/weeklygraph.php';

class TestOfWeeklyGraphInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testWeeklyGraphInsightForTwitterNotEnoughPosts() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $user = new User();
        $user->avatar = 'https://farm7.staticflickr.com/6146/5976784449_4fe7c02760_q.jpg';

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => 'a',
            'reply_count_cache' => 5,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 34
        $posts[] = new Post(array(
            'post_text' => 'b',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 15,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 33
        $posts[] = new Post(array(
            'post_text' => 'c',
            'reply_count_cache' => 2,
            'retweet_count_cache' => 5,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 27

        TimeHelper::setTime(1); //use the first possible headline
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, $user, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        //$this->debug(Utils::varDumpToString($result));
        $this->assertNull($result);
    }

    public function testWeeklyGraphInsightForTwitterEnoughPosts() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $user = new User();
        $user->avatar = 'https://farm7.staticflickr.com/6146/5976784449_4fe7c02760_q.jpg';

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => 'a',
            'reply_count_cache' => 5,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 34
        $posts[] = new Post(array(
            'post_text' => 'b',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 1,
            'favlike_count_cache' => 15,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 33
        $posts[] = new Post(array(
            'post_text' => 'c',
            'reply_count_cache' => 2,
            'retweet_count_cache' => 5,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 27
        $posts[] = new Post(array(
            'post_text' => 'c',
            'reply_count_cache' => 2,
            'retweet_count_cache' => 5,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 27

        TimeHelper::setTime(1); //use the first possible headline
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, $user, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        //$this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("Whatever @testeriffic said must have been memorable", $result->headline);
        $this->assertEqual('In the past week, @testeriffic got 20 likes, beating out 9 replies and 12 retweets.',
            $result->text);
        $this->dumpRenderedInsight($result, $instance, "");
    }

    public function testWeeklyGraphInsightForFacebookNotEnoughPosts() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'tester_fb';
        $instance->network = 'facebook';

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

        TimeHelper::setTime(1); //use the first possible headline
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        //$this->debug(Utils::varDumpToString($result));
        $this->assertNull($result);
    }

    public function testWeeklyGraphInsightForFacebookEnoughPosts() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'tester_fb';
        $instance->network = 'facebook';

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
        $posts[] = new Post(array(
            'reply_count_cache' => 2,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 12

        TimeHelper::setTime(1); //use the first possible headline
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        //$this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("Whatever tester_fb said must have been memorable", $result->headline);
        $this->assertEqual('In the past week, tester_fb got 20 likes, beating out 12 comments.', $result->text);
        $this->dumpRenderedInsight($result, $instance, "");
    }

    public function testWeeklyGraphInsightTwitterWithFavorites() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>33, 'instance_id'=>10,
        'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-1d', 'related_data'=>serialize('sample hot posts data') ));

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 6
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'favlike_count_cache' => 15,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 30
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 12
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 12

        TimeHelper::setTime(1); //use the first possible headline
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        //$this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("Whatever @testeriffic said must have been memorable", $result->headline);
        $this->assertEqual('In the past week, @testeriffic got 20 likes.', $result->text);
        $this->dumpRenderedInsight($result, $instance, "");
    }

    public function testWeeklyGraphInsightInstagramWithFavorites() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'instagram';

        $builders = array();
        $builders[] = FixtureBuilder::build('insights', array('id'=>33, 'instance_id'=>10,
        'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-1d', 'related_data'=>serialize('sample hot posts data') ));

        $posts = array();

        $post_vals = array(
            'id' => 33,
            'post_id' => 33,
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 3,
            'network' => 'instagram',
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        );
        $posts[] = new Post($post_vals); // popularity_index = 6
        $builders[] = FixtureBuilder::build('posts', $post_vals);
        $builders[] = FixtureBuilder::build('photos', array('post_key'=>33, 'post_id'=>33, 'is_short_video'=>0));
        $post_vals = array(
            'id' => 44,
            'post_id' => 44,
            'reply_count_cache' => 0,
            'favlike_count_cache' => 15,
            'network' => 'instagram',
            'is_short_video'=>1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        );
        $posts[] = new Post($post_vals); // popularity_index = 30
        $builders[] = FixtureBuilder::build('posts', $post_vals);
        $builders[] = FixtureBuilder::build('photos', array('post_key'=>44, 'post_id'=>44, 'is_short_video'=>1));

        $post_vals = array(
            'id' => 35,
            'post_id' => 35,
            'reply_count_cache' => 0,
            'favlike_count_cache' => 1,
            'network' => 'instagram',
            'is_short_video'=>0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        );
        $posts[] = new Post($post_vals); // popularity_index = 12
        $builders[] = FixtureBuilder::build('posts', $post_vals);
        $builders[] = FixtureBuilder::build('photos', array('post_key'=>35, 'post_id'=>35, 'is_short_video'=>0));

        $post_vals = array(
            'id' => 36,
            'post_id' => 36,
            'reply_count_cache' => 0,
            'favlike_count_cache' => 1,
            'network' => 'instagram',
            'is_short_video'=>0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        );
        $posts[] = new Post($post_vals); // popularity_index = 12
        $builders[] = FixtureBuilder::build('posts', $post_vals);
        $builders[] = FixtureBuilder::build('photos', array('post_key'=>36, 'post_id'=>36, 'is_short_video'=>1));

        TimeHelper::setTime(1); //use the first possible headline
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        //$this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("testeriffic really won hearts", $result->headline);
        $this->assertEqual('In the past week, testeriffic got 20 likes.', $result->text);
        $html = $this->getRenderedInsightInHTML($result);
        //Two of the posts in the chart should have the video prefix
        $this->assertPattern('/Video/', $html);
        //'/Unable to describe table "tu_notable"/', $e->getMessage());
        $this->dumpRenderedInsight($result, $instance, "");
    }

    public function testWeeklyGraphInsightTwitterWithRepliesAndFavorites() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 4,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 5,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 30
        $posts[] = new Post(array(
            'reply_count_cache' => 5,
            'favlike_count_cache' => 15,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 30
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 12
        $posts[] = new Post(array(
            'reply_count_cache' => 6,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 12

        TimeHelper::setTime(1); //use the first possible headline
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        //$this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("Whatever @testeriffic said must have been memorable", $result->headline);
        $this->assertEqual('In the past week, @testeriffic got 22 likes, beating out 15 replies.', $result->text);
        $this->dumpRenderedInsight($result, $instance, "");
    }

    public function testForCommas() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 4179,
            'retweet_count_cache' => 9999,
            'favlike_count_cache' => 1234,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 30
        $posts[] = new Post(array(
            'reply_count_cache' => 5,
            'favlike_count_cache' => 15,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 30
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 12
        $posts[] = new Post(array(
            'reply_count_cache' => 6,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 12

        TimeHelper::setTime(1); //use the first possible headline
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("@testeriffic shared lots of things people wanted to amplify", $result->headline);
        $this->assertEqual("This past week, @testeriffic's retweets outnumbered replies by 5,809 and likes "
            ."by 8,748.", $result->text);
        $this->dumpRenderedInsight($result, $instance, "");

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 4179,
            'retweet_count_cache' => 4242,
            'favlike_count_cache' => 999999,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 30

        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("@testeriffic shared lots of things people wanted to amplify", $result->headline);
        $this->assertEqual("This past week, @testeriffic's retweets outnumbered replies by 5,809 and likes ".
            "by 8,748.", $result->text);

        $this->dumpRenderedInsight($result, $instance, "");
    }

    public function testSkipInsightIfNoActivity() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $posts = array();
        for ($i=0; $i<=20; $i++) {
            $days = 1 + floor($i/2);
            $posts[] = new Post(array(
                'post_text' => 'not_cool',
                'reply_count_cache' => 0,
                'retweet_count_cache' => 0,
                'favlike_count_cache' => 0,
                'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
            ));
        }
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertNull($result);
    }

    public function testForAtLeastFourPosts() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $posts = array();
        for ($i=0; $i<=3; $i++) {
            $days = 1 + floor($i/2);
            $posts[] = new Post(array(
                'post_text' => 'not_cool',
                'reply_count_cache' => 4,
                'retweet_count_cache' => 0,
                'favlike_count_cache' => 0,
                'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
            ));
        }
        TimeHelper::setTime(1); //use the first possible headline
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);

        $data = unserialize($result->related_data);
        $this->assertNotNull($data['posts']);
        $this->assertEqual("@testeriffic really inspired conversations", $result->headline);
        $this->dumpRenderedInsight($result, $instance, "");
    }

    public function testPostLimitAndSorting() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $posts = array();
        for ($i=0; $i<=10; $i++) {
            $days = 1 + floor($i/2);
            $posts[] = new Post(array(
                'post_text' => 'not_cool',
                'reply_count_cache' => 4,
                'retweet_count_cache' => 0,
                'favlike_count_cache' => 0,
                'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
            )); // popularity_index = 20
            $posts[] = new Post(array(
                'post_text' => 'cool',
                'reply_count_cache' => 5,
                'retweet_count_cache' => 0,
                'favlike_count_cache' => 0,
                'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
            )); // popularity_index = 25
        }

        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);

        $data = unserialize($result->related_data);
        $this->assertNotNull($data['posts']);
        $posts = json_decode($data['posts'][0]);
        $this->assertEqual(10, count($posts->rows));
        // Ensure the popular posts are shown
        for ($i=0; $i<10; $i++) {
            $post = $posts->rows[$i];
            //print_r($post);
            $this->assertEqual('cool...', $post->c[0]->v);
        }
        $this->dumpRenderedInsight($result, $instance, "");
    }

    public function testInsightTextsTwitter() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $insight_plugin = new WeeklyGraphInsight();

        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        TimeHelper::setTime(1); //set headline to expect
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("@testeriffic really inspired conversations", $result->headline);
        $this->assertEqual('In the past week, replies to @testeriffic outnumbered likes or retweets.',
            $result->text);
        $this->dumpRenderedInsight($result, $instance, "");

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        TimeHelper::setTime(2); //set headline to expect
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("@testeriffic shared lots of things people wanted to amplify", $result->headline);
        $this->assertEqual("This past week, @testeriffic's retweets outnumbered replies by 12 and likes by 12.",
            $result->text);
        $this->dumpRenderedInsight($result, $instance, "");

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        TimeHelper::setTime(3); //set headline to expect
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("@testeriffic really inspired conversations", $result->headline);
        $this->assertEqual('In the past week, replies to @testeriffic outnumbered likes.', $result->text);
        $this->dumpRenderedInsight($result, $instance, "");

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        TimeHelper::setTime(4); //set headline to expect
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("@testeriffic really inspired conversations", $result->headline);
        $this->assertEqual('@testeriffic got more replies than anything else.', $result->text);
        $this->dumpRenderedInsight($result, $instance, "");

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        TimeHelper::setTime(5); //set headline to expect
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Whatever @testeriffic said must have been memorable", $result->headline);
        $this->assertEqual('In the past week, @testeriffic got 12 likes, beating out 8 retweets.', $result->text);
        $this->dumpRenderedInsight($result, $instance, "");

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Whatever @testeriffic said must have been memorable", $result->headline);
        $this->assertEqual('In the past week, @testeriffic got 12 likes.', $result->text);
        $this->dumpRenderedInsight($result, $instance, "");

        $posts = array(new Post(array(
            'reply_count_cache' => 1,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Whatever @testeriffic said must have been memorable", $result->headline);
        $this->assertEqual('In the past week, @testeriffic got 12 likes.', $result->text);
        $this->dumpRenderedInsight($result, $instance, "");
    }

    public function testSingularLike() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $insight_plugin = new WeeklyGraphInsight();
        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 1,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        TimeHelper::setTime(1); //set headline to expect
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertNull($result);
    }

    public function testInsightTextsFacebook() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'Jo ThinkUp';
        $instance->network = 'facebook';
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $insight_plugin = new WeeklyGraphInsight();

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        TimeHelper::setTime(1); //set headline to expect
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Jo ThinkUp really inspired conversations", $result->headline);
        $this->assertEqual('In the past week, comments to Jo ThinkUp outnumbered likes or reshares.', $result->text);
        $this->dumpRenderedInsight($result, $instance, "");

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        TimeHelper::setTime(2); //set headline to expect
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Jo ThinkUp shared lots of things people wanted to amplify", $result->headline);
        $this->assertEqual("This past week, Jo ThinkUp's reshares outnumbered comments by 12 and likes by 12.",
            $result->text);
        $this->dumpRenderedInsight($result, $instance, "");

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        TimeHelper::setTime(3); //set headline to expect
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Jo ThinkUp really inspired conversations", $result->headline);
        $this->assertEqual('In the past week, comments to Jo ThinkUp outnumbered likes.', $result->text);
        $this->dumpRenderedInsight($result, $instance, "");

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        TimeHelper::setTime(4); //set headline to expect
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Jo ThinkUp really inspired conversations", $result->headline);
        $this->assertEqual('Jo ThinkUp got more comments than anything else.', $result->text);
        $this->dumpRenderedInsight($result, $instance, "");

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        TimeHelper::setTime(5); //set headline to expect
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Whatever Jo ThinkUp said must have been memorable", $result->headline);
        $this->assertEqual('In the past week, Jo ThinkUp got 12 likes, beating out 8 reshares.', $result->text);
        $this->dumpRenderedInsight($result, $instance, "");

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Whatever Jo ThinkUp said must have been memorable", $result->headline);
        $this->assertEqual('In the past week, Jo ThinkUp got 12 likes.', $result->text);
        $this->dumpRenderedInsight($result, $instance, "");

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 1,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 1,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 1,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $posts[] = new Post(array(
            'reply_count_cache' => 1,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        ));
        $insight_plugin->generateInsight($instance, null, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Whatever Jo ThinkUp said must have been memorable", $result->headline);
        $this->assertEqual('In the past week, Jo ThinkUp got 12 likes, beating out 4 comments and 8 reshares.',
            $result->text);

        $this->dumpRenderedInsight($result, $instance, "");
    }
}
