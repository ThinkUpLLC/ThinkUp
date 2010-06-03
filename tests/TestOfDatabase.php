<?php
require_once dirname(__FILE__) . '/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Database.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.LoggerSlowSQL.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';


class TestOfDatabase extends ThinkTankBasicUnitTestCase {
    function TestOfDatabase() {
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
        global $TEST_DATABASE;

        //Override default CFG values
        $THINKTANK_CFG['db_name'] = $TEST_DATABASE;

        $this->db = new Database($THINKTANK_CFG);
        $this->conn = $this->db->getConnection();

        //Build test table
        $q = "CREATE TABLE IF NOT EXISTS `tt_users` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) collate utf8_bin NOT NULL,
  `full_name` varchar(255) collate utf8_bin NOT NULL,
  `avatar` varchar(255) collate utf8_bin NOT NULL,
  `location` varchar(255) collate utf8_bin default NULL,
  `description` text collate utf8_bin,
  `url` varchar(255) collate utf8_bin default NULL,
  `is_protected` tinyint(1) NOT NULL,
  `follower_count` int(11) NOT NULL,
  `friend_count` int(11) NOT NULL default '0',
  `tweet_count` int(11) NOT NULL,
  `last_updated` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `found_in` varchar(100) collate utf8_bin default NULL,
  `last_post` timestamp NOT NULL default '0000-00-00 00:00:00',
  `joined` timestamp NOT NULL default '0000-00-00 00:00:00',
  `last_status_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `last_updated_user_id` (`last_updated`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
        $this->db->exec($q);

        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar) VALUES (12, 'jack', 'Jack Dorsey', 'avatar.jpg');";
        $this->db->exec($q);

        //Delete test data
        $q = "DROP TABLE tt_users;";
        $this->db->exec($q);

        //Clean up
        $this->db->closeConnection($this->conn);

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
                #prefix#users 
            WHERE 
                user_id = 930061");

        $db->closeConnection($conn);

    }

}
?>