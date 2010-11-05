<?php
/**
 *
 * ThinkUp/tests/TestOfUserErrorMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * Test of UserErrorMySQLDAO
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfUserErrorMySQLDAO extends ThinkUpUnitTestCase {

    /**
     * Constructor
     * @return TestOfUserDAO
     */
    public function __construct() {
        $this->UnitTestCase('UserErrorMySQLDAO class test');
    }

    /**
     * Set Up
     */
    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    protected function buildData() {
        $builders = array();
        //Insert test data into test table
        $builders[] = FixtureBuilder::build('users', array('user_id'=>12, 'user_name'=>'jack', 
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'location'=>'San Francisco'));
//        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, location)
//        VALUES (12, 'jack', 'Jack Dorsey', 'avatar.jpg', 'San Francisco');";
//        $this->db->exec($q);
        $this->logger = Logger::getInstance();
        return $builders;
    }

    /**
     * Tear down
     */
    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
        $this->logger->close();
    }

    /**
     * Test insert
     */
    public function testInsertError() {
        $dao = DAOFactory::getDAO('UserErrorDAO');

        $this->assertEqual($dao->insertError(10, 500, 'User error', 11, 'twitter'), 1);
    }
}
