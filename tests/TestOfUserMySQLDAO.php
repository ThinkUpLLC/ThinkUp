<?php
/**
 *
 * ThinkUp/tests/TestOfUserMySQLDAO.php
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
 * Test of UserDAO
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfUserMySQLDAO extends ThinkUpUnitTestCase {

    /**
     * Constructor
     * @return TestOfUserDAO
     */
    public function __construct() {
        $this->UnitTestCase('UserMySQLDAO class test');
    }

    /**
     * Set Up
     */
    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    protected function buildData() {
        //Insert test data into test table
        $builders[] = FixtureBuilder::build('users', array('user_id'=>12, 'user_name'=>'jack',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'location'=>'San Francisco', 
        'network'=>'twitter'));

        //Insert test data into test table
        $builders[] = FixtureBuilder::build('users', array('user_id'=>13, 'user_name'=>'zuck',
        'full_name'=>'Mark Zuckerberg', 'avatar'=>'avatar.jpg', 'location'=>'San Francisco', 
        'network'=>'facebook'));

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
     * Test DAO constructor
     */
    public function testCreateNewUserDAO() {
        $dao = DAOFactory::getDAO('UserDAO');
        $this->assertTrue(isset($dao));
    }

    /**
     * Test isUserInDB
     */
    public function testIsUserInDB() {
        $udao = DAOFactory::getDAO('UserDAO');
        $this->assertTrue($udao->isUserInDB(12, 'twitter'));
        $this->assertFalse($udao->isUserInDB(13, 'twitter'));
        $this->assertTrue($udao->isUserInDB(13, 'facebook'));
    }

    /**
     * Test isUserInDBByName
     */
    public function testIsUserInDBByName() {
        $udao = DAOFactory::getDAO('UserDAO');
        $this->assertTrue($udao->isUserInDBByName('jack', 'twitter'));
        $this->assertFalse($udao->isUserInDBByName('gina', 'twitter'));
        $this->assertTrue($udao->isUserInDBByName('zuck', 'facebook'));
        $this->assertFalse($udao->isUserInDBByName('zuck', 'twitter'));

    }

    /**
     * Test getDetails when the user exists
     */
    public function testGetDetailsUserExists() {
        $udao = DAOFactory::getDAO('UserDAO');
        $user = $udao->getDetails(12, 'twitter');
        $this->assertEqual($user->id, 1);
        $this->assertEqual($user->user_id, 12);
        $this->assertEqual($user->username, 'jack');
        $this->assertEqual($user->network, 'twitter');
        $user = $udao->getDetails(13, 'facebook');
        $this->assertEqual($user->id, 2);
        $this->assertEqual($user->user_id, 13);
        $this->assertEqual($user->username, 'zuck');
        $this->assertEqual($user->network, 'facebook');

        $user = $udao->getDetails(13, 'twitter');
        $this->assertTrue(!isset($user));
    }

    /**
     * Test getDetails when user does not exist
     */
    public function testGetDetailsUserDoesNotExist() {
        $udao = DAOFactory::getDAO('UserDAO');
        $user = $udao->getDetails(13, 'twitter');
        $this->assertTrue(!isset($user));
    }

    /**
     * Test update individual user
     */
    public function testUpdateUser() {
        $udao = DAOFactory::getDAO('UserDAO');

        $uarr = array('user_id'=>13, 'user_name'=>'ginatrapani', 'full_name'=>'Gina Trapani',
        'avatar'=>'avatar.jpg', 'location'=>'NYC', 'description'=>'Blogger', 'url'=>'http://ginatrapani.org', 
        'is_protected'=>0, 'follower_count'=>5000, 'post_count'=>1000, 'joined'=>'3/6/2007', 'network'=>'twitter');
        $user = new User($uarr, 'Test Insert');
        $this->assertEqual($udao->updateUser($user), 1, "1 user inserted");
        $user_from_db = $udao->getDetails(13, 'twitter');
        $this->assertEqual($user_from_db->user_id, 13);
        $this->assertEqual($user_from_db->username, 'ginatrapani');
        $this->assertEqual($user_from_db->avatar, 'avatar.jpg');
        $this->assertEqual($user_from_db->location, 'NYC');

        $uarr = array('user_id'=>13, 'user_name'=>'ginatrapani', 'full_name'=>'Gina Trapani ',
        'avatar'=>'avatara.jpg', 'location'=>'San Diego', 'description'=>'Blogger', 'url'=>'http://ginatrapani.org', 
        'is_protected'=>0, 'follower_count'=>5000, 'post_count'=>1000, 'joined'=>'3/6/2007', 'network'=>'twitter');
        $user1 = new User($uarr, 'Test Update');
        $this->assertEqual($udao->updateUser($user1), 1, "1 row updated");
        $user_from_db = $udao->getDetails(13, 'twitter');
        $this->assertEqual($user_from_db->user_id, 13);
        $this->assertEqual($user_from_db->username, 'ginatrapani');
        $this->assertEqual($user_from_db->avatar, 'avatara.jpg');
        $this->assertEqual($user_from_db->location, 'San Diego');
    }

    /**
     * Test updateUsers for multiple user update
     */
    public function testUpdateUsers() {
        $udao = DAOFactory::getDAO('UserDAO');

        $user_array1 = array('id'=>2, 'user_id'=>13, 'user_name'=>'ginatrapani', 'full_name'=>'Gina Trapani',
        'avatar'=>'avatar.jpg', 'location'=>'NYC', 'description'=>'Blogger', 'url'=>'http://ginatrapani.org', 
        'is_protected'=>0, 'follower_count'=>5000, 'post_count'=>1000, 'joined'=>'3/6/2007', 'network'=>'twitter');
        $user1 = new User($user_array1, 'Test');
        $user_array2 = array('id'=>3, 'user_id'=>14, 'user_name'=>'anildash', 'full_name'=>'Anil Dash',
        'avatar'=>'avatar.jpg', 'location'=>'NYC', 'description'=>'Blogger', 'url'=>'http://ginatrapani.org', 
        'is_protected'=>0, 'follower_count'=>5000, 'post_count'=>1000, 'joined'=>'3/6/2007', 'network'=>'twitter');
        $user2 = new User($user_array2, 'Test');

        $users_to_update = array($user1, $user2);

        $this->assertTrue($udao->updateUsers($users_to_update) == 2);
    }

    /**
     * Test getUserByName when user exists
     */
    public function testGetUserByNameUserExists() {
        $udao = DAOFactory::getDAO('UserDAO');

        $user = $udao->getUserByName('jack', 'twitter');
        $this->assertEqual($user->id, 1);
        $this->assertEqual($user->user_id, 12);
        $this->assertEqual($user->username, 'jack');
        $this->assertEqual($user->full_name, 'Jack Dorsey');
        $this->assertEqual($user->location, 'San Francisco');
    }

    /**
     * Test getUserByName when user does not exist
     */
    public function testGetUserByNameUserDoesNotExist() {
        $udao = DAOFactory::getDAO('UserDAO');

        $user = $udao->getUserByName('gina', 'twitter');
        $this->assertEqual($user, null);
    }
}
