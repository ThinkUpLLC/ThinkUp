ALTER TABLE  tu_owners 
ADD timezone VARCHAR( 50 ) NOT NULL DEFAULT  'UTC' COMMENT  'Owner timezone.',
ADD membership_level VARCHAR( 20 ) NULL DEFAULT NULL COMMENT  'ThinkUp.com membership level.';
