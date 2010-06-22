<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.UserDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.UserMySQLDAO.php';

/**
 * Test of UserErrorMySQLDAO
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfUserErrorMySQLDAO extends ThinkTankUnitTestCase {

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
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, location)
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
