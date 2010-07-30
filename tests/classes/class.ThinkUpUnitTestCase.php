<?php
require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpTestDatabaseHelper.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.DAOFactory.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.MySQLDAO.deprecated.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PDODAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Database.deprecated.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.LoggerSlowSQL.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Config.php';
require_once $SOURCE_ROOT_PATH.'tests/config.tests.inc.php';

/**
 * ThinkUp Unit Test Case
 *
 * Adds database support to the basic unit test case, for tests that need ThinkUp's database structure.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ThinkUpUnitTestCase extends ThinkUpBasicUnitTestCase {
    var $db;
    var $conn;
    var $testdb_helper;

    /**
     * Create a clean copy of the ThinkUp database structure
     */
    public function setUp() {
        parent::setUp();
        global $THINKUP_CFG;
        global $TEST_DATABASE;

        //Override default CFG values
        $THINKUP_CFG['db_name'] = $TEST_DATABASE;
        $config = Config::getInstance();
        $config->setValue('db_name', $TEST_DATABASE);

        $this->db = new Database($THINKUP_CFG);
        $this->conn = $this->db->getConnection();

        $this->testdb_helper = new ThinkUpTestDatabaseHelper();
        $this->testdb_helper->drop($this->db);
        $this->testdb_helper->create($this->db);
    }

    /**
     * Drop the database and kill the connection
     */
    public function tearDown() {
        $this->testdb_helper->drop($this->db);
        $this->db->closeConnection($this->conn);
        parent::tearDown();
    }
}
