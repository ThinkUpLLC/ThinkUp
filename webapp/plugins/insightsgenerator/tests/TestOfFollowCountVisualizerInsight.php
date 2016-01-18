<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfFollowCountVisualizerInsight.php
 *
 * Copyright (c) 2014-2016 Chris Moyer
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
 * @copyright 2014-2016 Chris Moyer
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

    public function testPassed56() {
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(57), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@mario's followers would fill a yellow school bus");
        $this->assertEqual($result->text,
            "@mario has 57 followers&mdash;and they wouldn't all fit on a 56-seat yellow bus.");
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['hero_image']);
        $this->assertEqual($data['hero_image']['url'],'https://www.thinkup.com/assets/images/insights/2014-05/bus.jpg');
        $this->assertEqual($data['hero_image']['img_link'],'https://www.flickr.com/photos/ivydawned/5460058051');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 56);
        $this->dumpRenderedInsight($result, $this->instance, __METHOD__);
    }

    public function testPassed115() {
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(117), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->debug($this->getRenderedInsightInHTML($result));

        $this->assertNotNull($result);
        $this->assertEqual($result->headline, '@mario has as many fans as the Rolling Stones');
        $this->assertEqual($result->text,
            "@mario has 117 followers, but only 115 people attended the Rolling Stones' first live performance.");
        $this->assertNotNull($result->related_data['hero_image']);
        $this->assertEqual($result->related_data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-05/stones.jpg');
        $this->assertEqual($result->related_data['hero_image']['img_link'],
            'https://www.flickr.com/photos/cr01/7392740268/');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 115);
        $this->dumpRenderedInsight($result, $this->instance);
    }

    public function testPassed200() {
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(201), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->debug($this->getRenderedInsightInHTML($result));

        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@mario's followers would pack a New York City subway");
        $this->assertEqual($result->text, "@mario has 201 followers, but only 200 people fit in a typical subway car.");
        $this->assertNotNull($result->related_data['hero_image']);
        $this->assertEqual($result->related_data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-05/subway.jpg');
        $this->assertEqual($result->related_data['hero_image']['img_link'],
            'https://www.flickr.com/photos/juliandunn/6920197196');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 200);
        $this->dumpRenderedInsight($result, $this->instance);
    }

    public function testPassed360() {
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(363), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->debug($this->getRenderedInsightInHTML($result));

        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@mario's followers outnumber the Mormon Tabernacle Choir");
        $this->assertEqual($result->text,
            "@mario has 363 followers, but there are only 360 singers in the Mormon Tabernacle Choir.");
        $this->assertNotNull($result->related_data['hero_image']);
        $this->assertEqual($result->related_data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-05/choir.jpg');
        $this->assertEqual($result->related_data['hero_image']['img_link'], '');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 360);
        $this->dumpRenderedInsight($result, $this->instance);
    }

    public function testJustPassed400() {
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(402), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->debug($this->getRenderedInsightInHTML($result));

        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@mario's followers would fill a 747");
        $this->assertEqual($result->text,
            "Some of @mario's 402 followers would have to go on standby, because they'd fill a 400-seat airplane "
            ."to capacity.");
        $this->assertNotNull($result->related_data['hero_image']);
        $this->assertEqual($result->related_data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-05/747.jpg');
        $this->assertEqual($result->related_data['hero_image']['img_link'],
            'https://www.flickr.com/photos/aero_icarus/4707805048/');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 400);
        $this->dumpRenderedInsight($result, $this->instance);
    }

    public function testJustPassed560() {
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(565), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->debug($this->getRenderedInsightInHTML($result));

        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@mario's followers would need more than 10 buses");
        $this->assertEqual($result->text,
            "@mario has 565 followers, but only 560 students would fill 10 yellow school buses.");
        $this->assertNotNull($result->related_data['hero_image']);
        $this->assertEqual($result->related_data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2014-05/buses.jpg');
        $this->assertEqual($result->related_data['hero_image']['img_link'],
            'https://www.flickr.com/photos/dhendrix/6906652333/');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 560);
        $this->dumpRenderedInsight($result, $this->instance);
    }

    public function testJustPassed12500() {
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(12600), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->debug($this->getRenderedInsightInHTML($result));

        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "@mario's followers would fill Wembley Arena");
        $this->assertEqual($result->text,
            "@mario has 12,600 followers, but there are only 12,500 seats at Wembley Arena.");
        $this->assertNotNull($result->related_data['hero_image']);
        $this->assertEqual($result->related_data['hero_image']['url'],
            'https://www.thinkup.com/assets/images/insights/2015-02/wembley.jpg');
        $this->assertEqual($result->related_data['hero_image']['img_link'],
            'https://www.flickr.com/photos/tim_uk/10353361694');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 12500);
        $this->dumpRenderedInsight($result, $this->instance);
    }

    public function testRenderingAllVisualizations() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $insight_plugin = new FollowCountVisualizerInsight();

        foreach ($insight_plugin->milestones as $milestone=>$copy) {
            $insight_plugin->generateInsight($this->instance, $this->makeUser($milestone+17), array(), 1);
            $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
            $this->dumpRenderedInsight($result, $this->instance, __METHOD__);
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
        $this->assertEqual($result->headline, "@mario's followers outnumber Boston Marathon runners");
        $this->assertEqual($result->text,
            "@mario has 36,001 followers, but only 36,000 people ran the 2014 Boston Marathon.");

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
        $this->assertEqual($result->headline, "@mario's followers would fill Yankee Stadium");
        $this->assertEqual($result->text,
            "@mario has 50,001 followers, but only 50,000 fans can fit in Yankee Stadium.");

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
