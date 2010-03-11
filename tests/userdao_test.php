<?php

require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("class.MySQLDAO.php");
require_once ("class.User.php");
require_once ("class.Database.php");
require_once ("class.Logger.php");
require_once ("class.LoggerSlowSQL.php");
require_once ("config.inc.php");


class TestOfUserDAO extends UnitTestCase {
	var $logger;
	var $db;
	var $conn;

	function TestOfUserDAO() {
		$this->UnitTestCase('UserDAO class test');
	}

	function setUp() {
		global $THINKTANK_CFG;

		//Override default CFG values
		$THINKTANK_CFG['db_name'] ="thinktank_tests";

		//Build test table
		$q = "CREATE TABLE IF NOT EXISTS `tt_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `full_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `avatar` varchar(255) COLLATE utf8_bin NOT NULL,
  `location` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `description` text COLLATE utf8_bin,
  `url` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `is_protected` tinyint(1) NOT NULL,
  `follower_count` int(11) NOT NULL,
  `friend_count` int(11) NOT NULL DEFAULT '0',
  `post_count` int(11) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `found_in` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `last_post` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `joined` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_post_id` bigint(20) NOT NULL DEFAULT '0',
  `network` varchar(10) COLLATE utf8_bin NOT NULL DEFAULT 'twitter',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `last_updated_user_id` (`last_updated`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
		";

		$this->logger = new Logger($THINKTANK_CFG['log_location']);
		$this->db = new Database($THINKTANK_CFG);
		$this->conn = $this->db->getConnection();
		$this->db->exec($q);

		//Insert test data into test table
		$q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar) VALUES (12, 'jack', 'Jack Dorsey', 'avatar.jpg');";
		$this->db->exec($q);

	}

	function tearDown() {
		$this->logger->close();

		//Delete test data
		$q = "DROP TABLE tt_users;";
		$this->db->exec($q);

		//Clean up
		$this->db->closeConnection($this->conn);
	}

	function testCreateNewUserDAO() {
		$dao = new UserDAO($this->db, $this->logger);
		$this->assertTrue(isset($dao->logger), "Logger set");
		$this->assertTrue(isset($dao->db), "DB set");

	}

	function testIsUserInDB() {
		$udao = new UserDAO($this->db, $this->logger);
		$this->assertTrue($udao->isUserInDB(12));
		$this->assertTrue(!$udao->isUserInDB(13));

	}

	function testIsUserInDBByName() {
		$udao = new UserDAO($this->db, $this->logger);
		$this->assertTrue($udao->isUserInDBByName('jack'));
		$this->assertTrue(!$udao->isUserInDBByName('gina'));

	}

	function testUpdateUser() {
		$udao = new UserDAO($this->db, $this->logger);

		$uarr = array('id'=>2, 'user_id'=>13, 'user_name'=>'ginatrapani', 'full_name'=>'Gina Trapani', 'avatar'=>'avatar.jpg', 'location'=>'NYC', 'description'=>'Blogger', 'url'=>'http://ginatrapani.org', 'is_protected'=>0, 'follower_count'=>5000, 'post_count'=>1000, 'joined'=>'3/6/2007');
		$u = new User($uarr, 'Test');
		$this->assertTrue($udao->updateUser($u));
	}

	function testUpdateUsers() {
		$udao = new UserDAO($this->db, $this->logger);

		$user_array1 = array('id'=>2, 'user_id'=>13, 'user_name'=>'ginatrapani', 'full_name'=>'Gina Trapani', 'avatar'=>'avatar.jpg', 'location'=>'NYC', 'description'=>'Blogger', 'url'=>'http://ginatrapani.org', 'is_protected'=>0, 'follower_count'=>5000, 'post_count'=>1000, 'joined'=>'3/6/2007');
		$user1 = new User($user_array1, 'Test');
		$user_array2 = array('id'=>3, 'user_id'=>14, 'user_name'=>'anildash', 'full_name'=>'Anil Dash', 'avatar'=>'avatar.jpg', 'location'=>'NYC', 'description'=>'Blogger', 'url'=>'http://ginatrapani.org', 'is_protected'=>0, 'follower_count'=>5000, 'post_count'=>1000, 'joined'=>'3/6/2007');
		$user2 = new User($user_array2, 'Test');

		$users_to_update = array($user1, $user2);

		$this->assertTrue($udao->updateUsers($users_to_update) == 2 );
	}

	function testGetDetailsUserExists() {
		$udao = new UserDAO($this->db, $this->logger);

		$user = $udao->getDetails(12);
		$this->assertTrue($user->id == 1);
		$this->assertTrue($user->user_id == 12);
		$this->assertTrue($user->user_name == 'jack');
	}

	function testGetDetailsUserDoesNotExist() {
		$udao = new UserDAO($this->db, $this->logger);

		$user = $udao->getDetails(13);

		$this->assertTrue(!isset($user));
	}
	
	function testGetUserByNameUserExists() {
		$udao = new UserDAO($this->db, $this->logger);

		$user = $udao->getUserByName('jack');
		$this->assertTrue($user->id == 1);
		$this->assertTrue($user->user_id == 12);
		$this->assertTrue($user->user_name == 'jack');
	}

	function testGetUserByNameUserDoesNotExist() {
		$udao = new UserDAO($this->db, $this->logger);

		$user = $udao->getUserByName('gina');
		$this->assertTrue(!isset($user));
	}
	
}
?>