<?php
require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("class.Database.php");
require_once ("class.LoggerSlowSQL.php");
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
		global $THINKTANK_CFG;
		$db = new Database($THINKTANK_CFG);
		$this->assertTrue($db->db_host==$THINKTANK_CFG['db_host'], "Database vars set");
	}

	function testCreatingNewDatabaseConnection() {
		global $THINKTANK_CFG;
		$db = new Database($THINKTANK_CFG);
		$conn = $db->getConnection();
		$this->assertTrue(isset($conn), 'Connection created');
		$db->closeConnection($conn);
	}

	function testExecutingSQLWithTablePrefixAndGMTOffset() {
		global $THINKTANK_CFG;
		$db = new Database($THINKTANK_CFG);
		$conn = $db->getConnection();
		$sql_result = $db->exec("SELECT
				t.*, u.*, pub_date - interval %gmt_offset% hour as adj_pub_date 
			FROM 
				%prefix%tweets t
			INNER JOIN
				%prefix%users u
			ON
				t.author_user_id = u.user_id
			WHERE 
				 in_reply_to_user_id =  930061
			ORDER BY 
				pub_date DESC 
			LIMIT 15;");

		$db->closeConnection($conn);
	}

	function testCreatingBadDatabaseConnection() {
		global $THINKTANK_CFG;
		$THINKTANK_TEST_CFG['db_password'] = 'wrong password';
		$THINKTANK_TEST_CFG['table_prefix'] = '';
		$THINKTANK_TEST_CFG['db_host'] = $THINKTANK_CFG['db_host'];
		$THINKTANK_TEST_CFG['db_name'] = $THINKTANK_CFG['db_name'];
		$THINKTANK_TEST_CFG['db_user'] = $THINKTANK_CFG['db_user'];


		$db = new Database($THINKTANK_TEST_CFG);
		$this->expectException( new Exception("ERROR: Access denied for user '".$THINKTANK_TEST_CFG['db_user']."'@'localhost' (using password: YES)localhost".$THINKTANK_TEST_CFG['db_user']."wrong password") );
		$conn = $db->getConnection();
		$this->assertTrue($conn==null, 'Connection not set');
		$db->closeConnection($conn);

	}

	function testExecutingSQLWithUnSetTablePrefixShouldFail() {
		global $THINKTANK_CFG;

		$THINKTANK_TEST_CFG['table_prefix'] = 'tw_';
		$THINKTANK_TEST_CFG['db_password'] = $THINKTANK_CFG['db_password'];
		$THINKTANK_TEST_CFG['db_host'] = $THINKTANK_CFG['db_host'];
		$THINKTANK_TEST_CFG['db_name'] = $THINKTANK_CFG['db_name'];
		$THINKTANK_TEST_CFG['db_user'] = $THINKTANK_CFG['db_user'];

		$this->expectException();
		$db = new Database($THINKTANK_TEST_CFG);
		$conn = $db->getConnection();
		$sql_result = $db->exec("SELECT
				user_id 
			FROM 
				%prefix%users 
			WHERE 
				user_id = 930061");

		$db->closeConnection($conn);

	}

}

?>