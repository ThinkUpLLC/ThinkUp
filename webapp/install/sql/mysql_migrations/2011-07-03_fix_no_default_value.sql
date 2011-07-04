-- tu_instances
-- Avoid PDOException: SQLSTATE[HY000]: General error: 1364 Field last_post_id doesnt have a default value

ALTER TABLE  tu_instances CHANGE  last_post_id  last_post_id BIGINT( 20 ) UNSIGNED NOT NULL DEFAULT 0 COMMENT  'Last network post ID fetched for this instance.';

-- tu_users
-- Avoid PDOException: SQLSTATE[HY000]: General error: 1364 Field last_post_id doesnt have a default value
-- Avoid PDOException: SQLSTATE[HY000]: General error: 1292 Incorrect datetime value: 1970-01-01 00:00:00 for column last_post at row 1
-- Add comments while we are altering the table anyway

CREATE TABLE tu_users_b14 (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  user_id bigint(11) NOT NULL COMMENT 'User ID on a given network.',
  user_name varchar(255) NOT NULL COMMENT 'Username on a given network, like a user''s Twitter username or Facebook user name.',
  full_name varchar(255) NOT NULL COMMENT 'Full name on a given network.',
  avatar varchar(255) NOT NULL COMMENT 'URL to user''s avatar on a given network.',
  location varchar(255) DEFAULT NULL COMMENT 'Service user location.',
  description text COMMENT 'Service user description, like a Twitter user''s profile description.',
  url varchar(255) DEFAULT NULL COMMENT 'Service user''s URL.',
  is_protected tinyint(1) NOT NULL COMMENT 'Whether or not the user is public.',
  follower_count int(11) NOT NULL COMMENT 'Total number of followers a service user has.',
  friend_count int(11) NOT NULL DEFAULT 0 COMMENT 'Total number of friends a service user has.',
  post_count int(11) NOT NULL DEFAULT 0 COMMENT 'Total number of posts the user has authored.',
  last_updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Last time this user''s record was updated.',
  found_in varchar(100) DEFAULT NULL COMMENT 'What data source or API call the last update originated from (for developer debugging).',
  last_post timestamp DEFAULT 0 COMMENT 'The time of the latest post the user authored.',
  joined timestamp DEFAULT 0 COMMENT 'When the user joined the network.',
  last_post_id bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT 'Network post ID of the latest post the user authored.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  favorites_count int(11) DEFAULT NULL COMMENT 'Total number of posts the user has favorited.',
  PRIMARY KEY (id),
  UNIQUE KEY user_id (user_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Service user details.';

INSERT INTO tu_users_b14 (SELECT * FROM tu_users);

RENAME TABLE tu_users TO tu_users_b13;

RENAME TABLE tu_users_b14 TO tu_users;

DROP TABLE tu_users_b13;
