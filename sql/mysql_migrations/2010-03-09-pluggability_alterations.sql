ALTER TABLE  `tt_tweets` ADD  `network` VARCHAR( 10 ) NOT NULL DEFAULT  'twitter';
ALTER TABLE  `tt_follows` ADD  `network` VARCHAR( 10 ) NOT NULL DEFAULT  'twitter';
ALTER TABLE  `tt_users` ADD  `network` VARCHAR( 10 ) NOT NULL DEFAULT  'twitter';
ALTER TABLE  `tt_user_errors` ADD  `network` VARCHAR( 10 ) NOT NULL DEFAULT  'twitter';

CREATE TABLE  `tt_plugins` (
`id` INT NOT NULL AUTO_INCREMENT,
`name` VARCHAR( 255 ) NOT NULL ,
`folder_name` VARCHAR( 255 ) NOT NULL ,
`description` VARCHAR( 255 ),
`author` VARCHAR( 255 ),
`homepage` VARCHAR( 255 ),
`version` VARCHAR( 255 ),
`is_active` TINYINT NOT NULL ,
PRIMARY KEY (  `id` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE  `tt_plugin_options` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`plugin_id` INT NOT NULL ,
`option_name` VARCHAR( 255 ) NOT NULL ,
`option_value` VARCHAR( 255 ) NOT NULL ,
INDEX (  `plugin_id` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO  `tt_plugins` (`name` , `folder_name` , `description` , `author` , `homepage` , `version` , `is_active` )
VALUES ('Twitter',  'twitter',  'Twitter support',  'Gina Trapani',  'http://thinktankapp.com',  '0.01',  '1');
