<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfMetweetInsight.php
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
 * Test of MetweetInsight
 *
 * Test for the MetweetInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/metweet.php';

class TestOfMetweetInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testMetweetInsightNoPriorBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new MetweetInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('metweet', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic retweeted \@testeriffic mentions <strong>5 times/', $result->headline);
        $this->assertPattern('/<strong>5 times<\/strong> last week./', $result->headline);
        $this->assertPattern('/It\'s cool to let people know what others are saying, but too many can get annoying./', $result->text);
    }

    public function testMetweetInsightPriorGreaterBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new MetweetInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'metweet_count',
        'instance_id'=>10, 'value'=>11));
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('metweet', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic retweeted \@testeriffic mentions <strong>5 times/', $result->headline);
        $this->assertPattern('/<strong>5 times<\/strong> last week./', $result->headline);
        $this->assertPattern('/6 fewer times than the prior week./', $result->text);
    }

    public function testMetweetInsightPriorGreaterByOneBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new MetweetInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'metweet_count',
        'instance_id'=>10, 'value'=>6));
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('metweet', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic retweeted \@testeriffic mentions <strong>5 times/', $result->headline);
        $this->assertPattern('/<strong>5 times<\/strong> last week./', $result->headline);
        $this->assertPattern('/1 fewer time than the prior week./', $result->text);
    }

    public function testMetweetInsightPriorSmallerBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new MetweetInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'metweet_count',
        'instance_id'=>10, 'value'=>3));
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('metweet', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic retweeted \@testeriffic mentions <strong>5 times/', $result->headline);
        $this->assertPattern('/<strong>5 times<\/strong> last week./', $result->headline);
        $this->assertPattern('/2 more times than the prior week./', $result->text);
    }

    public function testMetweetInsightPriorSmallerByOneBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new MetweetInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'metweet_count',
        'instance_id'=>10, 'value'=>4));
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('metweet', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic retweeted \@testeriffic mentions <strong>5 times/', $result->headline);
        $this->assertPattern('/<strong>5 times<\/strong> last week./', $result->headline);
        $this->assertPattern('/1 more time than the prior week./', $result->text);
    }

    public function testMetweetInsightPriorEqualBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new MetweetInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'metweet_count',
        'instance_id'=>10, 'value'=>5));
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('metweet', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic retweeted \@testeriffic mentions <strong>5 times/', $result->headline);
        $this->assertPattern('/<strong>5 times<\/strong> last week./', $result->headline);
        //assert no comparison to prior week
        $this->assertNoPattern('/prior week/', $result->text);
    }

    /**
     * Get test post objects
     * @return array of post objects for use in testing
     */
    private function getTestPostObjects() {
        $post_text_arr = array();
        $post_text_arr[] = "I was out today with @testeriffic.";
        $post_text_arr[] = "This is how I feel after sharing a bag of peanut m&ms ".
        "with @testeriffic at the fireworks. http://pic.twitter.com/BHGfKa9Q1u";
        $post_text_arr[] = ".@testeriffic stop trying to figure everything out, and let God work in your life.";
        $post_text_arr[] = "So @testeriffic's new article is out!";
        $post_text_arr[] = "What @testeriffic once told me. http://pic.twitter.com/6qTbTsh4wr";

        $posts = array();
        foreach ($post_text_arr as $test_text) {
            $p = new Post();
            $p->post_text = $test_text;
            $p->in_retweet_of_post_id = rand();
            $posts[] = $p;
        }

        $p = new Post();
        $p->post_text = "@testeriffic did not retweet this.";
        $posts[] = $p;

        return $posts; // 6 posts, 5 metweets
    }
}
