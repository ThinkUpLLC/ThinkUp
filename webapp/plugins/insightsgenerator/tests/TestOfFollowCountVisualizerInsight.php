<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfFollowCountVisualizerInsight.php
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
 * Test of FollowCountVisualizerInsight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/followcountvisualizer.php';

class TestOfFollowCountVisualizerInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();

        $this->instance = new Instance();
        $this->instance->id = 10;
        $this->instance->network_user_id = 42;
        $this->instance->network_username = 'mario';
        $this->instance->network = 'twitter';
        $this->instance->is_public = 1;
        TimeHelper::setTime(1);

        $this->builders = array();
        $this->builders[] = FixtureBuilder::build('users',array('user_id'=>42,'network'=>'twitter',
            'avatar' => 'avatar.jpg', 'user_name' => 'mario', 'full_name' => 'Mario Nintendo',
            'follower_count' => 100));
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new FollowCountVisualizerInsight();
        $this->assertIsA($insight_plugin, 'FollowCountVisualizerInsight' );
    }

    public function testSub56() {
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(12), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->assertNull($result);

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $lastest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNull($latest);
    }

    public function test56Exactly() {
        TimeHelper::setTime(1);
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(56), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, '56 people follow @mario.');
        $this->assertEqual($result->text, "@mario's followers could fill a yellow school bus!");
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['hero_image']);
        $this->assertEqual($data['hero_image']['url'],'https://www.thinkup.com/assets/images/insights/2014-05/bus.jpg');
        $this->assertEqual($data['hero_image']['img_link'],'https://www.flickr.com/photos/ivydawned/5460058051');
        $this->debug($this->getRenderedInsightInHTML($result));

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 56);

        /**
         * Use this code to output the individual insight's fully-rendered email HTML to file.
         * Then, open the file in your browser to view.
         *
         * $ TEST_DEBUG=1 php webapp/plugins/insightsgenerator/tests/TestOfHelloThinkUpInsight.php
         * -t testHelloThinkUpInsight > webapp/insight_email.html
         */
        $email_insight = $this->getRenderedInsightInEmail($result);
        //Uncomment this out to see the email view of insight
        //$this->debug($email_insight);
    }

    public function test56ExactlyAlternateHeadline() {
        TimeHelper::setTime(2);
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(56), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, '@mario has reached 56 followers!');
        $this->assertEqual($result->text, "@mario's followers could fill a yellow school bus!");

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 56);

        TimeHelper::setTime(2);
    }

    public function testJustPassed56() {
        TimeHelper::setTime(1);
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(57), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'More than 56 people follow @mario.');
        $this->assertEqual($result->text, "@mario's followers could fill a yellow school bus!");
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['hero_image']);
        $this->assertEqual($data['hero_image']['url'],'https://www.thinkup.com/assets/images/insights/2014-05/bus.jpg');
        $this->assertEqual($data['hero_image']['img_link'],'https://www.flickr.com/photos/ivydawned/5460058051');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 56);
    }

    public function testJustPassed56AlternateHeadline() {
        TimeHelper::setTime(2);
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(57), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, '@mario has passed 56 followers!');
        $this->assertEqual($result->text, "@mario's followers could fill a yellow school bus!");
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['hero_image']);
        $this->assertEqual($data['hero_image']['url'],'https://www.thinkup.com/assets/images/insights/2014-05/bus.jpg');
        $this->assertEqual($data['hero_image']['img_link'],'https://www.flickr.com/photos/ivydawned/5460058051');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 56);
    }

    public function testJustPassed115() {
        TimeHelper::setTime(1);
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(117), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->debug($this->getRenderedInsightInHTML($result));

        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'More than 115 people follow @mario.');
        $this->assertEqual($result->text, "That's how many fans saw the Rolling Stones' first live performance!");
        $this->assertNotNull($result->related_data['hero_image']);
        $this->assertEqual($result->related_data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-05/stones.jpg');
        $this->assertEqual($result->related_data['hero_image']['img_link'],
            'https://www.flickr.com/photos/cr01/7392740268/');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 115);
    }

    public function testJustPassed200() {
        TimeHelper::setTime(1);
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(201), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->debug($this->getRenderedInsightInHTML($result));

        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'More than 200 people follow @mario.');
        $this->assertEqual($result->text, "@mario's followers could fill a New York City subway car!");
        $this->assertNotNull($result->related_data['hero_image']);
        $this->assertEqual($result->related_data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-05/subway.jpg');
        $this->assertEqual($result->related_data['hero_image']['img_link'],
            'https://www.flickr.com/photos/juliandunn/6920197196');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 200);
    }

    public function testJustPassed360() {
        TimeHelper::setTime(1);
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(363), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->debug($this->getRenderedInsightInHTML($result));

        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'More than 360 people follow @mario.');
        $this->assertEqual($result->text, "@mario's followers outnumber singers in the Mormon Tabernacle Choir!");
        $this->assertNotNull($result->related_data['hero_image']);
        $this->assertEqual($result->related_data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-05/choir.jpg');
        $this->assertEqual($result->related_data['hero_image']['img_link'], '');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 360);
    }

    public function testJustPassed400() {
        TimeHelper::setTime(1);
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(402), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->debug($this->getRenderedInsightInHTML($result));

        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'More than 400 people follow @mario.');
        $this->assertEqual($result->text, "@mario's followers could fill up a 747!");
        $this->assertNotNull($result->related_data['hero_image']);
        $this->assertEqual($result->related_data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-05/747.jpg');
        $this->assertEqual($result->related_data['hero_image']['img_link'],
            'https://www.flickr.com/photos/aero_icarus/4707805048/');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 400);
    }

    public function testJustPassed560() {
        TimeHelper::setTime(1);
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(565), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->debug($this->getRenderedInsightInHTML($result));

        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'More than 560 people follow @mario.');
        $this->assertEqual($result->text, "That's 10 school buses full of students!");
        $this->assertNotNull($result->related_data['hero_image']);
        $this->assertEqual($result->related_data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-05/buses.jpg');
        $this->assertEqual($result->related_data['hero_image']['img_link'],
            'https://www.flickr.com/photos/dhendrix/6906652333/');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 560);
    }

    public function testJustPassed12500() {
        TimeHelper::setTime(1);
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(12600), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->debug($this->getRenderedInsightInHTML($result));

        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'More than 12,500 people follow @mario.');
        $this->assertEqual($result->text, "@mario's followers could fill up Wembley Arena!");
        $this->assertNotNull($result->related_data['hero_image']);
        $this->assertEqual($result->related_data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-05/wembley.jpg');
        $this->assertEqual($result->related_data['hero_image']['img_link'],
            'https://www.flickr.com/photos/36593372@N04/8240126447');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 12500);
    }

    public function testRenderingAllVisualizations() {
        TimeHelper::setTime(1);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $insight_plugin = new FollowCountVisualizerInsight();

        foreach ($insight_plugin->milestones as $milestone=>$copy) {
            $insight_plugin->generateInsight($this->instance, $this->makeUser($milestone), array(), 1);
            $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
            $this->debug($this->getRenderedInsightInHTML($result));
        }
    }

    public function testWithExistingCurrentBaseline() {
        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('follower_vis_last_run', $this->instance->id, 12500);

        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(13000), array(), 1);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->assertNull($result);
    }

    public function testWithExistingPreviousBaseline() {
        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('follower_vis_last_run', $this->instance->id, 12500);

        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(36001), array(), 1);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'More than 36,000 people follow @mario.');
        $this->assertEqual($result->text, "That's how many runners were in the 2014 Boston Marathon!");

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 36000);
    }

    public function testWithDoubleTheMilestone() {
        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('follower_vis_last_run', $this->instance->id, 50000);

        $insight_plugin = new FollowCountVisualizerInsight();
        //More than double the next milestone (57000)
        $insight_plugin->generateInsight($this->instance, $this->makeUser(115000), array(), 1);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->assertNull($result);
    }

    public function testJumpingAhead() {
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(50001), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'More than 50,000 people follow @mario.');
        $this->assertEqual($result->text, "@mario's followers could fill Yankee Stadium!");

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 50000);
    }

    /**
     * Create a test user.
     * @param  int $num_followers
     * @return User
     */
    private function makeUser($num_followers) {
        $user = new User();
        $user->username = $this->insight->network_username;
        $user->full_name = "Mario Nintendo";
        $user->user_id = 999;
        $user->network = $this->insight->network;
        $user->description = "It's me, Mario!";
        $user->verified = 1;
        $user->follower_count = $num_followers;
        return $user;
    }
}
