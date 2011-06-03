CREATE TABLE tu_users_b13 (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id bigint(11) NOT NULL,
  user_name varchar(255) NOT NULL,
  full_name varchar(255) NOT NULL,
  avatar varchar(255) NOT NULL,
  location varchar(255) DEFAULT NULL,
  description text,
  url varchar(255) DEFAULT NULL,
  is_protected tinyint(1) NOT NULL,
  follower_count int(11) NOT NULL,
  friend_count int(11) NOT NULL DEFAULT '0',
  post_count int(11) NOT NULL,
  last_updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  found_in varchar(100) DEFAULT NULL,
  last_post timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  joined timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  last_post_id bigint(20) unsigned NOT NULL,
  network varchar(20) NOT NULL DEFAULT 'twitter',
  favorites_count int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY user_id (user_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO tu_users_b13 SELECT * FROM tu_users GROUP BY CONCAT(user_id, network); 

RENAME TABLE tu_users TO tu_users_b12;
RENAME TABLE tu_users_b13 TO tu_users;

DROP TABLE tu_users_b12;