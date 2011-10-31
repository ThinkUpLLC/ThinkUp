-- This lengthy database migration makes several alterations to accommodate Google+ data:
-- * Changes user ID from a bigint to a varchar(30) across all tables
-- * Changes post ID from a bigint to a varchar(80) across all tables
-- * Changes posts.post_text from a varchar to a lengthier text field


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
ALTER TABLE  tu_users_b16 CHANGE  last_post_id  last_post_id varchar( 80 ) NOT NULL 
COMMENT 'Network post ID of the latest post the user authored.';

INSERT INTO tu_users_b16 (SELECT * FROM tu_users)#rollback=3;

RENAME TABLE tu_users TO tu_users_b15;
RENAME TABLE tu_users_b16 TO tu_users;
DROP TABLE IF EXISTS tu_users_b15;


DROP TABLE IF EXISTS tu_posts_b16;
CREATE TABLE tu_posts_b16 LIKE tu_posts;

ALTER TABLE  tu_posts_b16 CHANGE  author_user_id  author_user_id VARCHAR( 30 ) NOT NULL
COMMENT  'The user ID inside the respective service, e.g. Twitter or Facebook user IDs.';

ALTER TABLE  tu_posts_b16 CHANGE  in_reply_to_user_id  in_reply_to_user_id VARCHAR( 30 ) NULL DEFAULT NULL
COMMENT  'The ID of the user that this post is in reply to.';

ALTER TABLE  tu_posts_b16 CHANGE  in_rt_of_user_id  in_rt_of_user_id VARCHAR( 30 ) NULL DEFAULT NULL
COMMENT  'The ID of the user that this post is retweeting. [Twitter-specific]';

ALTER TABLE  tu_posts_b16 CHANGE  post_id  post_id varchar( 80 ) NOT NULL
COMMENT  'The ID of the post inside the respective service.';

ALTER TABLE  tu_posts_b16 CHANGE  in_reply_to_post_id  in_reply_to_post_id varchar( 80 ) NULL DEFAULT NULL
COMMENT  'The ID of the post that this post is in reply to.';

ALTER TABLE  tu_posts_b16 CHANGE  in_retweet_of_post_id  in_retweet_of_post_id varchar( 80 ) NULL DEFAULT NULL
COMMENT  'The ID of the post that this post is a retweet of. [Twitter-specific]';

ALTER TABLE  tu_posts_b16 CHANGE post_text  post_text TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
COMMENT  'The textual content of a user''s post on a given service.';

INSERT INTO tu_posts_b16 (SELECT * FROM tu_posts)#rollback=8;

RENAME TABLE tu_posts TO tu_posts_b15;
RENAME TABLE tu_posts_b16 TO tu_posts;
DROP TABLE IF EXISTS tu_posts_b15;


DROP TABLE IF EXISTS tu_follows_b16;
CREATE TABLE tu_follows_b16 LIKE tu_follows;

ALTER TABLE  tu_follows_b16 CHANGE  follower_id  follower_id VARCHAR( 30 ) NOT NULL
COMMENT  'User ID on a particular service who has followed user_id.';

ALTER TABLE  tu_follows_b16 CHANGE  user_id  user_id VARCHAR( 30 ) NOT NULL
COMMENT  'User ID on a particular service who has been followed.';

INSERT INTO tu_follows_b16 (SELECT * FROM tu_follows)#rollback=3;

RENAME TABLE tu_follows TO tu_follows_b15;
RENAME TABLE tu_follows_b16 TO tu_follows;
DROP TABLE IF EXISTS tu_follows_b15;


DROP TABLE IF EXISTS tu_follower_count_b16;
CREATE TABLE tu_follower_count_b16 (
  network_user_id varchar(30) NOT NULL COMMENT 'User ID on a particular service with a follower count.',
  network varchar(20) NOT NULL COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  date date NOT NULL COMMENT 'Date of follower count.',
  count int(11) NOT NULL COMMENT 'Total number of followers.'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Follower counts by date and time.';

INSERT INTO tu_follower_count_b16 (SELECT * FROM tu_follower_count)#rollback=1;

RENAME TABLE tu_follower_count TO tu_follower_count_b15;
RENAME TABLE tu_follower_count_b16 TO tu_follower_count;
DROP TABLE IF EXISTS tu_follower_count_b15;


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

INSERT INTO tu_user_errors_b16 (SELECT * FROM tu_user_errors)#rollback=1;

RENAME TABLE tu_user_errors TO tu_user_errors_b15;
RENAME TABLE tu_user_errors_b16 TO tu_user_errors;
DROP TABLE IF EXISTS tu_user_errors_b15;


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

ALTER TABLE  tu_post_errors CHANGE  post_id  post_id varchar( 80 ) NOT NULL
COMMENT 'Post ID on the originating service.';

INSERT INTO tu_post_errors_b16 (SELECT * FROM tu_user_errors)#rollback=2;

RENAME TABLE tu_post_errors TO tu_post_errors_b15;
RENAME TABLE tu_post_errors_b16 TO tu_post_errors;
DROP TABLE IF EXISTS tu_post_errors_b15;


ALTER TABLE  tu_mentions CHANGE  user_id  user_id VARCHAR( 30 ) NOT NULL
COMMENT  'The user ID inside the respective service, e.g. Twitter or Facebook user IDs.';

ALTER TABLE  tu_mentions_posts CHANGE  author_user_id  author_user_id VARCHAR( 30 ) NOT NULL
COMMENT  'Author user ID of the post which contains the mention on a given network.';

ALTER TABLE  tu_favorites CHANGE  post_id  post_id varchar( 80 ) NOT NULL
COMMENT  'Post ID on a given network.';

ALTER TABLE  tu_hashtags_posts CHANGE  post_id  post_id varchar( 80 ) NOT NULL
COMMENT  'Post ID on a given network.';

ALTER TABLE  tu_instances CHANGE  last_post_id  last_post_id varchar( 80 ) NOT NULL
COMMENT  'Last network post ID fetched for this instance.';

-- Don't alter tu_links b/c it will throw a "Specified key was too long; max key length is 1000 bytes" error
-- ALTER TABLE  tu_links CHANGE  post_id  post_id varchar( 80 ) NOT NULL


ALTER TABLE  tu_mentions_posts CHANGE  post_id  post_id varchar( 80 ) NOT NULL
COMMENT  'Post ID on a given network.';

ALTER TABLE  tu_places_posts CHANGE  post_id  post_id varchar( 80 ) NOT NULL
COMMENT  'Post ID on a given network.';

ALTER TABLE  tu_instances_twitter CHANGE  last_favorite_id  last_favorite_id varchar( 80 ) DEFAULT NULL 
COMMENT  'Last favorite post ID of the instance saved.';

ALTER TABLE  tu_user_errors CHANGE  error_issued_to_user_id  error_issued_to_user_id VARCHAR( 30 ) NOT NULL
COMMENT  'User ID service issued error to.';


-- tu_post_errors
-- Due to a bug in the prior migration, the table structure has to be recreated with comments.
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

ALTER TABLE  tu_post_errors_b16 CHANGE  post_id  post_id varchar( 80 ) NOT NULL
COMMENT 'Post ID on the originating service.';

-- Don't insert new data b/c it was corrupted in the prior migration. 
-- Losing what few rows might have been isn't a concern because this table is for debugging, doesn't affect display.

RENAME TABLE tu_post_errors TO tu_post_errors_b15;
RENAME TABLE tu_post_errors_b16 TO tu_post_errors;
DROP TABLE IF EXISTS tu_post_errors_b15;
