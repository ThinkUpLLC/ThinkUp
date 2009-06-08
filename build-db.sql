# Sequel Pro dump
# Version 663
# http://code.google.com/p/sequel-pro
#
# Host: localhost (MySQL 5.1.34)
# Database: twitalytic
# Generation Time: 2009-06-07 21:56:28 -0700
# ************************************************************

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table follows
# ------------------------------------------------------------

DROP TABLE IF EXISTS `follows`;

CREATE TABLE `follows` (
  `user_id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `last_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`follower_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table instances
# ------------------------------------------------------------

DROP TABLE IF EXISTS `instances`;

CREATE TABLE `instances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `twitter_user_id` int(11) NOT NULL,
  `twitter_username` varchar(255) COLLATE utf8_bin NOT NULL,
  `twitter_password` varchar(255) COLLATE utf8_bin NOT NULL,
  `last_status_id` int(11) DEFAULT NULL,
  `crawler_last_run` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_page_fetched_followers` int(11) NOT NULL,
  `last_page_fetched_replies` int(11) NOT NULL DEFAULT '1',
  `last_page_fetched_tweets` int(11) NOT NULL DEFAULT '1',
  `total_tweets_by_owner` int(11) DEFAULT '0',
  `total_tweets_in_system` int(11) DEFAULT '0',
  `total_replies_in_system` int(11) DEFAULT NULL,
  `total_users_in_system` int(11) DEFAULT NULL,
  `total_follows_in_system` int(11) DEFAULT NULL,
  `earliest_tweet_in_system` datetime DEFAULT NULL,
  `earliest_reply_in_system` datetime DEFAULT NULL,
  `is_archive_loaded_replies` int(11) NOT NULL DEFAULT '0',
  `is_archive_loaded_follows` int(11) NOT NULL DEFAULT '0',
  `api_calls_to_leave_unmade` int(11) NOT NULL DEFAULT '50',
  PRIMARY KEY (`id`),
  KEY `twitter_user_id` (`twitter_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Dump of table tweets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tweets`;

CREATE TABLE `tweets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status_id` int(11) NOT NULL,
  `author_user_id` int(11) NOT NULL,
  `author_username` varchar(50) COLLATE utf8_bin NOT NULL,
  `author_fullname` varchar(50) COLLATE utf8_bin NOT NULL,
  `author_avatar` varchar(255) COLLATE utf8_bin NOT NULL,
  `tweet_text` varchar(160) COLLATE utf8_bin NOT NULL,
  `tweet_html` varchar(255) COLLATE utf8_bin NOT NULL,
  `pub_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `in_reply_to_user_id` int(11) DEFAULT NULL,
  `in_reply_to_status_id` int(11) DEFAULT NULL,
  `reply_count_cache` int(11) NOT NULL DEFAULT '0',
  `is_follow_friday` int(11) NOT NULL DEFAULT '0',
  `is_retweet` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `status_id` (`status_id`),
  KEY `author_username` (`author_username`),
  KEY `pub_date` (`pub_date`),
  KEY `author_user_id` (`author_user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=29538 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text,
  `url` varchar(255) DEFAULT NULL,
  `is_protected` tinyint(1) NOT NULL,
  `follower_count` int(11) NOT NULL,
  `friend_count` int(11) NOT NULL DEFAULT '0',
  `tweet_count` int(11) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `found_in` varchar(100) DEFAULT NULL,
  `last_post` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `joined` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=80318 DEFAULT CHARSET=latin1;






/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
