<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYTopStoriesInsight.php
 *
 * Copyright (c) 2012-2015 Gina Trapani
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
 * Test for the EOYTopStoriesInsight class.
 *
 * Copyright (c) 2014-2015 Chris Moyer
 *
 * @author Chris Moyer chris@inarow.net
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2015 Adam Pash
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoytopstories.php';

class TestOfEOYTopStoriesInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'Anderson Cooper';
        $instance->author_id = '18';
        $instance->network_user_id = '18';
        $instance->network = 'facebook';
        $this->instance = $instance;
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testNoTopics() {
        $insight_plugin = new EOYTopStoriesInsight();
        $year = date('Y');
        $day = $year.'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Anderson Cooper didn't rehash $year's top news on Facebook");
        $this->assertEqual($result->text, "No Ice Bucket Challenge, Robin Williams, or Malaysia Airlines here. ".
            "Anderson Cooper broke away from the herd and avoided talking about 2014's biggest stories on Facebook ".
            "this year.");
        $data = unserialize($result->related_data);
        $this->assertNull($data['posts']);

        $this->dumpRenderedInsight($result, $this->instance, "No Topics");
    }

    public function testNoTopicsQualified() {
        $year = date('Y');
        $this->instance->last_post_id = '99999';
        $builders[] = FixtureBuilder::build('posts',
            array( 'post_id' => '99999', 'post_text' => 'This is my old post',
                'pub_date' => date('Y-m-d', strtotime('June 15')),
                'author_username' => $this->instance->network_username, 'network' => $this->instance->network,
                'favlike_count_cache' => 25
            )
        );

        $insight_plugin = new EOYTopStoriesInsight();
        $day = $year.'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Anderson Cooper didn't rehash $year's top news on Facebook");
        $this->assertEqual($result->text, "No Ice Bucket Challenge, Robin Williams, or Malaysia Airlines here. ".
            "Anderson Cooper broke away from the herd and avoided talking about 2014's biggest stories on Facebook ".
            "this year (at least since June).");
        $data = unserialize($result->related_data);
        $this->assertNull($data['posts']);

        $this->dumpRenderedInsight($result, $this->instance, "No Topics, Qualified");
    }

    public function testOneTopic() {
        $year = date('Y');
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => $year.'-02-01', 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'Are you ready for the Super Bowl?',
            )
        );
        $insight_plugin = new EOYTopStoriesInsight();
        $day = date('Y').'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Anderson Cooper was part of $year's biggest trends");
        $this->assertEqual($result->text, "Anderson Cooper's $year included the Super Bowl. That was one of "
            . '<a href="http://newsroom.fb.com/news/2014/12/2014-year-in-review/">Facebook\'s top topics of the year'
            . '</a> &mdash; that\'s so '.$year.'!');
        $data = unserialize($result->related_data);
        $this->assertEqual(1, count($data['posts']));

        $this->dumpRenderedInsight($result, $this->instance, "One Topic");
    }

    public function testTwoTopics() {
        $year = date('Y');
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => "$year-02-01", 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'I wonder if the gang will survive on walking dead.',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => "$year-02-05", 'post_id' => 2, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'I am really sad about Robin Williams.',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => "$year-01-02", 'post_id' => 3, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'The Walking Dead is my favorite show!',
            )
        );
        $insight_plugin = new EOYTopStoriesInsight();
        $day = date('Y').'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Anderson Cooper was part of $year's biggest trends");
        $this->assertEqual($result->text, "Anderson Cooper's $year included The Walking Dead and "
            . "Robin Williams. Those were some of <a href=\"http://newsroom.fb.com/news/2014/12/2014-year-in-"
            . "review/\">Facebook's top topics of the year</a> &mdash; that's so $year!");
        $data = unserialize($result->related_data);
        $this->assertEqual(2, count($data['posts']));
        $this->assertEqual($data['posts'][0]->post_text, 'The Walking Dead is my favorite show!');
        $this->assertEqual($data['posts'][1]->post_text, 'I am really sad about Robin Williams.');

        $this->dumpRenderedInsight($result, $this->instance, "Two Topics");
    }

    public function testThreeTopics() {
        $year = date('Y');
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => "$year-02-01", 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'The World Cup is a soccer thing, I guess.',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => "$year-04-05", 'post_id' => 2, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'I just poured an Ice Bucket on my head.',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => "$year-01-01", 'post_id' => 3, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'MH370 is still missing.',
            )
        );
        $insight_plugin = new EOYTopStoriesInsight();
        $day = date('Y').'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Anderson Cooper was part of $year's biggest trends");
        $this->assertEqual($result->text, "Anderson Cooper's $year included MH370, the World Cup, and the Ice "
            . "Bucket Challenge. Those were some of <a href=\"http://newsroom.fb.com/news/2014/12/2014-year-in-"
            . "review/\">Facebook's top topics of the year</a> &mdash; that's so $year!");
        $data = unserialize($result->related_data);
        $this->assertEqual(3, count($data['posts']));
        $this->assertEqual($data['posts'][0]->post_text, 'MH370 is still missing.');
        $this->assertEqual($data['posts'][1]->post_text, 'The World Cup is a soccer thing, I guess.');
        $this->assertEqual($data['posts'][2]->post_text, 'I just poured an Ice Bucket on my head.');

        $this->dumpRenderedInsight($result, $this->instance, "Three Topics");
    }

    public function testFourTopics() {
        $year = date('Y');
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => "$year-02-01", 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'World Cup!!!!',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => "$year-03-01", 'post_id' => 2, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'Super Bowl!!!!',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => "$year-04-05", 'post_id' => 3, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'Robin Williams. ;(',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => "$year-01-01", 'post_id' => 4, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'MH370?',
            )
        );
        $insight_plugin = new EOYTopStoriesInsight();
        $day = date('Y').'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Anderson Cooper was part of $year's biggest trends");
        $this->assertEqual($result->text, "Anderson Cooper's $year included MH370, the World Cup, the Super Bowl, and "
            . "Robin Williams. Those were some of <a href=\"http://newsroom.fb.com/news/2014/12/2014-year-in-"
            . "review/\">Facebook's top topics of the year</a> &mdash; that's so $year!");
        $data = unserialize($result->related_data);
        $this->assertEqual(3, count($data['posts']));
        $this->assertEqual($data['posts'][0]->post_text, 'MH370?');
        $this->assertEqual($data['posts'][1]->post_text, 'World Cup!!!!');
        $this->assertEqual($data['posts'][2]->post_text, 'Super Bowl!!!!');

        $this->dumpRenderedInsight($result, $this->instance, "Four Topics (three posts)");
    }

    public function testSameTopicMultipleTerms() {
        $year = date('Y');
        $builders = array();
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => "$year-02-01", 'post_id' => 1, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'Super Bowl!!!',
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
                'pub_date' => "$year-01-11", 'post_id' => 2, 'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id, 'network' => $this->instance->network,
                'post_text' => 'Super bowl, tomorrow!',
            )
        );
        $insight_plugin = new EOYTopStoriesInsight();
        $day = date('Y').'-'.$insight_plugin->run_date;
        $insight_dao = new InsightMySQLDAO();
        $insight_plugin->generateInsight($this->instance, new User(), array(), 3);
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id, $day);
        $this->assertNotNull($result);

        $this->assertEqual($result->headline, "Anderson Cooper was part of $year's biggest trends");
        $this->assertEqual($result->text, "Anderson Cooper's $year included the Super Bowl. That was one of "
            . "<a href=\"http://newsroom.fb.com/news/2014/12/2014-year-in-review/\">Facebook's top topics of the "
            . "year</a> &mdash; that's so $year!");
        $data = unserialize($result->related_data);
        $this->assertEqual(1, count($data['posts']));
        $this->assertEqual($data['posts'][0]->post_text, 'Super bowl, tomorrow!');

        $this->dumpRenderedInsight($result, $this->instance, "One Topic, Mentioned multiple times");
    }
}
