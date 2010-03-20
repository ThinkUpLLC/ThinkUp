ALTER TABLE `links` ADD `is_image` TINYINT NOT NULL DEFAULT '0';

ALTER TABLE `links` ADD INDEX ( `is_image` ) ;