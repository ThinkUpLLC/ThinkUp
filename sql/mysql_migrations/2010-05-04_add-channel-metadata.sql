ALTER TABLE `tt_channels` CHANGE `keyword` `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `tt_channels` ADD `url` VARCHAR( 255 ) NOT NULL AFTER `name` ,
ADD `network_id`  BIGINT( 11 ) NULL AFTER `name` 