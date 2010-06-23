ALTER TABLE tt_posts ADD is_reply_by_friend TINYINT NOT NULL DEFAULT '0' AFTER reply_count_cache;
ALTER TABLE tt_posts ADD is_retweet_by_friend TINYINT NOT NULL DEFAULT '0' AFTER retweet_count_cache; 