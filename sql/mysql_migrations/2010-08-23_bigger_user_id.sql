-- Change the user_id field into a bigint(11)
ALTER TABLE tu_users CHANGE user_id user_id BIGINT(11) NOT NULL;

-- Fix the incorrect user_id values in the users table
UPDATE tu_posts p, tu_users u SET u.user_id = p.author_user_id WHERE p.author_fullname = u.full_name AND p.network = u.network AND u.user_id = 2147483647;

-- Remove duplicates in users table
-- Use this new table
CREATE TABLE tu_users2 (
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
  last_post_id bigint(11) NOT NULL DEFAULT '0',
  network varchar(10) NOT NULL DEFAULT 'twitter',
  PRIMARY KEY (id),
  KEY user_id (user_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO tu_users2 (SELECT * FROM tu_users WHERE 1 GROUP BY user_id);
DROP TABLE tu_users;
RENAME TABLE tu_users2 TO tu_users;
