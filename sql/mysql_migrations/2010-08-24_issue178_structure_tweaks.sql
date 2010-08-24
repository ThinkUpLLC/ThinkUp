ALTER TABLE  tu_follows DROP PRIMARY KEY;

ALTER TABLE  tu_follows ADD UNIQUE (user_id, follower_id, network);

ALTER TABLE  tu_instances DROP INDEX  twitter_user_id;

ALTER TABLE  tu_instances ADD INDEX (  network_user_id ,  network );

ALTER TABLE  tu_post_errors CHANGE  error_issued_to_user_id  error_issued_to_user_id BIGINT( 11 ) NOT NULL;

ALTER TABLE  tu_posts CHANGE  in_reply_to_user_id  in_reply_to_user_id  BIGINT( 11 ) NULL DEFAULT NULL;

ALTER TABLE  tu_user_errors CHANGE  user_id  user_id BIGINT( 11 ) NOT NULL;

ALTER TABLE  tu_user_errors CHANGE  error_issued_to_user_id  error_issued_to_user_id BIGINT( 11 ) NOT NULL;

ALTER TABLE  tu_follower_count CHANGE  network_user_id  network_user_id BIGINT( 11 ) NOT NULL;

ALTER TABLE  tu_follows CHANGE  user_id  user_id BIGINT( 11 ) NOT NULL;

ALTER TABLE  tu_follows CHANGE  follower_id  follower_id BIGINT( 11 ) NOT NULL;

ALTER TABLE  tu_instances CHANGE  network_viewer_id  network_viewer_id BIGINT( 11 ) NOT NULL DEFAULT  '1';

ALTER TABLE  tu_post_errors CHANGE  post_id  post_id BIGINT( 11 ) NOT NULL;