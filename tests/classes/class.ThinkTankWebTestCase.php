<?php
require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankTestDatabaseHelper.php';
require_once 'model/class.MySQLDAO.php';
require_once 'model/class.Database.php';
require_once 'model/class.Logger.php';
require_once 'model/class.LoggerSlowSQL.php';
require_once 'config.inc.php';

class ThinkTankWebTestCase extends WebTestCase {
    var $logger;
    var $db;
    var $conn;
    var $testdb_helper;


    function setUp() {
        global $THINKTANK_CFG;
        global $TEST_DATABASE;

        //Override default CFG values
        $THINKTANK_CFG['db_name'] = $TEST_DATABASE;

        $this->logger = new Logger($THINKTANK_CFG['log_location']);
        $this->db = new Database($THINKTANK_CFG);
        $this->conn = $this->db->getConnection();

        $this->testdb_helper = new ThinkTankTestDatabaseHelper();
        $this->testdb_helper->create($this->db);
    }

    function tearDown() {
        $this->testdb_helper->drop($this->db);
        $this->db->closeConnection($this->conn);
        $this->logger->close();
    }
}
?>
