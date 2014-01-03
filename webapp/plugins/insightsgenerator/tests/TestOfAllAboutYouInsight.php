<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfAllAboutYouInsight.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * Test of AllAboutYouInsight
 *
 * Test for the AllAboutYouInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/allaboutyou.php';

class TestOfAllAboutYouInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testCountFirstPersonReferences() {
        $count = AllAboutYouInsight::countFirstPersonReferences("I don't know, really? I thought so.");
        $this->assertEqual($count, 2);

        $count = AllAboutYouInsight::countFirstPersonReferences(
        "Now that I'm back on Android, realizing just how under sung Google Now is. I want it everywhere.");
        $this->assertEqual($count, 2);

        $count = AllAboutYouInsight::countFirstPersonReferences(
        "New YearÕs Eve! Feeling very gay today, but not very homosexual.");
        $this->assertEqual($count, 0);

        $count = AllAboutYouInsight::countFirstPersonReferences("Tis the season for adorable cards w/ photos of my ".
        "friends' kids & pets that remind me what I'd do for the holidays if I had my act together.");
        $this->assertEqual($count, 5);

        $count = AllAboutYouInsight::countFirstPersonReferences("Took 1 firearms safety class to realize my ".
        "fantasy of stopping an attacker was just that: http://bit.ly/mybH2j  Slate: http://slate.me/T6vwde");
        $this->assertEqual($count, 1);

        $count = AllAboutYouInsight::countFirstPersonReferences("When @anildash told me he was writing this I was ".
        "like 'yah whatever cool' then I read it and it knocked my socks off http://bit.ly/W9ASnj ");
        $this->assertEqual($count, 4);
    }

    public function testAllAboutYouInsightNoPriorBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new AllAboutYouInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('all_about_you', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        if (strpos($result->headline, 'vanity')) {
            $this->assertPattern('/There could be no extreme vanity in my recognition of myself, if in fact there could be any at all./', $result->headline);
        } else {
            $this->assertPattern('/But enough about me&hellip;/', $result->headline);
        }
    }

    public function testAllAboutYouInsightPriorGreaterBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new AllAboutYouInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'all_about_you',
        'instance_id'=>10, 'value'=>19));
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('all_about_you', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        if (strpos($result->headline, 'vanity')) {
            $this->assertPattern('/There could be no extreme vanity in my recognition of myself, if in fact there could be any at all./', $result->headline);
        } else {
            $this->assertPattern('/But enough about me&hellip;/', $result->headline);
        }
        $this->assertPattern('/10 fewer times than the prior week/', $result->text);
    }

    public function testAllAboutYouInsightPriorGreaterBy1Baseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new AllAboutYouInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'all_about_you',
        'instance_id'=>10, 'value'=>10));
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('all_about_you', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        if (strpos($result->headline, 'vanity')) {
            $this->assertPattern('/There could be no extreme vanity in my recognition of myself, if in fact there could be any at all./', $result->headline);
        } else {
            $this->assertPattern('/But enough about me&hellip;/', $result->headline);
        }
        $this->assertPattern('/\@testeriffic\'s tweets contained the words/', $result->text);
        $this->assertPattern('/9 times/', $result->text);
        $this->assertPattern('/1 fewer time than the prior week/', $result->text);
    }

    public function testAllAboutYouInsightPriorSmallerBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new AllAboutYouInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'all_about_you',
        'instance_id'=>10, 'value'=>7));
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('all_about_you', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s tweets contained the words/', $result->text);
        $this->assertPattern('/9 times/', $result->text);
        $this->assertPattern('/2 more times than the prior week/', $result->text);
    }

    public function testAllAboutYouInsightPriorSmallerByOneBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new AllAboutYouInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'all_about_you',
        'instance_id'=>10, 'value'=>8));
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('all_about_you', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s tweets contained the words/', $result->text);
        $this->assertPattern('/9 times/', $result->text);
        $this->assertPattern('/1 more time than the prior week/', $result->text);
    }

    public function testAllAboutYouInsightPriorEqualBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new AllAboutYouInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'all_about_you',
        'instance_id'=>10, 'value'=>9));
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('all_about_you', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s tweets contained the words/', $result->text);
        $this->assertPattern('/9 times/', $result->text);
        //assert no comparison to prior week
        $this->assertNoPattern('/prior week/', $result->text);
        $this->assertNoPattern('/prior week/', $result->text);
    }

    /**
     * Get test post objects
     * @return array of post objects for use in testing
     */
    private function getTestPostObjects() {
        $post_text_arr = array();
        $post_text_arr[] = "I don't know, really? I thought so.";
        $post_text_arr[] = "Now that I'm back on Android, realizing just how under sung Google Now is. ".
        "I want it everywhere.";
        $post_text_arr[] = "New YearÕs Eve! Feeling very gay today, but not very homosexual.";
        $post_text_arr[] = "Took 1 firearms safety class to realize my ".
        "fantasy of stopping an attacker was just that: http://bit.ly/mybH2j  Slate: http://slate.me/T6vwde";
        $post_text_arr[] = "When @anildash told me he was writing this I was ".
        "like 'yah whatever cool' then I read it and it knocked my socks off http://bit.ly/W9ASnj ";

        $posts = array();
        foreach ($post_text_arr as $test_text) {
            $p = new Post();
            $p->post_text = $test_text;
            $posts[] = $p;
        }
        return $posts;
    }
}
