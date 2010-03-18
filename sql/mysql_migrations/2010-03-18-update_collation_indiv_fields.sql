ALTER TABLE `tt_follows`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `tt_instances`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `tt_links`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `tt_owners`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `tt_owner_instances`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `tt_plugins`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `tt_plugin_options`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `tt_posts`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `tt_post_errors`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `tt_user_errors`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `tt_users`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `tt_replies`  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `tt_follows` CHANGE `network` `network` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'twitter';

ALTER TABLE `tt_instances` CHANGE `network_username` `network_username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `network` `network` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'twitter';

ALTER TABLE `tt_links` CHANGE `url` `url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `expanded_url` `expanded_url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `title` `title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `tt_owners` CHANGE `full_name` `full_name` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `user_name` `user_name` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `user_pwd` `user_pwd` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `user_email` `user_email` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `country` `country` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `tt_plugins` CHANGE `name` `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `folder_name` `folder_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `description` `description` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE `author` `author` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE `homepage` `homepage` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE `version` `version` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `tt_plugin_options` CHANGE `option_name` `option_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `option_value` `option_value` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `tt_posts` CHANGE `author_username` `author_username` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `author_fullname` `author_fullname` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `author_avatar` `author_avatar` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `post_text` `post_text` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `source` `source` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
CHANGE `network` `network` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'twitter';

ALTER TABLE `tt_replies` CHANGE `author_username` `author_username` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `author_fullname` `author_fullname` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `author_avatar` `author_avatar` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `post_text` `post_text` VARCHAR( 160 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `source` `source` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `network` `network` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'twitter';

ALTER TABLE `tt_users` CHANGE `user_name` `user_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `full_name` `full_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `avatar` `avatar` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `location` `location` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE `url` `url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
CHANGE `network` `network` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'twitter',
CHANGE `found_in` `found_in` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;