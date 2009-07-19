-- phpMyAdmin SQL Dump
-- version 2.11.9.4
-- http://www.phpmyadmin.net
--
-- Generation Time: Jul 18, 2009 at 05:13 PM
-- Server version: 5.0.67
-- PHP Version: 5.2.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `twitalytic`
--

-- --------------------------------------------------------

--
-- Table structure for table `instances`
--

CREATE TABLE IF NOT EXISTS `instances` (
  `id` int(11) NOT NULL auto_increment,
  `twitter_user_id` int(11) NOT NULL,
  `twitter_username` varchar(255) collate utf8_bin NOT NULL,
  `last_status_id` bigint(11) default '0',
  `crawler_last_run` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `last_page_fetched_followers` int(11) NOT NULL,
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
  `api_calls_to_leave_unmade_per_minute` decimal(11,1) NOT NULL default '2.5',
  PRIMARY KEY  (`id`),
  KEY `twitter_user_id` (`twitter_user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
