ALTER TABLE `tt_follows` DROP KEY `follower_id_user_id` (`follower_id`,`user_id`);
ALTER TABLE `tt_tweets` ADD KEY `in_reply_to_user_id` (`in_reply_to_user_id`);

