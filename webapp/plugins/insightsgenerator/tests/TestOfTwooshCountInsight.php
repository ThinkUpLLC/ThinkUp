<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfTwooshCountInsight.php
 *
 * Copyright (c) Gareth Brady
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
 * Test of Twitter Ratios Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Gareth Brady
 * @author Gareth Brady <gareth.brady92[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.CriteriaMatchInsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/twooshcount.php';

class TestOfTwooshCountInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'janesmith';
        $instance->network = 'twitter';
        $instance->crawler_last_run = '2014-05-27 15:33:07';
        $this->instance = $instance;
        TimeHelper::setTime(3);
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new TwooshCountInsight();
        $this->assertIsA($insight_plugin, 'TwooshCountInsight' );
    }

    public function testNoTwoosh() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('pub_date' => date('Y-m-d', strtotime('-1 day')),
        'author_username'=> $this->instance->network_username, 'network' => $this->instance->network,
        'post_text' => "This is my first tweet."));

        $insight_plugin = new TwooshCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight("twoosh_count_weekly", 1, $today);
        $this->assertNull($result);
    }

    public function testTwoTwooshes() {
        TimeHelper::setTime(2);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $text = "This is just to show you what 120 letter characters looks like, this is all the space you have";
        $text .= " to compose your thoughts. Here's another 20..";
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('pub_date' => date('Y-m-d', strtotime('-1 day')),
        'author_username'=> $this->instance->network_username, 'network' => $this->instance->network,
        'post_text' => $text));
        $post_builders[] = FixtureBuilder::build('posts', array('pub_date' => date('Y-m-d', strtotime('-1 day')),
        'author_username'=> $this->instance->network_username, 'network' => $this->instance->network,
        'post_text' => $text));

        $insight_plugin = new TwooshCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight("twoosh_count_weekly", 1, $today);
        $this->assertEqual($result->headline, "Looks like there were some twooshes this week.");
        $text = "";
        $this->assertEqual($result->text, "@janesmith posted 2 tweets this week that were exactly 140 characters.");
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testTwooshesHeadlineTest1() {
        TimeHelper::setTime(1);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $text = "This is just to show you what 120 letter characters looks like, this is all the space you have";
        $text .= " to compose your thoughts. Here's another 20..";
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('pub_date' => date('Y-m-d', strtotime('-1 day')),
        'author_username'=> $this->instance->network_username, 'network' => $this->instance->network,
        'post_text' => $text));
        $post_builders[] = FixtureBuilder::build('posts', array('pub_date' => date('Y-m-d', strtotime('-1 day')),
        'author_username'=> $this->instance->network_username, 'network' => $this->instance->network,
        'post_text' => $text));

        $insight_plugin = new TwooshCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight("twoosh_count_weekly", 1, $today);
        $this->assertEqual($result->headline, "@janesmith had some twooshes this week.");
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testTwooshesHeadlineTest2() {
        TimeHelper::setTime(3);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $text = "This is just to show you what 120 letter characters looks like, this is all the space you have";
        $text .= " to compose your thoughts. Here's another 20..";
        $post_builders = array();
        $post_builders[] = FixtureBuilder::build('posts', array('pub_date' => date('Y-m-d', strtotime('-1 day')),
        'author_username'=> $this->instance->network_username, 'network' => $this->instance->network,
        'post_text' => $text));
        $post_builders[] = FixtureBuilder::build('posts', array('pub_date' => date('Y-m-d', strtotime('-1 day')),
        'author_username'=> $this->instance->network_username, 'network' => $this->instance->network,
        'post_text' => $text));

        $insight_plugin = new TwooshCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight("twoosh_count_weekly", 1, $today);
        $this->assertEqual($result->headline, "TWOOSH!");
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    
}