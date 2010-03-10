RENAME TABLE `tt_tweets` TO `tt_posts` 
ALTER TABLE  `tt_posts` CHANGE  `status_id`  `post_id` BIGINT( 11 ) NOT NULL
ALTER TABLE  `tt_posts` CHANGE  `tweet_text`  `post_text` VARCHAR( 160 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
ALTER TABLE  `tt_posts` CHANGE  `tweet_html`  `post_html` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
ALTER TABLE  `tt_posts` CHANGE  `in_reply_to_status_id`  `in_reply_to_post_id` BIGINT( 11 ) NULL DEFAULT NULL
ALTER TABLE  `tt_posts` CHANGE  `in_retweet_of_status_id`  `in_retweet_of_post_id` BIGINT( 11 ) NULL DEFAULT NULL
ALTER TABLE  `tt_posts` DROP `post_html`;


ALTER TABLE  `tt_links` CHANGE  `status_id`  `post_id` BIGINT( 11 ) NULL DEFAULT NULL

RENAME TABLE `tt_tweet_errors` TO `tt_post_errors` 
ALTER TABLE  `tt_post_errors` CHANGE  `status_id`  `post_id` BIGINT( 20 ) NOT NULL

CREATE TABLE IF NOT EXISTS `tt_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reply_id` bigint(11) NOT NULL,
  `author_user_id` int(11) NOT NULL,
  `author_username` varchar(50) COLLATE utf8_bin NOT NULL,
  `author_fullname` varchar(50) COLLATE utf8_bin NOT NULL,
  `author_avatar` varchar(255) COLLATE utf8_bin NOT NULL,
  `post_text` varchar(160) COLLATE utf8_bin NOT NULL,
  `source` varchar(255) COLLATE utf8_bin NOT NULL,
  `pub_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `in_reply_to_user_id` int(11) DEFAULT NULL,
  `in_reply_to_post_id` bigint(11) DEFAULT NULL,
  `in_retweet_of_post_id` bigint(11) DEFAULT NULL,
  `network` varchar(10) COLLATE utf8_bin NOT NULL DEFAULT 'twitter',
  PRIMARY KEY (`id`),
  UNIQUE KEY `reply_id` (`reply_id`),
  KEY `author_username` (`author_username`),
  KEY `pub_date` (`pub_date`),
  KEY `author_user_id` (`author_user_id`),
  KEY `in_reply_to_user_id` (`in_reply_to_user_id`),
  KEY `in_retweet_of_post_id` (`in_retweet_of_post_id`),
  FULLTEXT KEY `post_fulltext` (`post_text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;