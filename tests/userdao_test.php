<?php
 
require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("class.MySQLDAO.php");
require_once ("class.User.php");
require_once ("class.Database.php");
require_once ("class.Logger.php");
require_once ("config.inc.php");


class TestOfUserDAO extends UnitTestCase {
	var $logger;
	var $db;
	var $conn;
	
    function TestOfUserDAO() {
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
    
    function testCreatingNewUserDAO() {
		$dao = new UserDAO($this->logger, $this->db);
		$this->assertTrue(isset($dao->logger), "Logger set");
		$this->assertTrue(isset($dao->db), "DB set");

    }
	
	function testIsUserInDB() {
		$conn = $this->db->getConnection();
		$udao = new UserDAO($this->logger, $this->db);
		$this->assertTrue($udao->isUserInDB(930061));
		$this->assertTrue(!$udao->isUserInDB(93454654654650061));
		$this->db->closeConnection($conn);

    }
}
?>