ALTER TABLE `tt_instances` CHANGE `network_user_id` `network_user_id` BIGINT( 11 ) NOT NULL ; 
ALTER TABLE  `tt_instances` CHANGE  `network`  `network` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'twitter' ;
ALTER TABLE `tt_instances` ADD `network_viewer_id` BIGINT NOT NULL DEFAULT '1' AFTER `network_user_id` ;
UPDATE tt_instances SET network_viewer_id = network_user_id;


ALTER TABLE `tt_posts` CHANGE `author_user_id` `author_user_id` BIGINT( 11 ) NOT NULL  ;

 
