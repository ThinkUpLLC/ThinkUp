ALTER TABLE  `tt_users` CHANGE  `tweet_count` `post_count` int(11) NOT NULL;
ALTER TABLE  `tt_users` CHANGE  `last_status_id` `last_post_id` bigint(20) NOT NULL default '0';

ALTER TABLE  `tt_instances` CHANGE  `twitter_user_id` `network_user_id` int(11) NOT NULL;
ALTER TABLE  `tt_instances` CHANGE  `twitter_username` `network_username` varchar(255) collate utf8_bin NOT NULL;
ALTER TABLE  `tt_instances` CHANGE  `total_tweets_in_system` `total_posts_in_system` int(11) default '0';
ALTER TABLE  `tt_instances` CHANGE  `total_tweets_by_owner` `total_posts_by_owner` int(11) default '0';
ALTER TABLE  `tt_instances` CHANGE  `earliest_tweet_in_system` `earliest_post_in_system` datetime default NULL;
ALTER TABLE  `tt_instances` ADD  `network` VARCHAR( 10 ) NOT NULL DEFAULT  'twitter';




