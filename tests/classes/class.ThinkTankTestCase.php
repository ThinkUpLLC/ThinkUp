<?php
require_once 'model/class.MySQLDAO.php';
require_once 'model/class.Database.php';
require_once 'model/class.Logger.php';
require_once 'model/class.LoggerSlowSQL.php';
require_once 'config.inc.php';

class ThinkTankUnitTestCase extends UnitTestCase {
    var $logger;
    var $db;
    var $conn;

    function setUp() {
        global $THINKTANK_CFG;
        global $TEST_DATABASE;
        error_reporting(22527); //Don't show E_DEPRECATED PHP messages, split() is deprecated

        //Override default CFG values
        $THINKTANK_CFG['db_name'] = $TEST_DATABASE;

        $this->logger = new Logger($THINKTANK_CFG['log_location']);
        $this->db = new Database($THINKTANK_CFG);
        $this->conn = $this->db->getConnection();

        //Create all the tables based on the build script
        $create_db_script = file_get_contents($THINKTANK_CFG['source_root_path']."sql/build-db_mysql.sql");
        $create_statements = split(";", $create_db_script);
        foreach ($create_statements as $q) {
            if (trim($q) != '') {
                $this->db->exec($q.";");
            }
        }
    }

    function tearDown() {
        global $TEST_DATABASE;

        $this->logger->close();

        //Delete test data by dropping all existing tables
        $q = "SHOW TABLES FROM ".$TEST_DATABASE;
        $result = $this->db->exec($q);
        while ($row = mysql_fetch_assoc($result)) {
            $q = "DROP TABLE ".$row['Tables_in_'.$TEST_DATABASE];
            $this->db->exec($q);
        }

        //Clean up
        $this->db->closeConnection($this->conn);
    }
}

class ThinkTankWebTestCase extends WebTestCase {
    var $logger;
    var $db;
    var $conn;

    function setUp() {
        global $THINKTANK_CFG;
        global $TEST_DATABASE;

        //Override default CFG values
        $THINKTANK_CFG['db_name'] = $TEST_DATABASE;

        $this->logger = new Logger($THINKTANK_CFG['log_location']);
        $this->db = new Database($THINKTANK_CFG);
        $this->conn = $this->db->getConnection();


        //Create all the tables based on the build script
        $create_db_script = file_get_contents($THINKTANK_CFG['source_root_path']."sql/build-db_mysql.sql");
        $create_statements = split(";", $create_db_script);
        foreach ($create_statements as $q) {
            if (trim($q) != '') {
                $this->db->exec($q.";");
            }
        }
    }

    function tearDown() {
        global $TEST_DATABASE;

        $this->logger->close();

        //Delete test data by dropping all existing tables
        $q = "SHOW TABLES FROM ".$TEST_DATABASE;
        $result = $this->db->exec($q);
        while ($row = mysql_fetch_assoc($result)) {
            $q = "DROP TABLE ".$row['Tables_in_'.$TEST_DATABASE];
            $this->db->exec($q);
        }

        //Clean up
        $this->db->closeConnection($this->conn);
    }
}
?>
