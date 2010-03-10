<?php

require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("class.MySQLDAO.php");
require_once ("class.User.php");
require_once ("class.Database.php");
require_once ("class.Logger.php");
require_once ("class.LoggerSlowSQL.php");
require_once ("class.Follow.php");
require_once ("config.inc.php");


class TestOfFollowDAO extends UnitTestCase {
	var $logger;
	var $db;
	var $conn;

	function TestOfFollowDAO() {
		$this->UnitTestCase('FollowDAO class test');
	}

	function setUp() {
		global $THINKTANK_CFG;

		//Override default CFG values
		$THINKTANK_CFG['db_name'] ="thinktank_tests";

		$this->logger = new Logger($THINKTANK_CFG['log_location']);
		$this->db = new Database($THINKTANK_CFG);
		$this->conn = $this->db->getConnection();

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
		$this->db->exec($q);


		$q = "CREATE TABLE IF NOT EXISTS `tt_user_errors` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(20) NOT NULL,
  `error_code` int(11) NOT NULL,
  `error_text` varchar(255) collate utf8_bin NOT NULL,
  `error_issued_to_user_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
		$this->db->exec($q);



		$q = "CREATE TABLE IF NOT EXISTS `tt_follows` (
  `user_id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `last_seen` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`user_id`,`follower_id`),
  KEY `active` (`active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

";
		$this->db->exec($q);


		//Insert test data into test table
		$q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar) VALUES (12, 'jack', 'Jack Dorsey', 'avatar.jpg');";
		$this->db->exec($q);

		$q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev', 'Ev Williams', 'avatar.jpg', '1/1/2005');";
		$this->db->exec($q);

		$q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected) VALUES (16, 'private', 'Private Poster', 'avatar.jpg', 1);";
		$this->db->exec($q);

		$q = "INSERT INTO tt_user_errors (user_id, error_code, error_text, error_issued_to_user_id) VALUES (15, 404, 'User not found', 13);";
		$this->db->exec($q);

		$q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (13, 12, '1/1/2006');";
		$this->db->exec($q);

		$q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (13, 14, '1/1/2006');";
		$this->db->exec($q);

		$q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (13, 15, '1/1/2006');";
		$this->db->exec($q);

		$q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (13, 16, '1/1/2006');";
		$this->db->exec($q);

		$q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (16, 12, '1/1/2006');";
		$this->db->exec($q);

	}

	function tearDown() {
		$this->logger->close();

		//Delete test data
		$q = "DROP TABLE tt_users, tt_user_errors, tt_follows;";
		$this->db->exec($q);

		//Clean up
		$this->db->closeConnection($this->conn);
	}

	function testCreateNewFollowDAO() {
		$dao = new UserDAO($this->db, $this->logger);
		$this->assertTrue(isset($dao->logger), "Logger set");
		$this->assertTrue(isset($dao->db), "DB set");

	}

	function testFollowExists() {
		$dao = new FollowDAO($this->db, $this->logger);
		$this->assertTrue($dao->followExists(13, 12));
		$this->assertTrue(!$dao->followExists(12,13));
	}

	function testUpdate() {
		$dao = new FollowDAO($this->db, $this->logger);
		$this->assertTrue(!$dao->update(12, 13));
		$this->assertTrue($dao->update(13, 12));
	}

	function testDeactivate() {
		$dao = new FollowDAO($this->db, $this->logger);
		$this->assertTrue(!$dao->deactivate(12, 13));
		$this->assertTrue($dao->deactivate(13, 12));
	}

	function testInsert() {
		$dao = new FollowDAO($this->db, $this->logger);
		$this->assertTrue($dao->insert(12, 13));
		$this->assertTrue($dao->followExists(12, 13));
	}

	function testGetUnloadedFollowerDetails() {
		$dao = new FollowDAO($this->db, $this->logger);
		$unloaded_followers = $dao->getUnloadedFollowerDetails(13);

		$this->assertTrue(count($unloaded_followers) == 1);
		$this->assertTrue($unloaded_followers[0]['follower_id']==14);

	}

	function testGetTotalFollowsWithErrors() {
		$dao = new FollowDAO($this->db, $this->logger);
		$total_follower_errors = $dao->getTotalFollowsWithErrors(13);

		$this->assertTrue($total_follower_errors == 1);
	}

	function testGetTotalFriendsWithErrors() {
		$dao = new FollowDAO($this->db, $this->logger);
		$total_friend_errors = $dao->getTotalFriendsWithErrors(13);

		$this->assertTrue($total_friend_errors == 0);
	}

	function testGetTotalFollowsWithFullDetails() {
		$dao = new FollowDAO($this->db, $this->logger);
		$total_follows_with_details = $dao->getTotalFollowsWithFullDetails(13);

		$this->assertTrue($total_follows_with_details == 2);
	}

	function testGetTotalFollowsProtected() {
		$dao = new FollowDAO($this->db, $this->logger);
		$total_follows_protected = $dao->getTotalFollowsProtected(13);

		$this->assertTrue($total_follows_protected == 1);
	}

	function testGetTotalFriends() {
		$dao = new FollowDAO($this->db, $this->logger);
		$total_friends = $dao->getTotalFriends(12);

		$this->assertTrue($total_friends == 2);
	}

	function testGetTotalFriendsProtected() {
		$dao = new FollowDAO($this->db, $this->logger);
		$total_friends_protected = $dao->getTotalFriendsProtected(12);

		$this->assertTrue($total_friends_protected == 1);
	}

	function testGetStalestFriend() {
		$dao = new FollowDAO($this->db, $this->logger);
		$stalest_friend = $dao->getStalestFriend(12);

		$this->assertTrue($stalest_friend != null);
		$this->assertTrue($stalest_friend->user_id == 13);
	}
	
	//TODO Complete FollowDAO tests for the rest of the methods

}
?>