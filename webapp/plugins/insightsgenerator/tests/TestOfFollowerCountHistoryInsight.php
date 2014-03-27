<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfFollowerCountHistoryInsight.php
 *
 * Copyright (c) Chris Moyer
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
 * Test of Follower Count History
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/followercounthistory.php';

class TestOfFollowerCountHistoryInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'hitchhiker';
        $instance->network = 'twitter';
        $this->instance = $instance;
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $followercount_insight_plugin = new FollowerCountInsight();
        $this->assertIsA($followercount_insight_plugin, 'FollowerCountInsight' );
    }

    public function testWeeklyNextWeek() {
        $builders = array();
        for ($i=0; $i<20; $i++) {
            $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
                'type'=>'followers', 'count'=>'10', 'date' => date('Y-m-d', strtotime('-'.(16+($i*7)).' day'))));
        }
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'900', 'date' => date('Y-m-d', strtotime('-9 day'))));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'950', 'date' => date('Y-m-d', strtotime('-2 day'))));

        $insight_plugin = new FollowerCountInsight();
        $insight_plugin->generateInsight($this->instance, array(), 3);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('follower_count_history_by_week_milestone', 1, date('Y-m-d'));

        $this->assertEqual('Wow! Only <strong>1 week</strong> till @hitchhiker reaches <strong>1,000</strong> '.
            'followers.', $result->headline);

        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['history']), 15);
        $this->assertEqual($data['trend'], 63);
        $this->assertEqual($data['milestone']['units_of_time'], 'WEEK');
        $this->assertEqual($data['milestone']['next_milestone'], 1000);
        $this->assertEqual($data['milestone']['will_take'], 1);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWeeklyNextTenWeeks() {
        $builders = array();
        for ($i=0; $i<20; $i++) {
            $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
                'type'=>'followers', 'count'=>'846', 'date' => date('Y-m-d', strtotime('-'.(16+($i*7)).' day'))));
        }
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'846', 'date' => date('Y-m-d', strtotime('-9 day'))));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'940', 'date' => date('Y-m-d', strtotime('-2 day'))));

        $insight_plugin = new FollowerCountInsight();
        $insight_plugin->generateInsight($this->instance, array(), 3);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('follower_count_history_by_week_milestone', 1, date('Y-m-d'));

        $this->assertEqual('Looks like it will be <strong>10 weeks</strong> till @hitchhiker reaches '.
            '<strong>1,000</strong> followers.', $result->headline);

        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['history']), 15);
        $this->assertEqual($data['trend'], 6);
        $this->assertEqual($data['milestone']['units_of_time'], 'WEEK');
        $this->assertEqual($data['milestone']['next_milestone'], 1000);
        $this->assertEqual($data['milestone']['will_take'], 10);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testWeeklyNextTooManyWeeks() {
        $builders = array();
        for ($i=0; $i<20; $i++) {
            $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
                'type'=>'followers', 'count'=>'846', 'date' => date('Y-m-d', strtotime('-'.(16+($i*7)).' day'))));
        }
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'746', 'date' => date('Y-m-d', strtotime('-9 day'))));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'840', 'date' => date('Y-m-d', strtotime('-2 day'))));

        $insight_plugin = new FollowerCountInsight();
        $insight_plugin->generateInsight($this->instance, array(), 3);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('follower_count_history_by_week_milestone', 1, date('Y-m-d'));
        $this->assertNull($result);
    }

    public function testMonthlyNextMonth() {
        $builders = array();
        for ($i=0; $i<20; $i++) {
            $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
                'type'=>'followers', 'count'=>'9320', 'date' => date('Y-m-d', strtotime('-'.(92+($i*30)).' day'))));
        }
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'9900', 'date' => date('Y-m-d', strtotime('-62 day'))));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'9950', 'date' => date('Y-m-d', strtotime('-32 day'))));

        $insight_plugin = new FollowerCountInsight();
        $insight_plugin->generateInsight($this->instance, array(), 3);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('follower_count_history_by_month_milestone', 1, date('Y-m-d'));

        $this->assertEqual('Nice: Only <strong>1 month</strong> till @hitchhiker reaches <strong>10,000</strong> '.
            'followers.', $result->headline);

        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['history']), 15);
        $this->assertEqual($data['trend'], 42);
        $this->assertEqual($data['milestone']['units_of_time'], 'MONTH');
        $this->assertEqual($data['milestone']['next_milestone'], 10000);
        $this->assertEqual($data['milestone']['will_take'], 1);
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $result = $insight_dao->getInsight('follower_count_history_by_week_milestone', 1, date('Y-m-d'));
        $this->assertNull($result);
    }


    public function testMonthlyUnder15Months() {
        $builders = array();
        for ($i=0; $i<7; $i++) {
            $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
                'type'=>'followers', 'count'=>'9320', 'date' => date('Y-m-d', strtotime('-'.(92+($i*30)).' day'))));
        }
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'9900', 'date' => date('Y-m-d', strtotime('-62 day'))));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'9950', 'date' => date('Y-m-d', strtotime('-32 day'))));

        $insight_plugin = new FollowerCountInsight();
        $insight_plugin->generateInsight($this->instance, array(), 3);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('follower_count_history_by_month_milestone', 1, date('Y-m-d'));

        $this->assertEqual('Nice: Only <strong>1 month</strong> till @hitchhiker reaches <strong>10,000</strong> '.
            'followers.', $result->headline);

        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['history']), 9);
        $this->assertEqual($data['trend'], 70);
        $this->assertEqual($data['milestone']['units_of_time'], 'MONTH');
        $this->assertEqual($data['milestone']['next_milestone'], 10000);
        $this->assertEqual($data['milestone']['will_take'], 1);

        $result = $insight_dao->getInsight('follower_count_history_by_week_milestone', 1, date('Y-m-d'));
        $this->assertNull($result);
    }

    public function testWeeklyUnder15Weeks() {
        $builders = array();
        for ($i=0; $i<7; $i++) {
            $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
                'type'=>'followers', 'count'=>'846' - ($i*10), 'date' => date('Y-m-d', strtotime('-'.(16+($i*7)).' day'))));
        }
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'846', 'date' => date('Y-m-d', strtotime('-9 day'))));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'940', 'date' => date('Y-m-d', strtotime('-2 day'))));

        $insight_plugin = new FollowerCountInsight();
        $insight_plugin->generateInsight($this->instance, array(), 3);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('follower_count_history_by_week_milestone', 1, date('Y-m-d'));

        $this->assertEqual('Looks like it will be <strong>4 weeks</strong> till @hitchhiker reaches '.
            '<strong>1,000</strong> followers.', $result->headline);

        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['history']), 9);
        $this->assertEqual($data['trend'], 17);
        $this->assertEqual($data['milestone']['units_of_time'], 'WEEK');
        $this->assertEqual($data['milestone']['next_milestone'], 1000);
        $this->assertEqual($data['milestone']['will_take'], 4);
        $vis_data = $data['vis_data'];
        $vis_data = preg_replace("/(new Date[^)]+\))/", '"$1"', $vis_data);
        $vis_data = json_decode($vis_data);
        $this->assertEqual(9, count($vis_data->rows));

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMonthlyTooFewMonths() {
        $builders = array();
        for ($i=0; $i<2; $i++) {
            $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
                'type'=>'followers', 'count'=>'9320', 'date' => date('Y-m-d', strtotime('-'.(92+($i*30)).' day'))));
        }
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'9900', 'date' => date('Y-m-d', strtotime('-62 day'))));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'9950', 'date' => date('Y-m-d', strtotime('-32 day'))));

        $insight_plugin = new FollowerCountInsight();
        $insight_plugin->generateInsight($this->instance, array(), 3);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('follower_count_history_by_month_milestone', 1, date('Y-m-d'));
        $this->assertNull($result);
    }

    public function testWeeklyTooFewWeeks() {
        $builders = array();
        for ($i=0; $i<2; $i++) {
            $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
                'type'=>'followers', 'count'=>'846', 'date' => date('Y-m-d', strtotime('-'.(16+($i*7)).' day'))));
        }
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'846', 'date' => date('Y-m-d', strtotime('-9 day'))));
        $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>42, 'network'=>'twitter',
            'type'=>'followers', 'count'=>'940', 'date' => date('Y-m-d', strtotime('-2 day'))));

        $insight_plugin = new FollowerCountInsight();
        $insight_plugin->generateInsight($this->instance, array(), 3);
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('follower_count_history_by_week_milestone', 1, date('Y-m-d'));
        $this->assertNull($result);
    }
}
