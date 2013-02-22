<?php
/**
 *
 * ThinkUp/tests/TestOfGroupMySQLDAO.php
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

class TestOfGroupMySQLDAO extends ThinkUpUnitTestCase {
    /**
     * @var GroupMySQLDAO
     */
    protected $DAO;
    /**
     * @var Logger
     */
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

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>'18864710', 'group_name'=>'@someguy/a-list',
        'network' => 'twitter', 'is_active'=>1));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>'19994710', 'group_name'=>'@somegal/her-list',
        'network' => 'twitter', 'is_active'=>1));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>'19554710', 'group_name'=>'@userx/anotherlist',
        'network' => 'twitter', 'is_active'=>1));

        $builders[] = FixtureBuilder::build('groups', array('group_id'=>'145669289', 'group_name'=>'@userx/oldlist',
        'network' => 'twitter', 'is_active'=>0));

        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
        $this->logger->close();
        $this->DAO = null;
    }

    public function testIsGroupInStorage() {
        $this->assertTrue($this->DAO->isGroupInStorage($group = '18864710', 'twitter'));
        $this->assertFalse($this->DAO->isGroupInStorage($group = '155555555', 'twitter'));

        //inactive groups
        $this->assertFalse($this->DAO->isGroupInStorage($group = '145669289', 'twitter', $active = true));
        $this->assertTrue($this->DAO->isGroupInStorage($group = '145669289', 'twitter'));
    }

    public function testUpdateOrInsertGroup() {
        //unpopulated Group object
        $group = new Group();
        $this->assertFalse($this->DAO->updateOrInsertGroup($group));

        $group = new Group(array('group_id'=>'18864710', 'group_name'=>'@someguy/another-name', 'network'=>'twitter',
        'is_active'=>1, 'last_seen'=>'2011-10-21', 'first_seen'=>'2011-10-21'));
        $this->assertTrue($this->DAO->updateOrInsertGroup($group));
    }

    public function testUpdate() {
        $this->assertEqual($this->DAO->update($group = '18864710', $group_name = '@someguy/new-name', 'twitter'), 1);
        $this->assertEqual($this->DAO->update($group = '245232343', $group_name = '@someguy/new-name', 'twitter'), 0);
    }

    public function testDeactivate() {
        $this->assertEqual($this->DAO->deactivate($group = '18864710', 'twitter'), 1);
        $this->assertEqual($this->DAO->deactivate($group = '145669289', 'twitter'), 0);
    }

    public function testInsert() {
        $this->assertEqual($this->DAO->insert($group_id='185823423', '@user/newlist', 'twitter'), 5);
        $this->assertTrue($this->DAO->isGroupInStorage($group_id = '185823423', 'twitter'));

        $this->assertFalse($this->DAO->isGroupInStorage($group_id = '133333333', 'twitter'));
        $group = new Group(array('group_id'=>'133333333', 'group_name'=>'@buddy/listy', 'network'=>'twitter',
        'is_active'=>1, 'last_seen'=>'2011-10-21', 'first_seen'=>'2011-10-21'));
        $this->assertTrue($this->DAO->updateOrInsertGroup($group));
        $this->assertTrue($this->DAO->isGroupInStorage($group = '133333333', 'twitter'));
    }

    public function testSetMetadata() {
        $val['id'] = 101;
        $val['group_id'] = 1001;
        $val['network'] = 'twitter';
        $val['group_name'] = '@mirqamar/fivebyfive';
        $val['is_active'] = 1;
        $val['first_seen'] = '2012-06-13';
        $val['last_seen'] = '2012-06-13';

        $group = new Group($val);
        $group->setMetadata();
        $this->assertEqual($group->url, 'http://twitter.com/mirqamar/fivebyfive');
        $this->assertEqual($group->keyword, 'fivebyfive');
    }
}
