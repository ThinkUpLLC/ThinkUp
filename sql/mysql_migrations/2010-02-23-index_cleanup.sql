ALTER TABLE `tt_follows` DROP INDEX `follower_id_user_id`;
ALTER TABLE `tt_tweets` ADD KEY `in_reply_to_user_id` (`in_reply_to_user_id`);


