<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Owner.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.OwnerDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.OwnerMySQLDAO.php';

/**
 * Test of OwnerMySQL DAO implementation
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfOwnerMySQLDAO extends ThinkTankUnitTestCase {
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
        $this->DAO = new OwnerMySQLDAO();
        $q = "INSERT INTO tt_owners SET user_name='ThinkTankUser', full_name='ThinkTank J. User', user_email='ttuser@example.com', user_activated=0, user_pwd='XXX', activation_code='8888'";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_owners SET user_name='ThinkTankUser1', full_name='ThinkTank J. User1', user_email='ttuser1@example.com', user_activated=1, user_pwd='YYY'";
        PDODAO::$PDO->exec($q);

    }

    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test getByEmail();
     */
    public function testGetByEmail() {
        //owner exists
        $existing_owner = $this->DAO->getByEmail('ttuser@example.com');
        $this->assertTrue(isset($existing_owner));
        $this->assertEqual($existing_owner->user_name, 'ThinkTankUser');
        $this->assertEqual($existing_owner->full_name, 'ThinkTank J. User');
        $this->assertEqual($existing_owner->user_email, 'ttuser@example.com');

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
        $this->assertEqual($all_owners[0]->user_email, 'ttuser@example.com');
        $this->assertEqual($all_owners[1]->user_email, 'ttuser1@example.com');
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
     * Test getForLogin
     */
    public function testGetForLogin() {
        $owner_for_login = $this->DAO->getForLogin('ttuser@example.com');
        $this->assertTrue(!isset($owner_for_login));
        $owner_for_login = $this->DAO->getForLogin('ttuser1@example.com');
        $this->assertTrue(isset($owner_for_login));
    }


    /**
     * Test getPassword
     */
    public function testGetPassword() {
        //owner who doesn't exist
        $result = $this->DAO->getPass('idontexist@example.com');
        $this->assertTrue(!isset($result));
        //owner who is not activated
        $result = $this->DAO->getPass('ttuser@example.com');
        $this->assertTrue(!isset($result));
        //activated owner
        $result = $this->DAO->getPass('ttuser1@example.com');
        $this->assertEqual($result['pwd'], 'YYY');
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
    /**
     * Test updateActivate
     */
    public function testUpdateActivate() {
        $existing_owner = $this->DAO->getByEmail('ttuser@example.com');
        $this->assertTrue(!$existing_owner->is_activated);
        $this->DAO->updateActivate('ttuser@example.com');
        $existing_owner = $this->DAO->getByEmail('ttuser@example.com');
        $this->assertTrue($existing_owner->is_activated);
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
        $this->assertEqual($this->DAO->create('ttuser2@example.com', 's3cr3t', 'USA', 'XXX', 'ThinkTank J. User2'), 1);
        //Create new owner who does exist
        $this->assertEqual($this->DAO->create('ttuser@example.com', 's3cr3t', 'USA', 'XXX', 'ThinkTank J. User2'), 0);
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
}