CREATE TABLE tt_channels (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`keyword` VARCHAR( 255 ) NOT NULL ,
`network` VARCHAR( 10 ) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE tt_instance_channels (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`instance_id` INT NOT NULL ,
`channel_id` INT NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `tt_instance_channels` ADD INDEX ( `instance_id` ) 