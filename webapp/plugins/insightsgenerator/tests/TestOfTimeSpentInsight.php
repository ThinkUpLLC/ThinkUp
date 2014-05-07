<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfTimeSpentInsight.php
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
 * Test of TimeSpent Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/timespent.php';

class TestOfTimeSpentInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        $this->insight_dao = DAOFactory::getDAO('InsightDAO');
        $this->baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testOfFirstRunTwitter() {
        $posts = array(new Post());
        $instance = new Instance();
        $instance->id = 10;
        $instance->network = 'twitter';
        $instance->network_username = 'davidlister';
        $instance->total_posts_in_system = 1234;
        $user = new User();
        $user->post_count = 4179;

        $insight_plugin = new TimeSpentInsight();
        $insight_plugin->generateInsight($instance, $user, $posts, 3);
        $result = $this->insight_dao->getInsight('time_spent', 10, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('@davidlister has tweeted 4,179 times.', $result->headline);
        $this->assertEqual("That's over<strong> 17 hours 24 minutes</strong> of @davidlister's life.", $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
        $result = $this->baseline_dao->getInsightBaseline('time_spent_41', 10);
        $this->assertNotNull($result);
        $this->assertEqual($result->value, 41);
    }

    public function testOfTwitterNotPassed100() {
        $posts = array(new Post());
        $instance = new Instance();
        $instance->id = 10;
        $instance->network = 'twitter';
        $instance->network_username = 'davidlister';
        $instance->total_posts_in_system = 1234;
        $user = new User();
        $user->post_count = 4183;

        $this->baseline_dao->insertInsightBaseline('time_spent_41', $instance->id, 41);

        $insight_plugin = new TimeSpentInsight();
        $insight_plugin->generateInsight($instance, $user, $posts, 3);
        $result = $this->insight_dao->getInsight('time_spent', 10, date('Y-m-d'));
        $this->assertNull($result);

        $result = $this->baseline_dao->getInsightBaseline('time_spent_41', 10);
        $this->assertNotNull($result);
        $this->assertEqual($result->value, 41);
    }

    public function testOfTwitterPassed100() {
        $posts = array(new Post());
        $instance = new Instance();
        $instance->id = 10;
        $instance->network = 'twitter';
        $instance->network_username = 'davidlister';
        $instance->total_posts_in_system = 1234;
        $user = new User();
        $user->post_count = 4283;

        $this->baseline_dao->insertInsightBaseline('time_spent_41', $instance->id, 41);

        $insight_plugin = new TimeSpentInsight();
        $insight_plugin->generateInsight($instance, $user, $posts, 3);
        $result = $this->insight_dao->getInsight('time_spent', 10, date('Y-m-d'));
        $this->assertEqual('@davidlister has tweeted 4,283 times.', $result->headline);
        $this->assertEqual("That's over<strong> 17 hours 50 minutes</strong> of @davidlister's life.", $result->text);

        $result = $this->baseline_dao->getInsightBaseline('time_spent_42', 10);
        $this->assertNotNull($result);
        $this->assertEqual($result->value, 42);
    }

    public function testOfFacebookTooFewPosts() {
        $posts = array(new Post(), new Post());
        $instance = new Instance();
        $instance->id = 10;
        $instance->network = 'facebook';
        $instance->network_username = 'David Lister';
        $instance->network_user_id= 99;
        $instance->total_posts_in_system = 1234;
        $user = new User();
        $user->post_count = 123;

        $post_builders = array();
        for ($i=1; $i<11; $i++) {
            $post_builder[] = $post_builder5 = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i,
                'pub_date' => date('-3 day'),
                'author_username'=>'David Lister', 'author_user_id' => 99));
        }

        $insight_plugin = new TimeSpentInsight();
        $insight_plugin->generateInsight($instance, $user, $posts, 3);
        $result = $this->insight_dao->getInsight('time_spent', 10, date('Y-m-d'));
        $this->assertNull($result);
    }

    public function testOfFirstRunFacebook() {
        $posts = array(new Post(), new Post(), new Post(), new Post(), new Post(), new Post());
        $instance = new Instance();
        $instance->id = 10;
        $instance->network = 'facebook';
        $instance->network_username = 'David Lister';
        $instance->network_user_id= 99;
        $instance->total_posts_in_system = 1234;
        $user = new User();
        $user->post_count = 123;

        $post_builders = array();
        for ($i=1; $i<11; $i++) {
            $post_builder[] = $post_builder5 = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i,
                'pub_date' => date('Y-m-d', strtotime('-3 day')),
                'author_username'=>'David Lister', 'author_user_id' => 99));
        }

        $insight_plugin = new TimeSpentInsight();
        $insight_plugin->generateInsight($instance, $user, $posts, 3);
        $result = $this->insight_dao->getInsight('time_spent', 10, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('David Lister posted 6 times in the past week.', $result->headline);
        $this->assertEqual("That's over<strong> 1 minute</strong> of David Lister's life.", $result->text);
        $this->debug($this->getRenderedInsightInHTML($result));
    }

    public function testOfSubsequentRunFacebook() {
        $insight = new Insight();
        $insight->slug = 'time_spent';
        $insight->instance_id = 10;
        $insight->filename = 'timespent.php';
        $insight->date = date('Y-m-d', strtotime('-10 day'));
        $insight->text = "I'm here to make this not a first run.";
        $insight->headline = "Yay!";
        $this->insight_dao->insertInsight($insight);

        $posts = array(new Post(), new Post(), new Post(), new Post(), new Post(), new Post());
        $instance = new Instance();
        $instance->id = 10;
        $instance->network = 'facebook';
        $instance->network_username = 'David Lister';
        $instance->network_user_id = 99;
        $instance->total_posts_in_system = 1234;
        $user = new User();
        $user->post_count = 123;

        $post_builders = array();
        for ($i=1; $i<11; $i++) {
            $post_builder[] = FixtureBuilder::build('posts', array('id'=>$i, 'post_id'=>$i,
                'pub_date' => date('Y-m-d', strtotime('-3 day')), 'network' => 'facebook',
                'author_username'=>'David Lister', 'author_user_id' => 99));
        }

        $insight_plugin = new TimeSpentInsight();
        $insight_plugin->generateInsight($instance, $user, $posts, 3);
        $result = $this->insight_dao->getInsight('time_spent', 10, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual('David Lister posted 10 times in the past month.', $result->headline);
        $this->assertEqual("That's over<strong> 2 minutes</strong> of David Lister's life.", $result->text);
    }
}
