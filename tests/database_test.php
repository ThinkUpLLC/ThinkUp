<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("class.Database.php");
require_once ("config.inc.php");


class TestOfDatabase extends UnitTestCase {
    function TestOfLogging() {
        $this->UnitTestCase('Database class test');
    }
    
    function setUp() {
    
    }
    
    function tearDown() {
    
    }
    
    function testCreatingNewDatabase() {
        global $TWITALYTIC_CFG;
        $db = new Database($TWITALYTIC_CFG);
		$this->assertTrue($db->db_host==$TWITALYTIC_CFG['db_host'], "Database vars set");
    }
	
	function testCreatingNewDatabaseConnection() {
        global $TWITALYTIC_CFG;
        $db = new Database($TWITALYTIC_CFG);
		$conn = $db->getConnection();
        $this->assertTrue(isset($conn), 'Connection created');
		$db->closeConnection($conn);
	}
	
	function testCreatingBadDatabaseConnection() {
        global $TWITALYTIC_CFG;
		$TWITALYTIC_CFG['db_password'] = 'wrong password';
        $db = new Database($TWITALYTIC_CFG);
		$this->expectException( new Exception("ERROR: Access denied for user 'twitalytic'@'localhost' (using password: YES)localhosttwitalyticwrong password") ); 
		$conn = $db->getConnection();
        $this->assertTrue($conn==null, 'Connection not set');
	}
}

?>