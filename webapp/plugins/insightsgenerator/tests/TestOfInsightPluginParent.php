<?php
/**
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfInsightPluginParent.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
 *
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
 *
 * Test of InsightPluginParent class
 *
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das, Gina Trapani
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';

class TestOfInsightPluginParent extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        putenv("MODE=PROD");
        $_SESSION["MODE"] = "PROD";
    }

    public function tearDown() {
        parent::tearDown();
        putenv("MODE=TESTS");
        $_SESSION["MODE"] = "TESTS";
    }

    public function testShouldGenerateInsight() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $time_now = date("Y-m-d H:i:s");
        $today = date('Y-m-d', strtotime('today'));
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $builders = array();

        $builders[] = FixtureBuilder::build('insights', array('id'=>76, 'instance_id'=>10, 'slug'=>'some_slug',
        'date'=>$today, 'time_generated'=>$time_now));

        $builders[] = FixtureBuilder::build('insights', array('id'=>77, 'instance_id'=>10, 'slug'=>'some_other_slug',
        'date'=>$yesterday, 'time_generated'=>$time_now));

        $insight_plugin_parent = new InsightPluginParent();
        $insight_plugin_parent->insight_dao = DAOFactory::getDAO('InsightDAO');

        // Test default values
        $this->assertTrue($insight_plugin_parent->shouldGenerateInsight('a_slug', $instance));
        $this->assertFalse($insight_plugin_parent->shouldGenerateInsight('some_slug', $instance));

        // Test regeneration on a given date
        $this->assertTrue($insight_plugin_parent->shouldGenerateInsight('a_slug', $instance,
            $insight_date=$today));
        $this->assertFalse($insight_plugin_parent->shouldGenerateInsight('some_other_slug', $instance,
            $insight_date=$yesterday));
        $this->assertTrue($insight_plugin_parent->shouldGenerateInsight('some_other_slug', $instance,
            $insight_date=$yesterday, $regenerate_existing_insight=true));

        // Test with last week of posts
        $this->assertTrue($insight_plugin_parent->shouldGenerateInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false,  $count_related_posts=13));
        $this->assertFalse($insight_plugin_parent->shouldGenerateInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $count_related_posts=0));

        // Test excluded networks
        $this->assertTrue($insight_plugin_parent->shouldGenerateInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $count_related_posts=null,
            $excluded_networks=array('facebook')));
        $this->assertFalse($insight_plugin_parent->shouldGenerateInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false,  $count_related_posts=null,
            $excluded_networks=array('twitter', 'facebook')));
    }

    public function testShouldGenerateWeeklyInsight() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $time_now = date("Y-m-d H:i:s");
        $today = date('Y-m-d', strtotime('today'));
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $builders = array();

        $builders[] = FixtureBuilder::build('insights', array('id'=>76, 'instance_id'=>10, 'slug'=>'some_slug',
        'date'=>$today, 'time_generated'=>$time_now));

        $builders[] = FixtureBuilder::build('insights', array('id'=>77, 'instance_id'=>10, 'slug'=>'some_other_slug',
        'date'=>$yesterday, 'time_generated'=>$time_now));

        $insight_plugin_parent = new InsightPluginParent();
        $insight_plugin_parent->insight_dao = DAOFactory::getDAO('InsightDAO');

        // Test default values
        $this->assertTrue($insight_plugin_parent->shouldGenerateWeeklyInsight('a_slug', $instance));
        $this->assertFalse($insight_plugin_parent->shouldGenerateWeeklyInsight('some_slug', $instance));

        // Test regeneration on a given date
        $this->assertTrue($insight_plugin_parent->shouldGenerateWeeklyInsight('a_slug', $instance,
            $insight_date=$today));
        $this->assertFalse($insight_plugin_parent->shouldGenerateWeeklyInsight('some_other_slug', $instance,
            $insight_date=$yesterday));
        $this->assertTrue($insight_plugin_parent->shouldGenerateWeeklyInsight('some_other_slug', $instance,
            $insight_date=$yesterday, $regenerate_existing_insight=true));

        // Test for day of week
        $dow1 = date('w');
        $dow2 = date('w', strtotime('-1 day'));
        $this->assertTrue($insight_plugin_parent->shouldGenerateWeeklyInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $day_of_week=$dow1));
        $this->assertFalse($insight_plugin_parent->shouldGenerateWeeklyInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $day_of_week=$dow2));

        // Test with last week of posts
        $this->assertTrue($insight_plugin_parent->shouldGenerateWeeklyInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $day_of_week=null,
            $count_last_week_of_posts=13));
        $this->assertFalse($insight_plugin_parent->shouldGenerateWeeklyInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $day_of_week=null, $count_last_week_of_posts=0));

        // Test excluded networks
        $this->assertTrue($insight_plugin_parent->shouldGenerateWeeklyInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $day_of_week=null,
            $count_last_week_of_posts=null, $excluded_networks=array('facebook')));
        $this->assertFalse($insight_plugin_parent->shouldGenerateWeeklyInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $day_of_week=null,
            $count_last_week_of_posts=null, $excluded_networks=array('twitter', 'facebook')));
    }

    public function testShouldGenerateMonthlyInsight() {
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $time_now = date("Y-m-d H:i:s");
        $today = date('Y-m-d', strtotime('today'));
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $builders = array();

        $builders[] = FixtureBuilder::build('insights', array('id'=>76, 'instance_id'=>10, 'slug'=>'some_slug',
        'date'=>$today, 'time_generated'=>$time_now));

        $builders[] = FixtureBuilder::build('insights', array('id'=>77, 'instance_id'=>10, 'slug'=>'some_other_slug',
        'date'=>$yesterday, 'time_generated'=>$time_now));

        $insight_plugin_parent = new InsightPluginParent();
        $insight_plugin_parent->insight_dao = DAOFactory::getDAO('InsightDAO');

        // Test default values
        $this->assertTrue($insight_plugin_parent->shouldGenerateMonthlyInsight('a_slug', $instance));
        $this->assertFalse($insight_plugin_parent->shouldGenerateMonthlyInsight('some_slug', $instance));

        // Test regeneration on a given date
        $this->assertTrue($insight_plugin_parent->shouldGenerateMonthlyInsight('a_slug', $instance,
            $insight_date=$today));
        $this->assertFalse($insight_plugin_parent->shouldGenerateMonthlyInsight('some_other_slug', $instance,
            $insight_date=$yesterday));
        $this->assertTrue($insight_plugin_parent->shouldGenerateMonthlyInsight('some_other_slug', $instance,
            $insight_date=$yesterday, $regenerate_existing_insight=true));

        // Test for day of month
        $day_of_month1 = date('j');
        $day_of_month2 = date('j', strtotime('-1 day'));
        $this->assertTrue($insight_plugin_parent->shouldGenerateMonthlyInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $day_of_month=$day_of_month1));
        $this->assertFalse($insight_plugin_parent->shouldGenerateMonthlyInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $day_of_month=$day_of_month2));

        // Test with last week of posts
        $this->assertTrue($insight_plugin_parent->shouldGenerateMonthlyInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $day_of_month=null,
            $count_related_posts=13));
        $this->assertFalse($insight_plugin_parent->shouldGenerateMonthlyInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $day_of_month=null, $count_related_posts=0));

        // Test excluded networks
        $this->assertTrue($insight_plugin_parent->shouldGenerateMonthlyInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $day_of_month=null,
            $count_last_week_of_posts=null, $excluded_networks=array('facebook')));
        $this->assertFalse($insight_plugin_parent->shouldGenerateMonthlyInsight('a_slug', $instance,
            $insight_date='today', $regenerate_existing_insight=false, $day_of_month=null,
            $count_last_week_of_posts=null, $excluded_networks=array('twitter', 'facebook')));
    }

}