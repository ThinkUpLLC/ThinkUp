--
-- Post Errors
--
ALTER TABLE tu_post_errors DROP INDEX status_id;

ALTER TABLE tu_post_errors ADD network VARCHAR( 10 ) COLLATE utf8_general_ci NOT NULL DEFAULT 'twitter' AFTER post_id;

ALTER TABLE tu_post_errors ADD INDEX ( post_id , network ) ;

--
-- Users
--
ALTER TABLE tu_users DROP INDEX user_id;

ALTER TABLE tu_users ADD INDEX ( user_id , network ) ;

--
-- Links
--
ALTER TABLE tu_links ADD network VARCHAR( 10 ) COLLATE utf8_general_ci NOT NULL DEFAULT 'twitter' AFTER post_id;

ALTER TABLE tu_links DROP INDEX status_id;

ALTER TABLE tu_links ADD INDEX ( post_id , network ) ;