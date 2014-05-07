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
 * Test for FollowCountViusalizer class.
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

class TestOfFollowCountVisualizerInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();

        $this->instance = new Instance();
        $this->instance->id = 10;
        $this->instance->network_username = 'mario';
        $this->instance->network = 'twitter';
        TimeHelper::setTime(1);
    }

    public function tearDown() {
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
        $this->assertEqual($result->headline, '56 people are following @mario.');
        $this->assertEqual($result->text, "That's how many high school students can sit on a yellow school bus.");
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['hero_image']);
        $this->assertEqual($data['hero_image']['url'],'https://www.thinkup.com/assets/images/insights/2014-05/bus.jpg');
        $this->assertEqual($data['hero_image']['img_link'],'http://example.com/tbd');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 56);
    }

    public function test56ExactlyAlternateHeadline() {
        TimeHelper::setTime(2);
        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(56), array(), 1);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('follow_count_visualizer', $this->instance->id, date('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, '@mario has reached 56 followers!');
        $this->assertEqual($result->text, "That's how many high school students can sit on a yellow school bus.");

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
        $this->assertEqual($result->headline, 'More than 56 people are following @mario.');
        $this->assertEqual($result->text, "That's how many high school students can sit on a yellow school bus.");
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['hero_image']);
        $this->assertEqual($data['hero_image']['url'],'https://www.thinkup.com/assets/images/insights/2014-05/bus.jpg');
        $this->assertEqual($data['hero_image']['img_link'],'http://example.com/tbd');

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
        $this->assertEqual($result->text, "That's how many high school students can sit on a yellow school bus.");
        $data = unserialize($result->related_data);
        $this->assertNotNull($data['hero_image']);
        $this->assertEqual($data['hero_image']['url'],'https://www.thinkup.com/assets/images/insights/2014-05/bus.jpg');
        $this->assertEqual($data['hero_image']['img_link'],'http://example.com/tbd');

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 56);
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
        $this->assertEqual($result->headline, 'More than 36,000 people are following @mario.');
        $this->assertEqual($result->text, "That's how many people ran the 2014 Boston Marathon.");

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 36000);
    }

    public function testWithDoubleTheMilestone() {
        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline('follower_vis_last_run', $this->instance->id, 600);

        $insight_plugin = new FollowCountVisualizerInsight();
        $insight_plugin->generateInsight($this->instance, $this->makeUser(30000), array(), 1);
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
        $this->assertEqual($result->headline, 'More than 50,000 people are following @mario.');
        $this->assertEqual($result->text, "That's enough people to fill the Roman Colosseum.");
        $data = unserialize($result->related_data);
        $this->assertNull($data['hero_image']); // This line needs to be removed when all milestones have images

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $latest = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $this->instance->id);
        $this->assertNotNull($latest);
        $this->assertEqual($latest->value, 50000);
    }

    private function makeUser($num_followers) {
        $user = new User();
        $user->username = $this->insight->network_username;
        $user->full_name = "Mario Nintendo";
        $user->user_id = 999;
        $user->description = "It's me, Mario!";
        $user->description = "It's me, Mario!";
        $user->verified = 1;
        $user->follower_count = $num_followers;
        return $user;
    }

}
