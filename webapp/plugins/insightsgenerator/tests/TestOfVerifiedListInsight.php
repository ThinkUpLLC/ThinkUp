<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfVerifiedListInsight.php
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
 * Test of VerifiedListInsight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/verifiedlist.php';

class TestOfVerifiedListInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 999999;
        $instance->network_username = 'joetwitter';
        $instance->network = 'twitter';
        $this->instance = $instance;

        TimeHelper::setTime(3);
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testFirstRunNoVerified() {
        $builders = $this->buildData(0);
        $insight_plugin = new VerifiedListInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('verifiedlist', 10, $today);
        $this->assertNull($result);
    }

    public function testFirstRunOneVerified() {
        $builders = $this->buildData(1);
        $insight_plugin = new VerifiedListInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('verifiedlist', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Verified!");
        $this->assertEqual($result->text, "@joetwitter has 1 verified follower.");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline = $baseline_dao->getMostRecentInsightBaseline('verifiedlistcount', $this->instance->id);
        $this->assertNotNull($baseline);
        $this->assertEqual(1, $baseline->value);
        $this->assertEqual($today, $baseline->date);
    }

    public function testFirstRunTwoVerified() {
        $builders = $this->buildData(2);
        $insight_plugin = new VerifiedListInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('verifiedlist', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Verified!");
        $this->assertEqual($result->text, "@joetwitter has 2 verified followers.");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 2);
        $this->assertEqual($data['people'][0]->username, 'ver2');
        $this->assertEqual($data['people'][1]->username, 'ver1');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline = $baseline_dao->getMostRecentInsightBaseline('verifiedlistcount', $this->instance->id);
        $this->assertNotNull($baseline);
        $this->assertEqual(2, $baseline->value);
        $this->assertEqual($today, $baseline->date);
    }

    public function testSecondRunAMonthLater() {
        $builders = $this->buildData(2);
        $builders[] = FixtureBuilder::build('insights', array('slug'=>'verifiedlist',
            'instance_id' => $this->instance->id, 'time_generated' => '-30d', 'related_data'=>serialize("")));

        $insight_plugin = new VerifiedListInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('verifiedlist', 10, $today);
        $this->assertNull($result);
    }

    public function testSecondRunAYearLaterNoChange() {
        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline("verifiedlistcount", $this->instance->id, 2,
            date('Y-m-d', strtotime('-368 days')));
        $builders = $this->buildData(2);
        $builders[] = FixtureBuilder::build('insights', array('slug'=>'verifiedlist',
            'instance_id' => $this->instance->id, 'time_generated' => '-368d', 'related_data'=>serialize("")));

        $insight_plugin = new VerifiedListInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('verifiedlist', 10, $today);
        $this->assertNull($result);
    }

    public function testSecondRunAYearLaterMoreVerified() {
        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline("verifiedlistcount", $this->instance->id, 1,
            date('Y-m-d', strtotime('-368 days')));
        $builders = $this->buildData(2);
        $builders[] = FixtureBuilder::build('insights', array('slug'=>'verifiedlist',
            'instance_id' => $this->instance->id, 'time_generated' => '-368d', 'related_data'=>serialize("")));

        $insight_plugin = new VerifiedListInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('verifiedlist', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Verified!");
        $this->assertEqual($result->text, "With 2 verified followers, @joetwitter must be doing something right. "
            . "That's up from 1 last year.");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 2);
        $this->assertEqual($data['people'][0]->username, 'ver2');
        $this->assertEqual($data['people'][1]->username, 'ver1');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline = $baseline_dao->getMostRecentInsightBaseline('verifiedlistcount', $this->instance->id);
        $this->assertNotNull($baseline);
        $this->assertEqual(2, $baseline->value);
        $this->assertEqual($today, $baseline->date);
    }

    public function testSecondRunAYearLaterLessVerified() {
        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline_dao->insertInsightBaseline("verifiedlistcount", $this->instance->id, 5,
            date('Y-m-d', strtotime('-368 days')));
        $builders = $this->buildData(3);
        $builders[] = FixtureBuilder::build('insights', array('slug'=>'verifiedlist',
            'instance_id' => $this->instance->id, 'time_generated' => '-368d', 'related_data'=>serialize("")));

        $insight_plugin = new VerifiedListInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('verifiedlist', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Verified!");
        $this->assertEqual($result->text, "@joetwitter has 3 verified followers. That's down from 5 last year.");
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 3);
        $this->assertEqual($data['people'][0]->username, 'ver3');
        $this->assertEqual($data['people'][1]->username, 'ver2');
        $this->assertEqual($data['people'][2]->username, 'ver1');

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $baseline = $baseline_dao->getMostRecentInsightBaseline('verifiedlistcount', $this->instance->id);
        $this->assertNotNull($baseline);
        $this->assertEqual(3, $baseline->value);
        $this->assertEqual($today, $baseline->date);
    }

    public function testAlternateText1() {
        TimeHelper::setTime(1);
        $builders = $this->buildData(2);
        $insight_plugin = new VerifiedListInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('verifiedlist', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Verified!");
        $this->assertEqual($result->text, "2 of @joetwitter's followers sport the coveted blue verified badge.");

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateText2() {
        TimeHelper::setTime(2);
        $builders = $this->buildData(1);
        $insight_plugin = new VerifiedListInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('verifiedlist', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Verified!");
        $this->assertEqual($result->text, "@joetwitter is basking in the reflected Twitter-legitimacy of 1 verified follower.");

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateText2a() {
        TimeHelper::setTime(2);
        $builders = $this->buildData(2);
        $insight_plugin = new VerifiedListInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('verifiedlist', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Verified!");
        $this->assertEqual($result->text, "@joetwitter is basking in the reflected Twitter-legitimacy of 2 verified followers.");

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateText3() {
        TimeHelper::setTime(4);
        $builders = $this->buildData(2);
        $insight_plugin = new VerifiedListInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('verifiedlist', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Verified!");
        $this->assertEqual($result->text, "2 of @joetwitter's followers sport the coveted blue verified badge.");

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testFacebook() {
        TimeHelper::setTime(2);
        $this->instance->network = 'facebook';
        $builders = $this->buildData(2);
        $insight_plugin = new VerifiedListInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts=array(), 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('verifiedlist', 10, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, "Did you know Facebook has verified users?");
        $this->assertEqual($result->text, "joetwitter is basking in the reflected Facebook-legitimacy of "
            ."2 verified friends.");

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    private function buildData($num_verified_followers) {
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>$this->instance->network_user_id,
            'user_name'=>$this->instance->network_username,
            'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>1, 'is_protected'=>1,
            'network'=>$this->instance->network, 'description'=>'A test User', 'location'=>'San Francisco, CA'));

        for ($i=1; $i<=$num_verified_followers; $i++) {
            $builders[] = FixtureBuilder::build('users', array('user_id'=>$i, 'user_name'=>'ver'.$i,
                'post_count' => 101, 'full_name'=>'Popular Gal','avatar'=>'avatar.jpg','follower_count'=>100 * $i,
                'is_protected'=>0,'friend_count'=>1, 'network'=>$this->instance->network,
                'avatar' => 'https://pbs.twimg.com/profile_images/426108979186384896/J3JDXvs4_normal.jpeg',
                'description'=>'Follower', 'location'=>'San Francisco, CA','is_verified'=>1));

            // Follows
            $builders[] = FixtureBuilder::build('follows', array('user_id'=>$this->instance->network_user_id,
                'follower_id'=>$i, 'last_seen'=>'-0d', 'first_seen'=>'-0d',
                'network'=>$this->instance->network,'active'=>1));
        }

        for ($i=1; $i<=10; $i++) {
            $builders[] = FixtureBuilder::build('users', array('user_id'=>10000+$i, 'user_name'=>'norm'.$i,
                'post_count' => 101, 'full_name'=>'Popular Gal','avatar'=>'avatar.jpg','follower_count'=>100 * $i,
                'is_protected'=>0,'friend_count'=>1, 'network'=>$this->instance->network,
                'description'=>'Follower', 'location'=>'San Francisco, CA','is_verified'=>0));

            // Follows
            $builders[] = FixtureBuilder::build('follows', array('user_id'=>$this->instance->network_user_id,
                'follower_id'=>1000+$i, 'last_seen'=>'-0d', 'first_seen'=>'-0d',
                'network'=>$this->instance->network,'active'=>1));
        }
        return $builders;
    }
}
