<?php
/**
 *
 * ThinkUp/tests/TestOfMutexMySQLDAO.php
 *
 * Copyright (c) 2009-2011 Guillaume Boudreau
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
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of MutexDAO
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Guillaume Boudreau
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 *
 */
class TestOfMutexMySQLDAO extends ThinkUpUnitTestCase {

    /**
     * Constructor
     * @return TestOfMutexMySQLDAO
     */
    public function __construct() {
        $this->UnitTestCase('MutexMySQLDAO class test');
    }

    /**
     * Set Up
     */
    public function setUp() {
        parent::setUp();
    }

    /**
     * Tear down
     */
    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test DAO constructor
     */
    public function testCreateNewMutexDAO() {
        $dao = DAOFactory::getDAO('MutexDAO');
        $this->assertTrue(isset($dao));
    }

    /**
     * Test getMutex
     */
    public function testGetMutex() {
        $mdao = DAOFactory::getDAO('MutexDAO');
        $lock_obtained = $mdao->getMutex('something');
        $this->assertTrue($lock_obtained);
        $lock_obtained = $mdao->getMutex('something_else');
        $this->assertTrue($lock_obtained);
        $lock_released = $mdao->releaseMutex('something_else');
        $this->assertTrue($lock_released);
        // Lock for something is gone, since we locked something_else
        $lock_released = $mdao->releaseMutex('something');
        $this->assertFalse($lock_released);
        // Lock for something_else was already released
        $lock_released = $mdao->releaseMutex('something_else');
        $this->assertFalse($lock_released);
    }

    /**
     * Test releaseMutex
     */
    public function testReleaseMutex() {
        $mdao = DAOFactory::getDAO('MutexDAO');
        $lock_obtained = $mdao->getMutex('something');
        $this->assertTrue($lock_obtained);

        // Checking release works
        $lock_released = $mdao->releaseMutex('something');
        $this->assertTrue($lock_released);

        // There is no lock to release
        $lock_released = $mdao->releaseMutex('something');
        $this->assertFalse($lock_released);
    }
}
