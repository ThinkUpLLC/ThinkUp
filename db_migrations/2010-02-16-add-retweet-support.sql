ALTER TABLE `tt_tweets` ADD `in_retweet_of_status_id` bigint(11) default NULL;

ALTER TABLE `tt_tweets` ADD `retweet_count_cache` int(11) NOT NULL default '0';


