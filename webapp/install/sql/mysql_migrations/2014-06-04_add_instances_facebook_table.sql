CREATE TABLE `tu_instances_facebook` (
      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
      `profile_updated` DATETIME COMMENT 'Last time the facebook profile was updated.',
      PRIMARY KEY (`id`)
) ENGINE=MyISAM CHARSET=utf8 COMMENT='Facebook-specific instance metadata.';
