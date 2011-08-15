<?php
/**
 *
 * ThinkUp/tests/TestOfOwnerMySQLDAO.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Michael Louis Thaler
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
 * @copyright 2009-2011 Gina Trapani, Michael Louis Thaler
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

    public function setUp() {
        parent::setUp();
        $this->DAO = new OwnerMySQLDAO();
        $this->builders = self::buildData();
        $this->config = Config::getInstance();
        $this->prefix = $this->config->getValue('table_prefix');
    }

    protected function buildData() {
        $builders = array();
        
        $salt = 'salt';
        $pwd1 = $this->DAO->pwdCrypt('pwd1');
        $pwd2 = $this->DAO->pwdCrypt('pwd2');
        $pwd3 = $this->DAO->generateUniqueSaltedPassword('pwd3', $salt);
        
        $builders[] = FixtureBuilder::build('owners', array('full_name'=>'ThinkUp J. User',
        'email'=>'ttuser@example.com', 'is_activated'=>0, 'pwd'=>$pwd1, 'pwd_salt'=>null, 'activation_code'=>'8888', 
        'account_status'=>'', 'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c'));

        $builders[] = FixtureBuilder::build('owners', array('full_name'=>'ThinkUp J. User1',
        'email'=>'ttuser1@example.com', 'is_activated'=>1, 'pwd'=>$pwd2, 'pwd_salt'=>null, 'account_status'=>''));
               
        $builders[] = FixtureBuilder::build('owners', array('full_name'=>'Salted User',
        'email'=>'salteduser@example.com', 'is_activated'=>1, 'pwd'=>$pwd3, 'pwd_salt'=>$salt, 
        'account_status'=>''));

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
        $this->assertEqual($existing_owner->api_key, 'c9089f3c9adaf0186f6ffb1ee8d6501c');

        //owner does not exist
        $non_existing_owner = $this->DAO->getByEmail('idontexist@example.com');
        $this->assertTrue(!isset($non_existing_owner));
    }

    /**
     * Test getAllOwners
     */
    public function testGetAllOwners() {
        $all_owners = $this->DAO->getAllOwners();
        $this->assertEqual(sizeof($all_owners), 3);
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
        $this->assertEqual($result, $this->DAO->pwdCrypt('pwd2'));
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
        $this->assertEqual($this->DAO->create('ttuser2@example.com', 's3cr3t', 'salt', 'XXX', 'ThinkUp J. User2'), 1);
        //Create new owner who does exist
        $this->assertEqual($this->DAO->create('ttuser@example.com', 's3cr3t', 'salt', 'XXX', 'ThinkUp J. User2'), 0);

        // we should validate this created user data
        $sql = "select *, unix_timestamp(joined) as joined_ts from " .
        $this->prefix . "owners where email = 'ttuser2@example.com'";
        $stmt = OwnerMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual('ThinkUp J. User2', $data['full_name']);
        $this->assertEqual('s3cr3t', $data['pwd']);
        $this->assertEqual(0, $data['is_activated']);
        $this->assertEqual(0, $data['is_admin']);
        $this->assertEqual('0000-00-00', $data['last_login']);
        $this->assertEqual('ttuser2@example.com', $data['email']);
        $this->assertTrue( time() < ($data['joined_ts'] + (60 * 60 * 25) )); // joind within last 25 hours
        $this->assertNotNull($data['api_key']);
        $this->assertEqual(strlen($data['api_key']), 32); //md5 32 char api key
    }

    /**
     * Test reset api key
     */
    public function testResetAPIKey() {
        // bad user id, key not reset
        $new_api_key = $this->DAO->resetAPIKey(-99);
        $this->assertFalse($new_api_key);
        $sql = "select id, api_key from " . $this->prefix . "owners where id = " .
        $this->builders[0]->columns['last_insert_id'];
        $stmt = OwnerMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($this->builders[0]->columns['api_key'], $data['api_key']); // should be the same api_key

        // good user id, key reset
        $new_api_key = $this->DAO->resetAPIKey($this->builders[0]->columns['last_insert_id']);
        $this->assertEqual(strlen($data['api_key']), 32); //md5 32 char api key
        $sql = "select id, api_key from " . $this->prefix . "owners where id = " .
        $this->builders[0]->columns['last_insert_id'];
        $stmt = OwnerMysqlDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($this->builders[0]->columns['last_insert_id'], $data['id']);
        $this->assertNotEqual($this->builders[0]->columns['api_key'], $data['api_key']); // should be a new api_key
        $this->assertNotNull($data['api_key']);
        $this->assertEqual(strlen($data['api_key']), 32); //md5 32 char api key
        $this->assertEqual($new_api_key, $data['api_key']);
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
        $dao->createAdmin('test@example.com', 'password', 'salt', 'adfadfad', 'My Full Name');
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

    public function testSetOwnerActive() {
        $builders_array = array();
        # build our data
        $builders_array[] = FixtureBuilder::build('owners', array('full_name'=>'ThinkUp J. User',
        'email'=>'ttuser2@example.com', 'is_activated'=>0));

        $builders_array[] = FixtureBuilder::build('owners', array('full_name'=>'ThinkUp J. User',
        'email'=>'ttuser3@example.com', 'is_activated'=>1));
        # init our dao
        $dao = new OwnerMySQLDAO();

        // flip form false to true
        $test_owners_records = $builders_array[0]->columns;
        $id = $test_owners_records['last_insert_id'];
        $this->assertTrue($dao->setOwnerActive($id, 1));
        $owner = $this->DAO->getByEmail('ttuser2@example.com');
        //new status
        $this->assertTrue($owner->is_activated);

        // already true
        $test_owners_records = $builders_array[1]->columns;
        $id = $test_owners_records['last_insert_id'];
        // nothing updated, so false
        $this->assertFalse($dao->setOwnerActive($id, 1));
        $owner = $this->DAO->getByEmail('ttuser3@example.com');
        //new status
        $this->assertTrue($owner->is_activated);

        // flip to false
        $test_owners_records = $builders_array[0]->columns;
        $id = $test_owners_records['last_insert_id'];
        $this->assertTrue($dao->setOwnerActive($id, 0));

        $owner = $this->DAO->getByEmail('ttuser2@example.com');
        //new status
        $this->assertFalse($owner->is_activated);

    }
    
    public function testGenerateSalt(){
        // Ensure a unique salt is generated for each user
        $salt_array = array();
        $salt_array[] = $this->DAO->generateSalt('me@me.com');
        $salt_array[] = $this->DAO->generateSalt('you@you.com');
        $salt_array[] = $this->DAO->generateSalt('testing@test.com');
        $salt_array[] = $this->DAO->generateSalt('hello@google.com');
        $salt_array[] = $this->DAO->generateSalt('me+cats@lol.com');
        $this->assertEqual(sizeof($salt_array), sizeof(array_unique($salt_array)), 'Size of arrays should be equal,
        each salt should be unique');
    }
    
    public function testGeneratePassword(){
        $this->assertEqual($this->DAO->generateUniqueSaltedPassword('password', 'salt'), 
        '212a4f83b0966b24188a7ac4f01abc81d70c6e3c118bd37ff789e3dd2109560f', "Password Generation is faulty, should be 
        equal");
    }
    
    public function testGetSaltByEmail(){     
        $this->assertEqual($this->DAO->getSaltByEmail('salteduser@example.com'), 'salt', 'Incorrect salt returned');
        $this->assertNotEqual($this->DAO->getSaltByEmail('salteduser@example.com'), 'wrongsalt', 
         'Wrong salt returned');
    }
    
    public function testUpdateSalt(){
        $this->assertEqual($this->DAO->updateSalt('salteduser@example.com', 'newsalt'), 1, 'Problem updating salt');
        $this->assertEqual($this->DAO->updateSalt('nonexistentuser@example.com', 'newsalt'), 0, 
        'Nothing should happen');
    }
    
    public function testCheckUniqueSaltedPassword(){
        $pass = $this->DAO->generateUniqueSaltedPassword('pwd3', 'salt');
        $this->assertTrue($this->DAO->checkUniqueSaltedPassword('salteduser@example.com', $pass), 'Password should 
        be correct');
        $this->assertFalse($this->DAO->checkUniqueSaltedPassword('salteduser@example.com', 'wrongpass'), 'Password 
        should be incorrect');
    }
    
    public function testIsOwnerAuthorized(){
        // Check a correct unique salted password
        $this->assertTrue($this->DAO->isOwnerAuthorized('salteduser@example.com', 'pwd3'), 'Credentials should be
        valid');
        // Check a correct non-unique salted password
        $this->assertTrue($this->DAO->isOwnerAuthorized('ttuser1@example.com', 'pwd2'), 'Credentials should be
        valid');
        // Check a incorrect unique salted password
        $this->assertFalse($this->DAO->isOwnerAuthorized('salteduser@example.com', 'wrongpass'), 'Credentials should be
        invalid');
        // Check a incorrect non-unique salted password
        $this->assertFalse($this->DAO->isOwnerAuthorized('ttuser1@example.com', 'wrong'), 'Credentials should be
        invalid');
    }
    
   public function testPwdCrypt() {
       // This function returns the saltedsha1(password) so we check if the correct saltedsha1 hash for cat is returned
       $this->assertEqual($this->DAO->pwdCrypt('cat'), '17ce242925679a052c18e8b3b5418052201d6554',
       'Wrong Hash Returned' );
    }

    public function testPwdCheck() {
        /*
         * Password check confirms that the password supplies matches either the MD5, SHA1 or salted SHA1 hash of the
         * password. So we need to check these 3 cases.
         */  
        
        // MD5 hash
        $this->assertTrue($this->DAO->pwdCrypt('cat', 'd077f244def8a70e5ea758bd8352fcd8'), 'MD5 hash verification
        is broke' );
        
        // SHA1 hash
        $this->assertTrue($this->DAO->pwdCrypt('cat', '9d989e8d27dc9e0ec3389fc855f142c3d40f0c50'),
        'SHA1 hash verification is broke' );
        
        // Salted SHA1 hash
        $this->assertTrue($this->DAO->pwdCrypt('cat', '17ce242925679a052c18e8b3b5418052201d6554'),
        'Salted SHA1 hash verification is broke' );
    }
    
}
