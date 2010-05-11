<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'webapp/model/class.MySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Database.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.LoggerSlowSQL.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';


class TestOfMySQLDAO extends UnitTestCase {
	var $logger;
	var $db;
    function TestOfMySQLDAO() {
        $this->UnitTestCase('MySQLDAO class test');
    }
    
    function setUp() {
		global $THINKTANK_CFG;
        $this->logger = new Logger($THINKTANK_CFG['log_location']);
		$this->db = new Database($THINKTANK_CFG);
    }
    
    function tearDown() {
		$this->logger->close();
    	
    }
    
    function testCreatingNewMySQLDAO() {
		$dao = new MySQLDAO($this->logger, $this->db);
		$this->assertTrue(isset($dao->logger), "Logger set");
		$this->assertTrue(isset($dao->db), "DB set");

    }
}
?>