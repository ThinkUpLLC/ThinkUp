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

class TestOfAllAboutYouInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $this->instance = $instance;
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testHasFirstPersonReferences() {
        $has = AllAboutYouInsight::hasFirstPersonReferences("I don't know, really? I thought so.");
        $this->assertTrue($has);

        $has = AllAboutYouInsight::hasFirstPersonReferences(
            "Now that I'm back on Android, realizing just how under sung Google Now is. I want it everywhere.");
        $this->assertTrue($has);

        $has = AllAboutYouInsight::hasFirstPersonReferences(
            "New YearÕs Eve! Feeling very gay today, but not very homosexual.");
        $this->assertFalse($has);

        $has = AllAboutYouInsight::hasFirstPersonReferences("Tis the season for adorable cards w/ photos of my ".
            "friends' kids & pets that remind me what I'd do for the holidays if I had my act together.");
        $this->assertTrue($has);

        $has = AllAboutYouInsight::hasFirstPersonReferences("Took 1 firearms safety class to realize my ".
            "fantasy of stopping an attacker was just that: http://bit.ly/mybH2j  Slate: http://slate.me/T6vwde");
        $this->assertTrue($has);

        $has = AllAboutYouInsight::hasFirstPersonReferences("When @anildash told me he was writing this I was ".
            "like 'yah whatever cool' then I read it and it knocked my socks off http://bit.ly/W9ASnj ");
        $this->assertTrue($has);
    }


    public function testAllAboutYouInsightNoPriorBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $insight_plugin = new AllAboutYouInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('all_about_you', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/<strong>80%</', $result->text);
        $this->assertNoPattern('/up/', $result->text);
        $this->assertNoPattern('/down/', $result->text);
        $this->debug($this->getRenderedInsightInEmail($result));

        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAllAboutYouInsightPriorGreaterBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $insight_plugin = new AllAboutYouInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'all_about_you_percent',
            'instance_id'=>10, 'value'=>99));
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('all_about_you', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/down 19 percentage points/', $result->text);

        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAllAboutYouInsightPriorGreaterBy1Baseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $insight_plugin = new AllAboutYouInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'all_about_you_percent',
        'instance_id'=>10, 'value'=>81));
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('all_about_you', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/down 1 percentage point /', $result->text);

        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAllAboutYouInsightPriorSmallerBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $insight_plugin = new AllAboutYouInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'all_about_you_percent',
        'instance_id'=>10, 'value'=>7));
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('all_about_you', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s tweets contained the words/', $result->text);
        $this->assertPattern('/<strong>80%</', $result->text);
        $this->assertPattern('/up 73 percentage points/', $result->text);
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAllAboutYouInsightPriorSmallerByOneBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $insight_plugin = new AllAboutYouInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'all_about_you_percent',
        'instance_id'=>10, 'value'=>79));
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('all_about_you', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s tweets contained the words/', $result->text);
        $this->assertPattern('/<strong>80%</', $result->text);
        $this->assertPattern('/up 1 percentage point /', $result->text);
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAllAboutYouInsightPriorEqualBaseline() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $insight_plugin = new AllAboutYouInsight();

        // Add a baseline from prior week
        $last_week = date('Y-m-d', strtotime('-7 day'));
        $builder = FixtureBuilder::build('insight_baselines', array('date'=>$last_week, 'slug'=>'all_about_you',
        'instance_id'=>10, 'value'=>9));
        $insight_plugin->generateInsight($this->instance, $posts, 3);

        // Assert that week-over-week comparison is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('all_about_you', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s tweets contained the words/', $result->text);
        $this->assertPattern('/<strong>80%</', $result->text);
        //assert no comparison to prior week
        $this->assertNoPattern('/prior week/', $result->text);
        $this->assertNoPattern('/up/', $result->text);
        $this->assertNoPattern('/down/', $result->text);
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testPercentRounding() {
        $today = date ('Y-m-d');
        $posts = self::getTestPostObjects();
        $p = new Post();
        $p->text = "This is not about the person posting.";
        $posts[] = $p;

        $insight_plugin = new AllAboutYouInsight();
        $insight_plugin->generateInsight($this->instance, $posts, 3);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('all_about_you', 10, $today);
        $this->assertPattern('/<strong>67%</', $result->text);
        $this->assertNoPattern('/66.6/', $result->text);

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline = $baseline_dao->getInsightBaseline('all_about_you_percent', $this->instance->id, $today);
        $this->assertEqual($baseline->value, 67);
        $this->assertEqual($baseline->slug, 'all_about_you_percent');
    }

    public function testHeadlines() {
        $posts = self::getTestPostObjects();
        $insight_plugin = new AllAboutYouInsight();
        $today = date ('Y-m-d');
        $insight_plugin = new AllAboutYouInsight();
        $insight_dao = DAOFactory::getDAO('InsightDAO');

        $good_headlines = array(
            null,
            'It\'s getting personal.',
            'But enough about me&hellip;',
            'Self-reflection is powerful stuff.',
            'Speaking from experience&hellip;',
            'Sometimes twitter is a first-person story.',
            'It\'s just me, myself and I.',
            '@testeriffic is getting personal.',
        );

        for ($i=1; $i<=7; $i++) {
            TimeHelper::setTime($i);
            $insight_plugin->generateInsight($this->instance, $posts, 3);
            $result = $insight_dao->getInsight('all_about_you', 10, $today);
            $this->assertEqual($result->headline, $good_headlines[$i]);
        }
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
