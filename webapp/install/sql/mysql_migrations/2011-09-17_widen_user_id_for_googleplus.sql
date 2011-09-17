ALTER TABLE  tu_instances CHANGE  network_user_id  network_user_id VARCHAR( 30 ) NOT NULL
COMMENT  'User ID on a given network, like a user''s Twitter ID or Facebook user ID.';

ALTER TABLE  tu_instances CHANGE  network_viewer_id  network_viewer_id VARCHAR( 30 ) NOT NULL
COMMENT  'Network user ID of the viewing user (which can affect permissions).';

ALTER TABLE  tu_favorites CHANGE  author_user_id  author_user_id VARCHAR( 30 ) NOT NULL
COMMENT  'User ID of favorited post author on a given network.';

ALTER TABLE  tu_favorites CHANGE  fav_of_user_id  fav_of_user_id VARCHAR( 30 ) NOT NULL
COMMENT  'User ID who favorited post on a given network.';


DROP TABLE IF EXISTS tu_users_b16;
CREATE TABLE tu_users_b16 LIKE tu_users;
ALTER TABLE  tu_users_b16 CHANGE  user_id  user_id VARCHAR( 30 ) NOT NULL  COMMENT 'User ID on a given network.';

INSERT INTO tu_users_b16 (SELECT * FROM tu_users);

RENAME TABLE tu_users TO tu_users_b15;
RENAME TABLE tu_users_b16 TO tu_users;
DROP TABLE tu_users_b15;


DROP TABLE IF EXISTS tu_posts_b16;
CREATE TABLE tu_posts_b16 LIKE tu_posts;

ALTER TABLE  tu_posts_b16 CHANGE  author_user_id  author_user_id VARCHAR( 30 ) NOT NULL
COMMENT  'The user ID inside the respective service, e.g. Twitter or Facebook user IDs.';

ALTER TABLE  tu_posts_b16 CHANGE  in_reply_to_user_id  in_reply_to_user_id VARCHAR( 30 ) NULL DEFAULT NULL
COMMENT  'The ID of the user that this post is in reply to.';

ALTER TABLE  tu_posts_b16 CHANGE  in_rt_of_user_id  in_rt_of_user_id VARCHAR( 30 ) NULL DEFAULT NULL
COMMENT  'The ID of the user that this post is retweeting. [Twitter-specific]';

INSERT INTO tu_posts_b16 (SELECT * FROM tu_posts);

RENAME TABLE tu_posts TO tu_posts_b15;
RENAME TABLE tu_posts_b16 TO tu_posts;
DROP TABLE tu_posts_b15;


DROP TABLE IF EXISTS tu_follows_b16;
CREATE TABLE tu_follows_b16 LIKE tu_follows;

ALTER TABLE  tu_follows_b16 CHANGE  follower_id  follower_id VARCHAR( 30 ) NOT NULL
COMMENT  'User ID on a particular service who has followed user_id.';

ALTER TABLE  tu_follows_b16 CHANGE  user_id  user_id VARCHAR( 30 ) NOT NULL
COMMENT  'User ID on a particular service who has been followed.';

INSERT INTO tu_follows_b16 (SELECT * FROM tu_follows);

RENAME TABLE tu_follows TO tu_follows_b15;
RENAME TABLE tu_follows_b16 TO tu_follows;
DROP TABLE tu_follows_b15;

DROP TABLE IF EXISTS tu_follower_count_b16;
CREATE TABLE tu_follower_count_b16 (
  network_user_id varchar(30) NOT NULL COMMENT 'User ID on a particular service with a follower count.',
  network varchar(20) NOT NULL COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  date date NOT NULL COMMENT 'Date of follower count.',
  count int(11) NOT NULL COMMENT 'Total number of followers.'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Follower counts by date and time.';

INSERT INTO tu_follower_count_b16 (SELECT * FROM tu_follower_count);

RENAME TABLE tu_follower_count TO tu_follower_count_b15;
RENAME TABLE tu_follower_count_b16 TO tu_follower_count;
DROP TABLE tu_follower_count_b15;

DROP TABLE IF EXISTS tu_user_errors_b16;
CREATE TABLE tu_user_errors_b16 (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  user_id varchar(30) NOT NULL COMMENT 'User ID on a particular service.',
  error_code int(11) NOT NULL COMMENT 'Error code issues from the service.',
  error_text varchar(255) NOT NULL COMMENT 'Error text as supplied from the service.',
  error_issued_to_user_id bigint(11) NOT NULL COMMENT 'User ID service issued error to.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  PRIMARY KEY (id),
  KEY user_id (user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Errors in response to requests for user information.';

INSERT INTO tu_user_errors_b16 (SELECT * FROM tu_user_errors);

RENAME TABLE tu_user_errors TO tu_user_errors_b15;
RENAME TABLE tu_user_errors_b16 TO tu_user_errors;
DROP TABLE tu_user_errors_b15;


DROP TABLE IF EXISTS tu_post_errors_b16;
CREATE TABLE tu_post_errors_b16 (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  post_id bigint(20) unsigned NOT NULL COMMENT 'Post ID on the originating service.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  error_code int(11) NOT NULL COMMENT 'Error code issues from the service.',
  error_text varchar(255) NOT NULL COMMENT 'Error text as supplied from the service.',
  error_issued_to_user_id varchar(30) NOT NULL COMMENT 'User ID service issued error to.',
  PRIMARY KEY (id),
  KEY post_id (post_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Errors in response to requests for post information.';

INSERT INTO tu_post_errors_b16 (SELECT * FROM tu_user_errors);

RENAME TABLE tu_post_errors TO tu_post_errors_b15;
RENAME TABLE tu_post_errors_b16 TO tu_post_errors;
DROP TABLE tu_post_errors_b15;


ALTER TABLE  tu_mentions CHANGE  user_id  user_id VARCHAR( 30 ) NOT NULL
COMMENT  'The user ID inside the respective service, e.g. Twitter or Facebook user IDs.';

ALTER TABLE  tu_mentions_posts CHANGE  author_user_id  author_user_id VARCHAR( 30 ) NOT NULL
COMMENT  'Author user ID of the post which contains the mention on a given network.';

