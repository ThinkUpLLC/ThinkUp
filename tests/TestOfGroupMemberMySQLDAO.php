<?php
/**
 *
 * ThinkUp/tests/TestOfGroupMemberMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, SwellPath, Inc.
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
 * (based on TestOfFollowMySQLDAO)
 *
 * @author Christian G. Warden <cwarden[at]xerus[dot]org>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, SwellPath, Inc.
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfGroupMemberMySQLDAO extends ThinkUpUnitTestCase {
    /**
     * @var GroupMemberMySQLDAO
     */
    protected $DAO;
    /**
     * @var Logger
     */
    protected $logger;

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->DAO = new GroupMemberMySQLDAO();
        $this->builders = self::buildData();
    }

    protected function buildData() {
        $builders = array();
        //Insert test data into test table

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1234567890', 'user_name'=>'jack',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>'150210', 'friend_count'=>124,
        'is_protected'=>0));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1623457890', 'user_name'=>'private',
        'full_name'=>'Private Poster', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>35342,
        'friend_count'=>1345));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>'18864710', 'group_name'=>'@someguy/a-list',
        'network' => 'twitter', 'is_active'=>1));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>'19994710', 'group_name'=>'@somegal/her-list',
        'network' => 'twitter', 'is_active'=>1));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>'19554710', 'group_name'=>'@userx/anotherlist',
        'network' => 'twitter', 'is_active'=>1));

        // Jack's in three groups
        $builders[] = FixtureBuilder::build('group_members', array('member_user_id'=>'1234567890',
        'group_id'=>'18864710', 'is_active' => 1, 'network'=>'twitter', 'last_seen' => '-2d', 'first_seen' => '-2d'));

        $builders[] = FixtureBuilder::build('group_members', array('member_user_id'=>'1234567890',
        'group_id'=>'19554710', 'is_active' => 1, 'network'=>'twitter', 'last_seen' => '-0h', 'first_seen' => '-0h'));

        // one stale
        $builders[] = FixtureBuilder::build('group_members', array('member_user_id'=>'1234567890',
        'group_id'=>'19994710', 'is_active' => 1, 'network'=>'twitter', 'last_seen' => '-3d'));

        // Private Poster is active in one group
        $builders[] = FixtureBuilder::build('group_members', array('member_user_id'=>'1623457890',
        'group_id'=>'19994710', 'is_active' => 1, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('group_members', array('member_user_id'=>'1623457890',
        'group_id'=>'19554710', 'is_active' => 0, 'network'=>'twitter'));

        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
        $this->logger->close();
        $this->DAO = null;
    }

    public function testIsGroupMemberInStorage() {
        $this->assertTrue($this->DAO->isGroupMemberInStorage($user_id = '1234567890', $group_id = '18864710',
        'twitter'));
        $this->assertFalse($this->DAO->isGroupMemberInStorage($user_id = '1623457890', $group_id = '18864710',
        'twitter'));

        //inactive group membership
        $this->assertFalse($this->DAO->isGroupMemberInStorage($user_id = '1623457890', $group_id = '19554710',
        'twitter', $is_active = true));
        $this->assertTrue($this->DAO->isGroupMemberInStorage($user_id = '1623457890', $group_id = '19554710',
        'twitter'));

        //active group membership
        $this->assertTrue($this->DAO->isGroupMemberInStorage($user_id = '1234567890', $group_id = '18864710', 'twitter',
        $active = true));
    }

    public function testUpdate() {
        $this->assertEqual($this->DAO->update($user_id = '1234567890', $group_id = '18864710', 'twitter'), 1);
        $this->assertEqual($this->DAO->update($user_id = '1623457890', $group_id = '18864710', 'twitter'), 0);
    }

    public function testDeactivate() {
        $this->assertEqual($this->DAO->deactivate($user_id = '1234567890', $group_id = '18864710', 'twitter'), 1);
        $this->assertEqual($this->DAO->deactivate($user_id = '1623457890', $group_id = '19554710', 'twitter'), 0);
    }

    public function testInsert() {
        $this->assertEqual($this->DAO->insert($user_id = '1623457890', $group_id = '18864710', 'twitter'), 1);
        $this->assertTrue($this->DAO->isGroupMemberInStorage($user_id = '1623457890', $group_id = '18864710',
        'twitter'));
    }

    public function testGetTotalGroups() {
        $this->assertEqual($this->DAO->getTotalGroups($user_id = '1234567890', 'twitter'), 3);
        $this->assertEqual($this->DAO->getTotalGroups($user_id = '1623457890', 'twitter', $active = false), 2);
        $this->assertEqual($this->DAO->getTotalGroups($user_id = '1623457890', 'twitter', $active = true), 1);
    }

    public function testGetFormerGroups() {
        $this->assertEqual(count($this->DAO->getFormerGroups($user_id = '1234567890', 'twitter')), 0);
        $this->assertEqual(count($this->DAO->getFormerGroups($user_id = '1623457890', 'twitter')), 1);
    }

    public function testFindStalestMemberships() {
        // first group
        $stale_group = $this->DAO->findStalestMemberships($user_id = '1234567890', 'twitter');
        $this->assertTrue(is_object($stale_group));
        $this->assertEqual(get_class($stale_group), 'Group');
        $this->assertEqual($stale_group->group_id, '19994710');

        //second group
        $this->DAO->update($user_id = '1234567890', $stale_group->group_id, 'twitter');
        $stale_group = $this->DAO->findStalestMemberships($user_id = '1234567890', 'twitter');
        $this->assertTrue(is_object($stale_group));
        $this->assertEqual(get_class($stale_group), 'Group');
        $this->assertEqual($stale_group->group_id, '18864710');

        //third group
        $this->DAO->update($user_id = '1234567890', $stale_group->group_id, 'twitter');
        $stale_group = $this->DAO->findStalestMemberships($user_id = '1234567890', 'twitter');
        $this->assertNull($stale_group);
    }

    public function testGetNewMembershipsByDate() {
        $new_groups = $this->DAO->getNewMembershipsByDate('twitter', '1234567890');
        $this->assertEqual(count($new_groups), 1);
        $this->assertEqual($new_groups[0]->id, 3);
        $this->assertEqual($new_groups[0]->group_id, '19554710');
        $this->assertEqual($new_groups[0]->group_name, '@userx/anotherlist');
        $this->assertEqual($new_groups[0]->is_active, 1);
    }
}
