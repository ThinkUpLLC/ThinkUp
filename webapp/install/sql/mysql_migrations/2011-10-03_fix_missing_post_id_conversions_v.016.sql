--
-- Convert two post ID fields missed in the prior migration from bigint(20) to varchar(50)
--

-- tu_instances_twitter
ALTER TABLE  tu_instances_twitter CHANGE  last_favorite_id  last_favorite_id VARCHAR( 50 ) DEFAULT NULL 
COMMENT  'Last favorite post ID of the instance saved.';

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

ALTER TABLE  tu_post_errors_b16 CHANGE  post_id  post_id VARCHAR( 50 ) NOT NULL
COMMENT 'Post ID on the originating service.';

-- Don't insert new data b/c it was corrupted in the prior migration. 
-- Losing what few rows might have been isn't a problem of concern because this table is primarily for debugging, doesn't affect display.

RENAME TABLE tu_post_errors TO tu_post_errors_b15;
RENAME TABLE tu_post_errors_b16 TO tu_post_errors;
DROP TABLE tu_post_errors_b15;

-- tu_user_errors
ALTER TABLE  tu_user_errors CHANGE  error_issued_to_user_id  error_issued_to_user_id VARCHAR( 30 ) NOT NULL
COMMENT  'User ID service issued error to.';
