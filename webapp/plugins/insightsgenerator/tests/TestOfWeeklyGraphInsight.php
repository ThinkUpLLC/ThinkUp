<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfWeeklyGraphInsight.php
 *
 * Copyright (c) 2013-2014 Nilaksh Das, Gina Trapani
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
 * @copyright 2013-2014 Nilaksh Das, Gina Trapani
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

    public function testWeeklyGraphInsightForTwitter() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

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
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("What's up with @testeriffic's tweets.", $result->headline);
        $this->assertEqual('Whatever @testeriffic said in the past week must have been memorable &mdash; there were '
            . '19 favorites, beating out 7 replies and 7 retweets.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWeeklyGraphInsightForFacebook() {
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
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("What's up with tester_fb's status updates.", $result->headline);
        $this->assertEqual('Whatever tester_fb said in the past week must have been memorable '.
            '&mdash; there were 19 likes, beating out 10 comments.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWeeklyGraphInsightWithOneReply() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>32, 'instance_id'=>10,
        'slug'=> 'PostMySQLDAO::getHotPosts', 'date'=>'-1d', 'related_data'=>serialize('sample hot posts data') ));

        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 1,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 5

        TimeHelper::setTime(1); //use the first possible headline
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("What's up with @testeriffic's tweets.", $result->headline);
        $this->assertEqual('@testeriffic really inspired conversations in the past week &mdash; '
            . 'replies outnumbered favorites or retweets.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWeeklyGraphInsightWithFavorites() {
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

        TimeHelper::setTime(1); //use the first possible headline
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("What's up with @testeriffic's tweets.", $result->headline);
        $this->assertEqual('Whatever @testeriffic said in the past week must have been memorable &mdash; '.
            'there were 3 favorites.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWeeklyGraphInsightWithRepliesAndFavorites() {
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

        TimeHelper::setTime(1); //use the first possible headline
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("What's up with @testeriffic's tweets.", $result->headline);
        $this->assertEqual('Whatever @testeriffic said in the past week must have been memorable &mdash; '
            .'there were 5 favorites, beating out 4 replies.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }


    public function  testForCommas() {
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

        TimeHelper::setTime(1); //use the first possible headline
        $insight_plugin = new WeeklyGraphInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("What's up with @testeriffic's tweets.", $result->headline);
        $this->assertEqual('@testeriffic shared a lot of things people wanted to amplify in the past week. '
            . 'Retweets outnumbered replies by 5,820 and favorites by 8,765.', $result->text);


        $posts = array();
        $posts[] = new Post(array(
            'reply_count_cache' => 4179,
            'retweet_count_cache' => 4242,
            'favlike_count_cache' => 999999,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        )); // popularity_index = 30

        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("What's up with @testeriffic's tweets.", $result->headline);
        $this->assertEqual('Whatever @testeriffic said in the past week must have been memorable &mdash; '
            . 'there were 999,999 favorites, beating out 4,179 replies and 4,242 retweets.', $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
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
        $insight_plugin->generateInsight($instance, $posts, 3);
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertNull($result);
    }

    public function testForAtLeastThreePosts() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $posts = array();
        for ($i=0; $i<=2; $i++) {
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
        $insight_plugin->generateInsight($instance, $posts, 3);
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);

        $data = unserialize($result->related_data);
        $this->assertNull($data['posts']);
        $this->assertEqual("What's up with @testeriffic's tweets.", $result->headline);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
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
        $insight_plugin->generateInsight($instance, $posts, 3);
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

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testInsightTextsTwitter() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $insight_plugin = new WeeklyGraphInsight();

        $posts = array(new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        TimeHelper::setTime(1); //set headline to expect
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("What's up with @testeriffic's tweets.", $result->headline);
        $this->assertEqual('@testeriffic really inspired conversations in the past week &mdash; replies outnumbered '
            .'favorites or retweets.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));

        $posts = array(new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        TimeHelper::setTime(2); //set headline to expect
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("What's happening with @testeriffic's tweets?", $result->headline);
        $this->assertEqual('@testeriffic shared a lot of things people wanted to amplify in the past week. '.
            'Retweets outnumbered replies by 3 and favorites by 3.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));

        $posts = array(new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        TimeHelper::setTime(3); //set headline to expect
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Here's the deal with @testeriffic's tweets.", $result->headline);
        $this->assertEqual('@testeriffic really inspired conversations in the past week &mdash; '.
            'replies outnumbered favorites.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));

        $posts = array(new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        TimeHelper::setTime(4); //set headline to expect
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Last week in @testeriffic's tweets&hellip;", $result->headline);
        $this->assertEqual('@testeriffic really inspired conversations in the past week, getting more replies than '.
            'anything else.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));

        $posts = array(new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        TimeHelper::setTime(5); //set headline to expect
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("What's going on with @testeriffic's tweets.", $result->headline);
        $this->assertEqual('Whatever @testeriffic said in the past week must have been memorable &mdash; '.
            'there were 3 favorites, beating out 2 retweets.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));

        $posts = array(new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("What's going on with @testeriffic's tweets.", $result->headline);
        $this->assertEqual('Whatever @testeriffic said in the past week must have been memorable &mdash; there were '
            .'3 favorites.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));

        $posts = array(new Post(array(
            'reply_count_cache' => 1,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("What's going on with @testeriffic's tweets.", $result->headline);
        $this->assertEqual('Whatever @testeriffic said in the past week must have been memorable &mdash; '.
            'there were 3 favorites, beating out 1 reply and 2 retweets.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
    }

    public function testInsightTextsFacebook() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'Jo Thinkup';
        $instance->network = 'facebook';
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $insight_plugin = new WeeklyGraphInsight();

        $posts = array(new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 0,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        TimeHelper::setTime(1); //set headline to expect
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("What's up with Jo Thinkup's status updates.", $result->headline);
        $this->assertEqual('Jo Thinkup really inspired conversations in the past week &mdash; comments outnumbered '
            .'likes or reshares.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));

        $posts = array(new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        TimeHelper::setTime(2); //set headline to expect
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("What's happening with Jo Thinkup's status updates?", $result->headline);
        $this->assertEqual('Jo Thinkup shared a lot of things people wanted to amplify in the past week. '.
            'Reshares outnumbered comments by 3 and likes by 3.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));

        $posts = array(new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 0,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        TimeHelper::setTime(3); //set headline to expect
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Here's the deal with Jo Thinkup's status updates.", $result->headline);
        $this->assertEqual('Jo Thinkup really inspired conversations in the past week &mdash; '.
            'comments outnumbered likes.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));

        $posts = array(new Post(array(
            'reply_count_cache' => 3,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        TimeHelper::setTime(4); //set headline to expect
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("Last week in Jo Thinkup's status updates&hellip;", $result->headline);
        $this->assertEqual('Jo Thinkup really inspired conversations in the past week, getting more comments than '.
            'anything else.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));

        $posts = array(new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        TimeHelper::setTime(5); //set headline to expect
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("What's going on with Jo Thinkup's status updates.", $result->headline);
        $this->assertEqual('Whatever Jo Thinkup said in the past week must have been memorable &mdash; '.
            'there were 3 likes, beating out 2 reshares.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));

        $posts = array(new Post(array(
            'reply_count_cache' => 0,
            'retweet_count_cache' => 3,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("What's going on with Jo Thinkup's status updates.", $result->headline);
        $this->assertEqual('Whatever Jo Thinkup said in the past week must have been memorable &mdash; there were '
            .'3 likes.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));

        $posts = array(new Post(array(
            'reply_count_cache' => 1,
            'retweet_count_cache' => 2,
            'favlike_count_cache' => 3,
            'pub_date' => date('Y-m-d H:i:s', strtotime('-'.$days.' day'))
        )));
        $insight_plugin->generateInsight($instance, $posts, 3);
        $result = $insight_dao->getInsight('weekly_graph', 10, $today);
        $this->assertEqual("What's going on with Jo Thinkup's status updates.", $result->headline);
        $this->assertEqual('Whatever Jo Thinkup said in the past week must have been memorable &mdash; '.
            'there were 3 likes, beating out 1 comment and 2 reshares.', $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
    }
}
