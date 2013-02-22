<?php
/**
 *
 * ThinkUp/tests/TestOfInviteMySQLDAO.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfInviteMySQLDAO extends ThinkUpUnitTestCase {
    /**
     * Invite DAO to test
     * @var InviteDAO
     */
    protected $dao;
    public function setUp() {
        parent::setUp();
        $this->dao = DAOFactory::getDAO('InviteDAO');
    }

    public function tearDown() {
        $this->dao = null;
        parent::tearDown();
    }

    public function testAddInviteCode() {
        $result = $this->dao->addInviteCode('aabbddcc');
        $this->assertEqual($result, 1);
    }

    public function testDeleteInviteCode(){
        //delete nonexistent invite
        $result = $this->dao->deleteInviteCode('nonexistent');
        $this->assertEqual($result, 0);

        //insert an invite
        $builders = array();
        $builders[] = FixtureBuilder::build('invites', array('invite_code'=>'yoyoinvite', 'created_time'=>'-1d'));
        $result = $this->dao->deleteInviteCode('yoyoinvite');
        $this->assertEqual($result, 1);
    }

    public function testGetInviteCode() {
        $this->dao->addInviteCode('aabbddcc');

        $result = $this->dao->getInviteCode('aabbddcc');
        $this->assertEqual($result['invite_code'], 'aabbddcc');

        $result = $this->dao->getInviteCode('nonexistent');
        $this->assertNull($result);
    }

    public function testDoesInviteExist() {
        $this->dao->addInviteCode('aabbddcc');

        $this->assertTrue($this->dao->doesInviteExist('aabbddcc'));
        $this->assertFalse($this->dao->doesInviteExist('nonexistent'));
    }

    public function testIsInviteValid() {
        $builders = array();
        $builders[] = FixtureBuilder::build('invites', array('invite_code'=>'freshinvit', 'created_time'=>'-1d'));
        $builders[] = FixtureBuilder::build('invites', array('invite_code'=>'staleinvit', 'created_time'=>'-9d'));

        $this->assertTrue($this->dao->isInviteValid('freshinvit'));
        $this->assertFalse($this->dao->isInviteValid('staleinvit'));
    }
}