alter table tu_posts add in_rt_of_user_id bigint(11) DEFAULT NULL;
alter table tu_posts change retweet_count_cache old_retweet_count_cache int(11) NOT NULL DEFAULT '0';
alter table tu_posts add retweet_count_cache int(11) NOT NULL DEFAULT '0';
