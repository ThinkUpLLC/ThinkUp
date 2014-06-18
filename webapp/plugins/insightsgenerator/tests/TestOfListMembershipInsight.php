<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfListMembershipInsight.php
 *
 * Copyright (c) 2013-2014 Gina Trapani
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
 * @copyright 2013-2014 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/listmembership.php';

class TestOfListMembershipInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        TimeHelper::setTime(2);
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testLessThan4NewListMembershipNoHistory() {
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
        $stylestats_insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'new_group_memberships');
        $this->assertEqual($result->filename, 'listmembership');
        $this->debug($result->headline);
        $this->assertEqual($result->headline, 'Do &ldquo;list7&rdquo;, &ldquo;list6&rdquo;, &ldquo;list5&rdquo;, '.
            'and &ldquo;list4&rdquo; sound like good descriptions of @ev?');
        $this->assertPattern('/sound like good descriptions of @ev?/', $result->headline);
        $this->assertPattern('/new lists: \<a href="http:\/\/twitter.com\/listmaker\/list7"\>list7\<\/a\>/',
        $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testLessThan4NewListMembershipWithHighHistory() {
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
        $stylestats_insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'new_group_memberships');
        $this->assertEqual($result->filename, 'listmembership');
        $this->assertPattern('/\@ev is on 8 new lists:/', $result->text);
        $this->debug($result->headline);
        $this->assertEqual($result->headline, 'Do &ldquo;list7&rdquo;, &ldquo;list6&rdquo;, &ldquo;list5&rdquo;, '.
        'and &ldquo;list4&rdquo; sound like good descriptions of @ev?');
        $this->assertPattern('/and &ldquo;list4&rdquo;/', $result->headline);
        $this->assertPattern('/bringing the total to \<strong\>58 lists\<\/strong\>\./', $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testLessThan4NewListMembershipWithLowHistory() {
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
        $stylestats_insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'new_group_memberships');
        $this->assertEqual($result->filename, 'listmembership');
        $this->assertPattern('/sound like good descriptions of @ev?/', $result->headline);
        $this->assertPattern('/new lists: \<a href="http:\/\/twitter.com\/listmaker\/list7"\>list7\<\/a\>/',
        $result->text);
        $this->assertNoPattern('/bringing your total to/', $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMoreThan4NewListMembershipNoHistory() {
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
        $stylestats_insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->debug($result->headline);
        $this->debug($result->text);
        $this->assertEqual($result->slug, 'new_group_memberships');
        $this->assertEqual($result->filename, 'listmembership');
        $this->assertPattern('/&ldquo;list22&rdquo; sound like good descriptions of @ev?/', $result->headline);
        $this->assertPattern('/and 22 more/', $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function test4NewListMembershipNoHistory() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNull($result);

        $builders = self::buildData($total_lists = 4);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $stylestats_insight_plugin = new ListMembershipInsight();
        $stylestats_insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->debug($result->headline);
        $this->debug($result->text);
        $this->assertEqual($result->slug, 'new_group_memberships');
        $this->assertEqual($result->filename, 'listmembership');
        $this->assertEqual($result->headline, 'Do &ldquo;list3&rdquo;, &ldquo;list2&rdquo;, &ldquo;list1&rdquo;, '.
        'and &ldquo;list0&rdquo; sound like good descriptions of @ev?');
        $this->assertPattern('/&ldquo;list0&rdquo; sound like good descriptions of @ev?/', $result->headline);
    }

    public function test1NewListMembershipNoHistory() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNull($result);

        $builders = self::buildData($total_lists = 1);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $stylestats_insight_plugin = new ListMembershipInsight();
        $stylestats_insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'new_group_memberships');
        $this->assertEqual($result->filename, 'listmembership');
        $this->debug($result->headline);
        $this->assertPattern('/Does &ldquo;list0&rdquo; seem like a good description of @ev?/', $result->headline);
        $this->assertNoPattern('/bringing/', $result->text);
        $data = unserialize($result->related_data);
        $this->assertNull($data['vis_data']);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function test1NewListMembershipWithHistory() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNull($result);

        $builders = self::buildData($total_lists = 1, $build_history= true, $history_ceiling=5);

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $stylestats_insight_plugin = new ListMembershipInsight();
        $stylestats_insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'new_group_memberships');
        $this->assertEqual($result->filename, 'listmembership');
        $this->assertPattern('/Does &ldquo;list0&rdquo; seem like a good description of @ev?/', $result->headline);
        $this->assertPattern('/bringing the total to \<strong\>6 lists\<\/strong\>./', $result->text);
    }

    public function testMultipleSameNamedLists() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNull($result);

        $builders = self::buildData($total_lists = 1);
        $builders[] = FixtureBuilder::build('groups', array('group_id'=>2, 'network'=>'twitter',
        'is_active'=>1, 'first_seen'=>date('Y-m-d H:i:'), 'group_name'=>'@listmaker/list0') );

        $builders[] = FixtureBuilder::build('group_members', array('group_id'=>2, 'member_user_id'=>'13',
        'network'=>'twitter', 'is_active'=>1, 'first_seen'=>date('Y-m-d H:i:'). (10)));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $stylestats_insight_plugin = new ListMembershipInsight();
        $stylestats_insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'new_group_memberships');
        $this->assertEqual($result->filename, 'listmembership');
        $this->assertPattern('/Does &ldquo;list0&rdquo; seem like a good description of @ev?/', $result->headline);
        $this->assertPattern('/@ev got added to a new list/', $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testMultipleSameNamedListsWithHistory() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNull($result);

        $builders = self::buildData($total_lists = 2, $build_history = true, $history_ceiling = 5);
        $builders[] = FixtureBuilder::build('groups', array('group_id'=>12, 'network'=>'twitter',
        'is_active'=>1, 'first_seen'=>date('Y-m-d H:i:'), 'group_name'=>'@listmaker/list0') );
        $builders[] = FixtureBuilder::build('group_members', array('group_id'=>12, 'member_user_id'=>'13',
        'network'=>'twitter', 'is_active'=>1, 'first_seen'=>date('Y-m-d H:i:10')));
        $builders[] = FixtureBuilder::build('groups', array('group_id'=>13, 'network'=>'twitter',
        'is_active'=>1, 'first_seen'=>date('Y-m-d H:i:'), 'group_name'=>'@listmaker/list1') );
        $builders[] = FixtureBuilder::build('group_members', array('group_id'=>13, 'member_user_id'=>'13',
        'network'=>'twitter', 'is_active'=>1, 'first_seen'=>date('Y-m-d H:i:11')));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $stylestats_insight_plugin = new ListMembershipInsight();
        $stylestats_insight_plugin->generateInsight($instance, null, array(), 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'new_group_memberships');
        $this->assertEqual($result->filename, 'listmembership');
        $this->assertPattern('/Do &ldquo;list1&rdquo; and &ldquo;list0&rdquo; sound like good descriptions of @ev?/', $result->headline);
        $this->assertPattern('/@ev is on 2 new lists/', $result->text);
        $this->assertPattern('/to <strong>7 lists/', $result->text);
        $this->assertPattern('/>list1</', $result->text);
        $this->assertPattern('/>list0</', $result->text);

        $data = unserialize($result->related_data);
        $this->assertNotNull($data['vis_data']);
    }

    public function testWeeklyLimiting() {
        $builders = self::buildData($total_lists = 2, $build_history = true, $history_ceiling = 5);
        $builders[] = FixtureBuilder::build('groups', array('group_id'=>12, 'network'=>'twitter',
        'is_active'=>1, 'first_seen'=>date('Y-m-d H:i:'), 'group_name'=>'@listmaker/list0') );
        $builders[] = FixtureBuilder::build('group_members', array('group_id'=>12, 'member_user_id'=>'13',
        'network'=>'twitter', 'is_active'=>1, 'first_seen'=>date('Y-m-d H:i:10')));
        $builders[] = FixtureBuilder::build('groups', array('group_id'=>13, 'network'=>'twitter',
        'is_active'=>1, 'first_seen'=>date('Y-m-d H:i:'), 'group_name'=>'@listmaker/list1') );
        $builders[] = FixtureBuilder::build('group_members', array('group_id'=>13, 'member_user_id'=>'13',
        'network'=>'twitter', 'is_active'=>1, 'first_seen'=>date('Y-m-d H:i:11')));

        $builders[] = FixtureBuilder::build('insights',array('slug'=>'new_group_memberships', 'instance_id' => 1,
            'related_data' => serialize(array()), 'time_generated' => '-6d'));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $insight_plugin = new ListMembershipInsight();
        $insight_plugin->generateInsight($instance, null, array(), 3);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNull($result);

        $deleted = $insight_dao->deleteInsightsBySlug('new_group_memberships', 1);
        $this->assertEqual($deleted, 1);
        $insight_plugin->generateInsight($instance, null, array(), 3);
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);

        $deleted = $insight_dao->deleteInsightsBySlug('new_group_memberships', 1);
        $this->assertEqual($deleted, 1);
        $insight_plugin->generateInsight($instance, null, array(), 3);
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
    }

    public function testThatAllMissedMembershipsAreShown() {
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'13', 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>70,
        'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>12, 'network'=>'twitter',
        'is_active'=>1, 'first_seen'=>'-8d', 'group_name'=>'@listmaker/list0') );
        $builders[] = FixtureBuilder::build('group_members', array('group_id'=>12, 'member_user_id'=>'13',
        'network'=>'twitter', 'is_active'=>1, 'first_seen'=>'-8d'));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>13, 'network'=>'twitter',
        'is_active'=>1, 'first_seen'=>'-3d', 'group_name'=>'@listmaker/list1') );
        $builders[] = FixtureBuilder::build('group_members', array('group_id'=>13, 'member_user_id'=>'13',
        'network'=>'twitter', 'is_active'=>1, 'first_seen'=>'-3d'));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>14, 'network'=>'twitter',
        'is_active'=>1, 'first_seen'=>'-2d', 'group_name'=>'@listmaker/list2') );
        $builders[] = FixtureBuilder::build('group_members', array('group_id'=>14, 'member_user_id'=>'13',
        'network'=>'twitter', 'is_active'=>1, 'first_seen'=>'-2d'));

        $builders[] = FixtureBuilder::build('insights',array('slug'=>'new_group_memberships', 'instance_id' => 1,
            'related_data' => serialize(array()), 'time_generated' => '-169h'));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $insight_plugin = new ListMembershipInsight();
        $insight_plugin->generateInsight($instance, null, array(), 3);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertPattern('/ev is on 2 new lists/', $result->text);
        $this->assertNoPattern('/list0/', $result->headline);
        $this->assertPattern('/list1/', $result->headline);
        $this->assertPattern('/list2/', $result->headline);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

    public function testAlternateText() {
        TimeHelper::setTime(3);
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'13', 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>70,
        'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>14, 'network'=>'twitter',
        'is_active'=>1, 'first_seen'=>'-2d', 'group_name'=>'@listmaker/list2') );
        $builders[] = FixtureBuilder::build('group_members', array('group_id'=>14, 'member_user_id'=>'13',
        'network'=>'twitter', 'is_active'=>1, 'first_seen'=>'-2d'));

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = '13';
        $instance->network = 'twitter';
        $instance->network_username = 'ev';
        $insight_plugin = new ListMembershipInsight();
        $insight_plugin->generateInsight($instance, null, array(), 3);

        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual("@listmaker added @ev to a list that's called &ldquo;list2&rdquo;.", $result->headline);
        $this->assertEqual('@ev got added to a new list, <a href="http://twitter.com/listmaker/list2">list2</a>.',
            $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>13, 'network'=>'twitter',
        'is_active'=>1, 'first_seen'=>'-3d', 'group_name'=>'@listmaker/list1') );
        $builders[] = FixtureBuilder::build('group_members', array('group_id'=>13, 'member_user_id'=>'13',
        'network'=>'twitter', 'is_active'=>1, 'first_seen'=>'-3d'));

        $deleted = $insight_dao->deleteInsightsBySlug('new_group_memberships', 1);
        $insight_plugin->generateInsight($instance, null, array(), 3);
        $result = $insight_dao->getInsight('new_group_memberships', 1, date ('Y-m-d'));
        $this->assertNotNull($result);
        $this->assertEqual("@ev got added to lists called &ldquo;list2&rdquo; and &ldquo;list1&rdquo;.", $result->headline);
        $this->assertEqual('@ev is on 2 new lists: <a href="http://twitter.com/listmaker/list2">list2</a> '
            . 'and <a href="http://twitter.com/listmaker/list1">list1</a>.', $result->text);

        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
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
            $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>'13',
            'network'=>'twitter', 'date'=>date('Y-m-d'), type =>'group_memberships','count'=>$history_ceiling));

            $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>'13',
            'network'=>'twitter', 'date'=>date ('Y-m-d', strtotime('-1 day')), 'type' =>'group_memberships',
            'count'=>($history_ceiling-3)));

            $builders[] = FixtureBuilder::build('count_history', array('network_user_id'=>'13',
            'network'=>'twitter', 'date'=>date ('Y-m-d', strtotime('-2 day')), 'type' =>'group_memberships',
            'count'=>($history_ceiling-5)));
        }

        return $builders;
    }
}
