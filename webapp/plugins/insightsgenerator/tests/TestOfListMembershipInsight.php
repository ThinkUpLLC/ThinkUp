<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfListMembershipInsight.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * Test of Style Stats Insight
 *
 * Test for ListMembershipInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/listmembership.php';

class TestOfListMembershipInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testLessThan10NewListMembershipNoHistory() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNull($result);

        $builders = self::buildData($total_lists = 8);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $stylestats_insight_plugin = new ListMembershipInsight();
        $stylestats_insight_plugin->generateInsight($instance, array(), 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'new_group_memberships');
        $this->assertEqual($result->prefix, 'Made the list:');
        $this->assertEqual($result->filename, 'listmembership');
        $this->assertPattern('/\@ev is on 8 new lists:/', $result->text);
        $this->assertPattern('/and \<a href="http:\/\/twitter.com\/listmaker\/list0\"\>list0\<\/a\>/', $result->text);
    }

    public function testLessThan10NewListMembershipWithHighHistory() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNull($result);

        $builders = self::buildData($total_lists = 8, $build_history= true, $history_ceiling=50);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $stylestats_insight_plugin = new ListMembershipInsight();
        $stylestats_insight_plugin->generateInsight($instance, array(), 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'new_group_memberships');
        $this->assertEqual($result->prefix, 'Made the list:');
        $this->assertEqual($result->filename, 'listmembership');
        $this->assertPattern('/\@ev is on 8 new lists:/', $result->text);
        $this->assertPattern('/and \<a href="http:\/\/twitter.com\/listmaker\/list0\"\>list0\<\/a\>/', $result->text);
        $this->assertPattern('/bringing the total to \<strong\>50 lists\<\/strong\>\./', $result->text);
    }

    public function testLessThan10NewListMembershipWithLowHistory() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNull($result);

        $builders = self::buildData($total_lists = 8, $build_history= true, $history_ceiling=5);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $stylestats_insight_plugin = new ListMembershipInsight();
        $stylestats_insight_plugin->generateInsight($instance, array(), 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'new_group_memberships');
        $this->assertEqual($result->prefix, 'Made the list:');
        $this->assertEqual($result->filename, 'listmembership');
        $this->assertPattern('/\@ev is on 8 new lists:/', $result->text);
        $this->assertPattern('/and \<a href="http:\/\/twitter.com\/listmaker\/list0\"\>list0\<\/a\>/', $result->text);
        $this->assertNoPattern('/bringing your total to/', $result->text);
    }

    public function testMoreThan10NewListMembershipNoHistory() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNull($result);

        $builders = self::buildData($total_lists = 26);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $stylestats_insight_plugin = new ListMembershipInsight();
        $stylestats_insight_plugin->generateInsight($instance, array(), 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'new_group_memberships');
        $this->assertEqual($result->prefix, 'Made the list:');
        $this->assertEqual($result->filename, 'listmembership');
        $this->assertPattern('/\@ev is on 26 new lists:/', $result->text);
        $this->assertPattern('/and 16 more/', $result->text);
    }

    private function buildData($total_lists, $build_history=false, $history_ceiling=50) {
        $builders = array();

        //Add user
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'13', 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>70,
        'network'=>'twitter'));

        //Add 25 groups and memberships
        $counter = 0;
        $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
        $todays_date = date('Y-m-d H:i:');
        while ($counter < $total_lists) {
            $builders[] = FixtureBuilder::build('groups', array('group_id'=>$counter, 'network'=>'twitter',
            'is_active'=>1, 'first_seen'=>$todays_date, 'group_name'=>'@listmaker/list'.$counter) );

            $builders[] = FixtureBuilder::build('group_members', array('group_id'=>$counter, 'member_user_id'=>'13',
            'network'=>'twitter', 'is_active'=>1, 'first_seen'=>$todays_date . (10+$counter)));
            $counter++;
        }

        if ($build_history) {
            date ('Y-m-d', strtotime('-1 day'));
            $builders[] = FixtureBuilder::build('group_member_count', array('member_user_id'=>'13',
            'network'=>'twitter', 'date'=>date('Y-m-d'), 'count'=>$history_ceiling));

            $builders[] = FixtureBuilder::build('group_member_count', array('member_user_id'=>'13',
            'network'=>'twitter', 'date'=>date ('Y-m-d', strtotime('-1 day')), 'count'=>($history_ceiling-3)));

            $builders[] = FixtureBuilder::build('group_member_count', array('member_user_id'=>'13',
            'network'=>'twitter', 'date'=>date ('Y-m-d', strtotime('-2 day')), 'count'=>($history_ceiling-5)));
        }

        return $builders;
    }
}
