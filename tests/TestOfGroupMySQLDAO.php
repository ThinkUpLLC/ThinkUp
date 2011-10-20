<?php
/**
 *
 * ThinkUp/tests/TestOfGroupMySQLDAO.php
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

class TestOfGroupMySQLDAO extends ThinkUpUnitTestCase {
    protected $DAO;
    protected $logger;

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->DAO = new GroupMySQLDAO();
        $this->builders = self::buildData();
    }

    protected function buildData() {
        $builders = array();
        //Insert test data into test table

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>18864710, 'group_name'=>'@someguy/a-list',
        'network' => 'twitter', 'active'=>1));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>19994710, 'group_name'=>'@somegal/her-list',
        'network' => 'twitter', 'active'=>1));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>19554710, 'group_name'=>'@userx/anotherlist',
        'network' => 'twitter', 'active'=>1));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>145669289, 'group_name'=>'@userx/oldlist',
        'network' => 'twitter', 'active'=>0));

        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
        $this->logger->close();
        $this->DAO = null;
    }

    public function testGroupExists() {
        $this->assertTrue($this->DAO->groupExists($group = 18864710, 'twitter'));
        $this->assertFalse($this->DAO->groupExists($group = 155555555, 'twitter'));

        //inactive groups
        $this->assertFalse($this->DAO->groupExists($group = 145669289, 'twitter', $active = true));
        $this->assertTrue($this->DAO->groupExists($group = 145669289, 'twitter'));
    }

    public function testUpdate() {
        $this->assertEqual($this->DAO->update($group = 18864710, $group_name = '@someguy/new-name', 'twitter'), 1);
        $this->assertEqual($this->DAO->update($group = 245232343, $group_name = '@someguy/new-name', 'twitter'), 0);

        $group = new Group(array('group_id' => 18864710, 'group_name' => '@someguy/another-name', 'network' => 'twitter'));
        $this->assertEqual($this->DAO->updateGroup($group), 1);
    }

    public function testDeactivate() {
        $this->assertEqual($this->DAO->deactivate($group = 18864710, 'twitter'), 1);
        $this->assertEqual($this->DAO->deactivate($group = 145669289, 'twitter'), 0);
    }

    public function testInsert() {
        $this->assertEqual($this->DAO->insert($group = 185823423, '@user/newlist', 'twitter'), 1);
        $this->assertTrue($this->DAO->groupExists($group = 185823423, 'twitter'));

        $this->assertFalse($this->DAO->groupExists($group = 133333333, 'twitter'));
        $group = new Group(array('group_id' => 133333333, 'group_name' => '@buddy/listy', 'network' => 'twitter'));
        $this->assertEqual($this->DAO->updateGroup($group), 1);
        $this->assertTrue($this->DAO->groupExists($group = 133333333, 'twitter'));
    }

}
