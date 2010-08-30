<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of UserErrorMySQLDAO
 *
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

        //Insert test data into test table
        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, location)
        VALUES (12, 'jack', 'Jack Dorsey', 'avatar.jpg', 'San Francisco');";
        $this->db->exec($q);
        $this->logger = Logger::getInstance();
    }

    /**
     * Tear down
     */
    public function tearDown() {
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
