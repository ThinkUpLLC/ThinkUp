ALTER TABLE tu_posts CHANGE post_id post_id  bigint(20) UNSIGNED NOT NULL;
ALTER TABLE tu_posts CHANGE in_retweet_of_post_id in_retweet_of_post_id  bigint(20) UNSIGNED NULL;
ALTER TABLE tu_posts CHANGE in_reply_to_post_id in_reply_to_post_id bigint(20) UNSIGNED NULL;
ALTER TABLE tu_posts CHANGE in_reply_to_user_id in_reply_to_user_id bigint(20) UNSIGNED NULL;
