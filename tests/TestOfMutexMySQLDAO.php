<?php
/**
 *
 * ThinkUp/tests/TestOfMutexMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Guillaume Boudreau
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
 * Test of MutexDAO
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Guillaume Boudreau
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfMutexMySQLDAO extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

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
        $this->assertFalse($mdao->isMutexFree('something'));
        $this->assertTrue($mdao->isMutexUsed('something'));

        $lock_obtained = $mdao->getMutex('something_else');
        $this->assertTrue($lock_obtained);
        $this->assertFalse($mdao->isMutexFree('something_else'));
        $this->assertTrue($mdao->isMutexUsed('something_else'));

        $lock_released = $mdao->releaseMutex('something_else');
        $this->assertTrue($lock_released);
        $this->assertTrue($mdao->isMutexFree('something_else'));
        $this->assertFalse($mdao->isMutexUsed('something_else'));

        // Lock for something is gone, since we locked something_else
        $this->assertTrue($mdao->isMutexFree('something'));
        $this->assertFalse($mdao->isMutexUsed('something'));
        $lock_released = $mdao->releaseMutex('something');
        $this->assertFalse($lock_released);

        // Lock for something_else was already released
        $this->assertTrue($mdao->isMutexFree('something_else'));
        $this->assertFalse($mdao->isMutexUsed('something_else'));
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
