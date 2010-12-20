<?php
/**
 *
 * ThinkUp/tests/TestOfOwnerMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Michael Louis Thaler
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
 * Test of OwnerMySQL DAO implementation
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Michael Louis Thaler
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfOwnerMySQLDAO extends ThinkUpUnitTestCase {
    /**
     *
     * @var OwnerMySQLDAO
     */
    protected $dao;
    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('OwnerMySQLDAO class test');
    }

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
        $this->DAO = new OwnerMySQLDAO();
    }

    protected function buildData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('owners', array('full_name'=>'ThinkUp J. User',
        'email'=>'ttuser@example.com', 'is_activated'=>0, 'pwd'=>'XXX', 'activation_code'=>'8888', 
        'account_status'=>''));

        $builders[] = FixtureBuilder::build('owners', array('full_name'=>'ThinkUp J. User1',
        'email'=>'ttuser1@example.com', 'is_activated'=>1, 'pwd'=>'YYY', 'account_status'=>''));

        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    /**
     * Test getByEmail();
     */
    public function testGetByEmail() {
        //owner exists
        $existing_owner = $this->DAO->getByEmail('ttuser@example.com');
        $this->assertTrue(isset($existing_owner));
        $this->assertEqual($existing_owner->full_name, 'ThinkUp J. User');
        $this->assertEqual($existing_owner->email, 'ttuser@example.com');
        $this->assertEqual($existing_owner->failed_logins, 0);
        $this->assertEqual($existing_owner->account_status, '');

        //owner does not exist
        $non_existing_owner = $this->DAO->getByEmail('idontexist@example.com');
        $this->assertTrue(!isset($non_existing_owner));
    }

    /**
     * Test getAllOwners
     */
    public function testGetAllOwners() {
        $all_owners = $this->DAO->getAllOwners();
        $this->assertEqual(sizeof($all_owners), 2);
        $this->assertEqual($all_owners[0]->email, 'ttuser@example.com');
        $this->assertEqual($all_owners[1]->email, 'ttuser1@example.com');
    }

    /**
     * Test getAdminOwners
     */
    public function testGetAdminOwners() {
        // no admins
        $admin_owners = $this->DAO->getAdmins();
        $this->assertNull($admin_owners, 'no admins');

        // build 1 valid admin and two invalid admins
        $builder1 = FixtureBuilder::build('owners', array('is_admin' => 1, 'is_activated' => 1, 'email' => 'm@w.nz'));
        $builder2 = FixtureBuilder::build('owners', array('is_admin' => 0, 'is_activated' => 1, 'email' => 'm2@w.nz'));
        $builder3 = FixtureBuilder::build('owners', array('is_admin' => 1, 'is_activated' => 0, 'email' => 'm3@w.nz'));

        $admin_owners = $this->DAO->getAdmins();
        $this->assertNotNull($admin_owners, 'an admin');
        $this->assertEqual(count($admin_owners), 1, 'an admin');
        $this->assertEqual($admin_owners[0]->is_admin, 1, 'valid admin');
        $this->assertEqual($admin_owners[0]->is_activated, 1, 'valid admin');
        $this->assertEqual($admin_owners[0]->email, 'm@w.nz', 'valid admin with email');

        // add one more valid admin
        $builder4 = FixtureBuilder::build('owners', array('is_admin' => 1, 'is_activated' => 1, 'email' => 'm4@w.nz'));

        $admin_owners = $this->DAO->getAdmins();
        $this->assertNotNull($admin_owners, 'we have admins admin');
        $this->assertEqual(count($admin_owners), 2, 'two admins');
        $this->assertEqual($admin_owners[0]->is_admin, 1, 'valid admin');
        $this->assertEqual($admin_owners[0]->is_activated, 1, 'valid admin');
        $this->assertEqual($admin_owners[0]->email, 'm@w.nz', 'valid admin with email');
        $this->assertEqual($admin_owners[1]->is_admin, 1, 'valid admin');
        $this->assertEqual($admin_owners[1]->is_activated, 1, 'valid admin');
        $this->assertEqual($admin_owners[1]->email, 'm4@w.nz', 'valid admin with email');
    }

    /**
     * Test doesOwnerExist
     */
    public function testDoesOwnerExist() {
        $this->assertTrue($this->DAO->doesOwnerExist('ttuser@example.com'));
        $this->assertTrue($this->DAO->doesOwnerExist('ttuser1@example.com'));
        $this->assertTrue(!$this->DAO->doesOwnerExist('idontexist@example.com'));
    }

    /**
     * Test getPassword
     */
    public function testGetPassword() {
        //owner who doesn't exist
        $result = $this->DAO->getPass('idontexist@example.com');
        $this->assertFalse($result);
        //owner who is not activated
        $result = $this->DAO->getPass('ttuser@example.com');
        $this->assertFalse($result);
        //activated owner
        $result = $this->DAO->getPass('ttuser1@example.com');
        $this->assertEqual($result, 'YYY');
    }

    /**
     * Test getActivationCode
     */
    public function testGetActivationCode() {
        //owner who doesn't exist
        $result = $this->DAO->getActivationCode('idontexist@example.com');
        $this->assertTrue(!isset($result));
        //owner who is not activated
        $result = $this->DAO->getActivationCode('ttuser@example.com');
        $this->assertEqual($result['activation_code'], '8888');
    }

    public function testActivate() {
        $existing_owner = $this->DAO->getByEmail('ttuser@example.com');
        $this->assertTrue(!$existing_owner->is_activated);
        $this->DAO->activateOwner('ttuser@example.com');
        $existing_owner = $this->DAO->getByEmail('ttuser@example.com');
        $this->assertTrue($existing_owner->is_activated);
        $this->DAO->deactivateOwner('ttuser@example.com');
        $existing_owner = $this->DAO->getByEmail('ttuser@example.com');
        $this->assertFalse($existing_owner->is_activated);
    }

    /**
     * Test updatePassword
     */
    public function testUpdatePassword() {
        $this->assertEqual($this->DAO->updatePassword('ttuser@example.com', '8989'), 1);
        $this->assertEqual($this->DAO->updatePassword('dontexist@example.com', '8989'), 0);
    }

    /**
     * Test create
     */
    public function testCreate() {
        //Create new owner who does not exist
        $this->assertEqual($this->DAO->create('ttuser2@example.com', 's3cr3t', 'XXX', 'ThinkUp J. User2'), 1);
        //Create new owner who does exist
        $this->assertEqual($this->DAO->create('ttuser@example.com', 's3cr3t', 'XXX', 'ThinkUp J. User2'), 0);
    }

    /**
     * Test updateLastLogin
     */
    public function testUpdateLastLogin() {
        //Update owner who does not exist
        $this->assertEqual($this->DAO->updateLastLogin('ttuser2@example.com'), 0);
        //Update wner who does exist
        $this->assertEqual($this->DAO->updateLastLogin('ttuser@example.com'), 1);
    }

    /**
     * Test updatePasswordToken
     */
    public function testUpdatePasswordToken() {
        $this->assertEqual($this->DAO->updatePasswordToken('ttuser@example.com', 'sample_token'), 1);
        $this->assertEqual($this->DAO->updatePasswordToken('dontexist@example.com', 'sample_token'), 0);
    }

    /**
     * Test getByPasswordToken
     */
    public function testGetByPasswordToken() {
        $this->DAO->updatePasswordToken('ttuser@example.com', 'sample_token');
        $owner = $this->DAO->getByPasswordToken('sample'); // searches for first half of token
        $this->assertEqual($owner->email, 'ttuser@example.com');
    }

    /**
     * Test createAdmin and doesAdminExist
     */
    public function testCreateAdminAndDoesAdminExist() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new OwnerMySQLDAO($config_array);

        $this->assertFalse($dao->doesAdminExist());
        $dao->createAdmin('test@example.com', 'password', 'adfadfad', 'My Full Name');
        $this->assertTrue($dao->doesAdminExist());
    }

    public function testPromoteToAdmin() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new OwnerMySQLDAO($config_array);

        $this->assertFalse($dao->doesAdminExist());
        $result = $dao->promoteToAdmin('ttuser1@example.com');
        $this->assertEqual($result, 1); //one row updated

        $this->assertTrue($dao->doesAdminExist());
    }

    public function testFailedLoginManagement() {
        $owner = $this->DAO->getByEmail('ttuser@example.com');
        //default value is 0
        $this->assertEqual($owner->failed_logins, 0);

        //increment to 1
        $this->assertTrue($this->DAO->incrementFailedLogins('ttuser@example.com'));
        $owner = $this->DAO->getByEmail('ttuser@example.com');
        $this->assertEqual($owner->failed_logins, 1);

        //return false for non-existent owner
        $this->assertFalse($this->DAO->incrementFailedLogins('idontexist@example.com'));

        //increment to 2
        $this->assertTrue($this->DAO->incrementFailedLogins('ttuser@example.com'));
        $owner = $this->DAO->getByEmail('ttuser@example.com');
        $this->assertEqual($owner->failed_logins, 2);

        //reset to 0
        $this->assertTrue($this->DAO->resetFailedLogins('ttuser@example.com'));
        $owner = $this->DAO->getByEmail('ttuser@example.com');
        $this->assertEqual($owner->failed_logins, 0);
    }

    public function testAccountStatus() {
        $owner = $this->DAO->getByEmail('ttuser@example.com');
        //default value is empty string (set in buildData)
        $this->assertEqual($owner->account_status, '');

        $this->DAO->setAccountStatus('ttuser@example.com', 'this is a test account status');
        $owner = $this->DAO->getByEmail('ttuser@example.com');
        //new status
        $this->assertEqual($owner->account_status, 'this is a test account status');
    }
}
