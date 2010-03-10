<?php

require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');
require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("class.MySQLDAO.php");
require_once ("class.User.php");
require_once ("class.Database.php");
require_once ("class.Logger.php");
require_once ("class.LoggerSlowSQL.php");
require_once ("class.Follow.php");
require_once ("config.inc.php");


class TestOfThinkTankFrontEnd extends WebTestCase {
	var $logger;
	var $db;
	var $conn;

	function setUp() {
		global $THINKTANK_CFG;

		//Override default CFG values
		$THINKTANK_CFG['db_name'] ="thinktank_tests";

		$this->logger = new Logger($THINKTANK_CFG['log_location']);
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

		$q = "CREATE TABLE IF NOT EXISTS `tt_instances` (
  `id` int(11) NOT NULL auto_increment,
  `twitter_user_id` int(11) NOT NULL,
  `twitter_username` varchar(255) collate utf8_bin NOT NULL,
  `last_status_id` bigint(11) default '0',
  `crawler_last_run` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_page_fetched_replies` int(11) NOT NULL default '1',
  `last_page_fetched_tweets` int(11) NOT NULL default '1',
  `total_tweets_by_owner` int(11) default '0',
  `total_tweets_in_system` int(11) default '0',
  `total_replies_in_system` int(11) default NULL,
  `total_users_in_system` int(11) default NULL,
  `total_follows_in_system` int(11) default NULL,
  `earliest_tweet_in_system` datetime default NULL,
  `earliest_reply_in_system` datetime default NULL,
  `is_archive_loaded_replies` int(11) NOT NULL default '0',
  `is_archive_loaded_follows` int(11) NOT NULL default '0',
  `api_calls_to_leave_unmade_per_minute` decimal(11,1) NOT NULL default '2.0',
  `is_public` int(1) NOT NULL default '0',
  `is_active` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `twitter_user_id` (`twitter_user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

";
		$this->db->exec($q);


		$q = "CREATE TABLE IF NOT EXISTS `tt_owners` (
  `id` int(20) NOT NULL auto_increment,
  `full_name` varchar(200) character set latin1 collate latin1_general_ci NOT NULL default '',
  `user_name` varchar(200) character set latin1 collate latin1_general_ci NOT NULL default '',
  `user_pwd` varchar(200) character set latin1 collate latin1_general_ci NOT NULL default '',
  `user_email` varchar(200) character set latin1 collate latin1_general_ci NOT NULL default '',
  `activation_code` int(10) NOT NULL default '0',
  `joined` date NOT NULL default '0000-00-00',
  `country` varchar(100) character set latin1 collate latin1_general_ci NOT NULL default '',
  `user_activated` int(1) NOT NULL default '0',
  `is_admin` int(1) NOT NULL default '0',
  `last_login` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
		$this->db->exec($q);


		$q = "CREATE TABLE IF NOT EXISTS `tt_owner_instances` (
  `id` int(20) NOT NULL auto_increment,
  `owner_id` int(10) NOT NULL,
  `instance_id` int(10) NOT NULL,
  `oauth_access_token` varchar(255) NOT NULL default '',
  `oauth_access_token_secret` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		";

		$this->db->exec($q);

		$q = "CREATE TABLE IF NOT EXISTS `tt_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` bigint(11) NOT NULL,
  `author_user_id` int(11) NOT NULL,
  `author_username` varchar(50) COLLATE utf8_bin NOT NULL,
  `author_fullname` varchar(50) COLLATE utf8_bin NOT NULL,
  `author_avatar` varchar(255) COLLATE utf8_bin NOT NULL,
  `post_text` varchar(160) COLLATE utf8_bin NOT NULL,
  `post_html` varchar(255) COLLATE utf8_bin NOT NULL,
  `source` varchar(255) COLLATE utf8_bin NOT NULL,
  `pub_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `in_reply_to_user_id` int(11) DEFAULT NULL,
  `in_reply_to_post_id` bigint(11) DEFAULT NULL,
  `mention_count_cache` int(11) NOT NULL DEFAULT '0',
  `in_retweet_of_post_id` bigint(11) DEFAULT NULL,
  `retweet_count_cache` int(11) NOT NULL DEFAULT '0',
  `network` varchar(10) COLLATE utf8_bin NOT NULL DEFAULT 'twitter',
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_id` (`post_id`),
  KEY `author_username` (`author_username`),
  KEY `pub_date` (`pub_date`),
  KEY `author_user_id` (`author_user_id`),
  KEY `in_reply_to_user_id` (`in_reply_to_user_id`),
  KEY `in_retweet_of_status_id` (`in_retweet_of_post_id`),
  FULLTEXT KEY `tweets_fulltext` (`post_text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
		";

		$this->db->exec($q);

		$q = "CREATE TABLE IF NOT EXISTS `tt_links` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(255) collate utf8_bin NOT NULL,
  `expanded_url` varchar(255) collate utf8_bin NOT NULL,
  `title` varchar(255) collate utf8_bin NOT NULL,
  `clicks` int(11) NOT NULL default '0',
  `post_id` bigint(11) NOT NULL,
  `is_image` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `post_id` (`post_id`),
  KEY `is_image` (`is_image`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
		";

		$this->db->exec($q);
		
		
		//Add owner
		$q = "INSERT INTO tt_owners (id, user_email, user_pwd, user_activated) VALUES (1, 'me@example.com', '70b0796c3c45a335b7a8459678e393d6bf3b208e', 1)";
		$this->db->exec($q);


		//Add instance
		$q = "INSERT INTO tt_instances (id, twitter_user_id, twitter_username, is_public) VALUES (1, 1234, 'thinktankapp', 1)";
		$this->db->exec($q);

		//Add instance_owner
		$q = "INSERT INTO tt_owner_instances (owner_id, instance_id) VALUES (1, 1)";
		$this->db->exec($q);


		//Insert test data into test table
		$q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar) VALUES (12, 'jack', 'Jack Dorsey', 'avatar.jpg');";
		$this->db->exec($q);

		$q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev', 'Ev Williams', 'avatar.jpg', '1/1/2005');";
		$this->db->exec($q);

		$q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected) VALUES (16, 'private', 'Private Poster', 'avatar.jpg', 1);";
		$this->db->exec($q);

		$q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected, follower_count) VALUES (17, 'thinktankapp', 'ThinkTankers', 'avatar.jpg', 0, 10);";
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

		// Plugin data
        $q = "CREATE TABLE  IF NOT EXISTS `tt_plugins` (
`id` INT NOT NULL AUTO_INCREMENT,
`name` VARCHAR( 255 ) NOT NULL ,
`folder_name` VARCHAR( 255 ) NOT NULL ,
`description` VARCHAR( 255 ),
`author` VARCHAR( 255 ),
`homepage` VARCHAR( 255 ),
`version` VARCHAR( 255 ),
`is_active` TINYINT NOT NULL ,
PRIMARY KEY (  `id` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
		";
        $this->db->exec($q);
        
        $q = "INSERT INTO  `tt_plugins` ( `name` , `folder_name` , `description` , `author` , `homepage` , `version` , `is_active` ) 
VALUES ( 'Twitter',  'twitter',  'Twitter support',  'Gina Trapani',  'http://thinktankapp.com',  '0.01',  '1' );";
        $this->db->exec($q);
		
        $q = "INSERT INTO  `tt_plugins` (`name` , `folder_name` , `description` , `author` , `homepage` , `version` , `is_active` )
VALUES (  'My Test Plugin',  'testplugin',  'Proof of concept plugin',  'Gina Trapani',  'http://thinktankapp.com',  '0.01',  '0' );";
        $this->db->exec($q);
	}

	function tearDown() {
		$this->logger->close();

		//Delete test data
		$q = "DROP TABLE tt_users, tt_user_errors, tt_follows, tt_owners, tt_instances, tt_owner_instances, tt_plugins, tt_posts, tt_links";
		$this->db->exec($q);

		//Clean up
		$this->db->closeConnection($this->conn);
	}

	function testPublicTimeline() {
		global $TEST_SERVER_DOMAIN;
			
		$this->get($TEST_SERVER_DOMAIN.'/public.php');
		$this->assertTitle('ThinkTank Public Timeline');
		$this->assertText('Sign in');
		$this->click('Sign in');
		$this->assertTitle('ThinkTank Sign In');
	}


	function testSignInAndPrivateDashboard() {
		global $TEST_SERVER_DOMAIN;

		$this->get($TEST_SERVER_DOMAIN.'/session/login.php');
		$this->setField('email', 'me@example.com');
		$this->setField('pwd', 'secretpassword');

		$this->click("Login");
		$this->assertTitle('ThinkTank');
		$this->assertText('Logged in as: me@example.com');
				
	}

	function testUserPage() {
		global $TEST_SERVER_DOMAIN;

		$this->get($TEST_SERVER_DOMAIN.'/session/login.php');
		$this->setField('email', 'me@example.com');
		$this->setField('pwd', 'secretpassword');

		$this->click("Login");
		$this->assertTitle('ThinkTank');

		$this->get($TEST_SERVER_DOMAIN.'/user/index.php?i=thinktankapp&u=ev');
		$this->assertTitle('ThinkTank');
		$this->assertText('Logged in as: me@example.com');
		$this->assertText('ev');
		
		$this->get($TEST_SERVER_DOMAIN.'/user/index.php?i=thinktankapp&u=usernotinsystem');
		$this->assertText('This user is not in the system.');
		
	}

	//TODO Write account page and status page tests
}
?>
