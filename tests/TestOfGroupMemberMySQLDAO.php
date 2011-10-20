<?php
/**
 *
 * ThinkUp/tests/TestOfGroupMemberMySQLDAO.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, SwellPath, Inc.
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * @copyright 2009-2011 Gina Trapani, SwellPath, Inc.
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfGroupMemberMySQLDAO extends ThinkUpUnitTestCase {
    protected $DAO;
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

        $builders[] = FixtureBuilder::build('users', array('user_id'=>1234567890, 'user_name'=>'jack',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>150210, 'friend_count'=>124));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>1623457890, 'user_name'=>'private',
        'full_name'=>'Private Poster', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>35342,
        'friend_count'=>1345));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>18864710, 'group_name'=>'@someguy/a-list',
        'network' => 'twitter', 'active'=>1));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>19994710, 'group_name'=>'@somegal/her-list',
        'network' => 'twitter', 'active'=>1));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>19554710, 'group_name'=>'@userx/anotherlist',
        'network' => 'twitter', 'active'=>1));

        // Jack's in two groups
        $builders[] = FixtureBuilder::build('group_members', array('member_user_id'=>1234567890, 'group_id'=>18864710,
        'active' => 1, 'network'=>'twitter', 'last_seen' => '-1h'));

        // one stale
        $builders[] = FixtureBuilder::build('group_members', array('member_user_id'=>1234567890, 'group_id'=>19994710,
        'active' => 1, 'network'=>'twitter', 'last_seen' => '-3d'));

        // Private Poster is active in one group
        $builders[] = FixtureBuilder::build('group_members', array('member_user_id'=>1623457890, 'group_id'=>19994710,
        'active' => 1, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('group_members', array('member_user_id'=>1623457890, 'group_id'=>19554710,
        'active' => 0, 'network'=>'twitter'));

        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
        $this->logger->close();
        $this->DAO = null;
    }

    public function testGroupMemberExists() {
        $this->assertTrue($this->DAO->groupMemberExists($user = 1234567890, $group = 18864710, 'twitter'));
        $this->assertFalse($this->DAO->groupMemberExists($user = 1623457890, $group = 18864710, 'twitter'));

        //inactive group membership
        $this->assertFalse($this->DAO->groupMemberExists($user = 1623457890, $group = 19554710, 'twitter', $active = true));
        $this->assertTrue($this->DAO->groupMemberExists($user = 1623457890, $group = 19554710, 'twitter'));

        //active group membership
        $this->assertTrue($this->DAO->groupMemberExists($user = 1234567890, $group = 18864710, 'twitter', $active = true));
    }

    public function testUpdate() {
        $this->assertEqual($this->DAO->update($user = 1234567890, $group = 18864710, 'twitter'), 1);
        $this->assertEqual($this->DAO->update($user = 1623457890, $group = 18864710, 'twitter'), 0);
    }

    public function testDeactivate() {
        $this->assertEqual($this->DAO->deactivate($user = 1234567890, $group = 18864710, 'twitter'), 1);
        $this->assertEqual($this->DAO->deactivate($user = 1623457890, $group = 19554710, 'twitter'), 0);
    }

    public function testInsert() {
        $this->assertEqual($this->DAO->insert($user = 1623457890, $group = 18864710, 'twitter'), 1);
        $this->assertTrue($this->DAO->groupMemberExists($user = 1623457890, $group = 18864710, 'twitter'));
    }

    public function testCountTotalGroups() {
        $this->assertEqual($this->DAO->countTotalGroups($user = 1234567890, 'twitter'), 2);
        $this->assertEqual($this->DAO->countTotalGroups($user = 1623457890, 'twitter', $active = false), 2);
        $this->assertEqual($this->DAO->countTotalGroups($user = 1623457890, 'twitter', $active = true), 1);
    }

    public function testGetFormerGroups() {
        $this->assertEqual(count($this->DAO->getFormerGroups($user = 1234567890, 'twitter')), 0);
        $this->assertEqual(count($this->DAO->getFormerGroups($user = 1623457890, 'twitter')), 1);
    }

    public function testFindStalestMemberships() {
        $staleGroup = $this->DAO->findStalestMemberships($user = 1234567890, 'twitter');
        $this->assertTrue(is_object($staleGroup));
        $this->assertEqual(get_class($staleGroup), 'Group');
        $this->assertEqual($staleGroup->group_id, 19994710);

        $this->DAO->update($user = 1234567890, $staleGroup->group_id, 'twitter');
        $staleGroup = $this->DAO->findStalestMemberships($user = 1234567890, 'twitter');
        $this->assertNull($staleGroup);
    }

}

