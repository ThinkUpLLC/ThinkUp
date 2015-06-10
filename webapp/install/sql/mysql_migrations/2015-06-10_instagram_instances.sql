
CREATE TABLE tu_instances_instagram (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'nternal unique ID.',
  followed_by_next_cursor varchar(255) DEFAULT NULL COMMENT 'Follower fetch cursor.',
  follows_next_cursor varchar(255) DEFAULT NULL COMMENT 'Friend fetch cursor.',
  next_max_like_id varchar(255) DEFAULT NULL COMMENT 'Likes fetch cursor.',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Instagram-specific instance metadata.';