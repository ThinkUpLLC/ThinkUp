ALTER TABLE tu_owner_instances
ADD COLUMN `is_twitter_referenced_instance` INT(1) NULL DEFAULT 0 COMMENT 'Indicates if the oauth_access is taken from another referenced instance' AFTER `auth_error`;
