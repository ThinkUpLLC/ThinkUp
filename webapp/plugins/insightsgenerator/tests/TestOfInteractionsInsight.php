<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfInteractionsInsight.php
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
 * Test of InteractionsInsight
 *
 * Test for the InteractionsInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013-2014 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/interactions.php';

class TestOfInteractionsInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testInteractionsInsightText() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne @mentionTwo blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne @mentionTwo blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionThree blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 2
        ));

        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1', 'user_name'=>'mentionOne',
            'full_name'=>'Twitter Test User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
            'network'=>'twitter', 'description'=>'A test Twitter user'));

        $insight_plugin = new InteractionsInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("interactions", 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic replied to \@mentionOne /', $result->headline);
        $this->assertPattern('/\@mentionOne <strong>4 times<\/strong> last week./', $result->headline);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testOneInteractionsNoInsight() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @lonelyfriend blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @lonelyfriend blah again",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1
        ));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1', 'user_name'=>'mentionOne',
            'full_name'=>'Twitter Test User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
            'network'=>'twitter', 'description'=>'A test Twitter user'));

        $insight_plugin = new InteractionsInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // With only one mention, should be no insight
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("interactions", 10, $today);
        $this->assertNull($result);
    }

    public function testInteractionsInsightRelatedData() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne @mentionTwo blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionTwo blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 2
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne @mentionThree blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 2
        ));
        $posts[] = new Post(array(
            'post_text' => "@mentionOne, you are the best!",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1
        ));
        $posts[] = new Post(array(
            'post_text' => "@mentionOne, one more thing!",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1
        ));
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1', 'user_name'=>'mentionOne',
            'full_name'=>'Twitter Test User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
            'network'=>'twitter', 'description'=>'A test Twitter user'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'2', 'user_name'=>'mentionTwo',
            'full_name'=>'Twitter Test User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
            'network'=>'twitter', 'description'=>'A test Twitter user'));

        $insight_plugin = new InteractionsInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("interactions", 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $dataset = unserialize($result->related_data.people);
        $this->assertEqual($dataset["people"][0]['mention'], '@mentionOne');
        $this->assertEqual($dataset["people"][0]['count'], 4);
        $this->assertEqual($dataset["people"][1]['mention'], '@mentionTwo');
        $this->assertEqual($dataset["people"][1]['count'], 2);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
}

    public function testInteractionsInsightTextWithMetweets() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network_user_id = 99;
        $instance->network = 'twitter';

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne @testeriffic blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1,
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne blah @testeriffic",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1,
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne blah @testeriffic!",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1,
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne blah @testeriffic?",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1,
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @testeriffic blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 99,
        ));
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1', 'user_name'=>'mentionOne',
            'full_name'=>'Twitter Test User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
            'network'=>'twitter', 'description'=>'A test Twitter user'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'99', 'user_name'=>'testeriffic',
            'full_name'=>'Twitter Test User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
            'network'=>'twitter', 'description'=>'A test Twitter user'));

        $insight_plugin = new InteractionsInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("interactions", 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic replied to /', $result->headline);
        $this->assertPattern('/\@mentionOne <strong>4 times/', $result->headline);
        $data = unserialize($result->related_data);
        $this->assertEqual($data['milestones']['items'][0]['number'], 1);
        $this->assertEqual($data['milestones']['items'][0]['label'], 'minute');
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testInteractionsInsightMentionCasesIgnored() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612345', 'user_name'=>'TwitterTestUser',
        'full_name'=>'Twitter Test User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter user'));

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @TwitterTestUser blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 4,
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @Twittertestuser blah blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 4,
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah @twitterTestUser blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 4,
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah @tWiTTerTeSTusEr blah bleh",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 4,
        ));
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'4', 'user_name'=>'TwitterTestUser',
            'full_name'=>'Twitter Test User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
            'network'=>'twitter', 'description'=>'A test Twitter user'));

        $insight_plugin = new InteractionsInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("interactions", 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $dataset = unserialize($result->related_data.people);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic replied to /', $result->headline);
        $this->assertPattern('/\@TwitterTestUser <strong>4 times/', $result->headline);
        $this->assertPattern('/avatar.jpg/', $result->header_image);
        $this->assertEqual($dataset["people"][0]['mention'], '@TwitterTestUser');
        $this->assertEqual($dataset["people"][0]['count'], 4);
        $this->assertEqual($dataset["people"][0]['user']->full_name, "Twitter Test User");
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testInteractionsInsightTextFacebook() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'Talky Person';
        $instance->network = 'facebook';

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne @mentionTwo blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1
        ));
        $posts[] = new Post(array(
            'post_text' => "Blah blah bleh @mentionOne blah",
            'pub_date' => date("Y-m-d H:i:s",strtotime('-2 days')),
            'in_reply_to_user_id' => 1
        ));

        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1', 'user_name'=>'Bob Mentioned',
            'full_name'=>'Twitter Test User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
            'network'=>'facebook', 'description'=>'A test Twitter user'));

        $insight_plugin = new InteractionsInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight("interactions", 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/Talky Person replied to Bob Mentioned /', $result->headline);
        $this->assertPattern('/Bob Mentioned <strong>twice<\/strong> last week./', $result->headline);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }
}
