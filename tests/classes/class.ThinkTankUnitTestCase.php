<?php
require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankTestDatabaseHelper.php';
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
 * ThinkTank Unit Test Case
 *
 * Adds database support to the basic unit test case, for tests that need ThinkTank's database structure.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ThinkTankUnitTestCase extends ThinkTankBasicUnitTestCase {
    var $db;
    var $conn;
    var $testdb_helper;

    /**
     * Create a clean copy of the ThinkTank database structure
     */
    public function setUp() {
        parent::setUp();
        global $THINKTANK_CFG;
        global $TEST_DATABASE;

        //Override default CFG values
        $THINKTANK_CFG['db_name'] = $TEST_DATABASE;
        $config = Config::getInstance();
        $config->setValue('db_name', $TEST_DATABASE);

        $this->db = new Database($THINKTANK_CFG);
        $this->conn = $this->db->getConnection();

        $this->testdb_helper = new ThinkTankTestDatabaseHelper();
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
