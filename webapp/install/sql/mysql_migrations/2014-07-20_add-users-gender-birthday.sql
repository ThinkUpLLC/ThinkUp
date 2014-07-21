--
-- Add two columns to the users table for storing gender and birthday
--
DROP TABLE IF EXISTS tu_users_20b11;

CREATE TABLE tu_users_20b11 LIKE tu_users;

ALTER TABLE tu_users_20b11 ADD gender VARCHAR ( 255 ) DEFAULT NULL COMMENT 'Service user''s gender with room for custom options.' AFTER url#rollback=2;

ALTER TABLE tu_users_20b11 ADD birthday date DEFAULT NULL COMMENT 'Service user''s birthday.' AFTER gender#rollback=3;

INSERT IGNORE INTO tu_users_20b11 (SELECT id, user_id, user_name, full_name, avatar, location, description, url, null, null, is_verified, is_protected, follower_count, friend_count, post_count, last_updated, found_in, last_post, joined, last_post_id, network, favorites_count FROM tu_users)#rollback=4;

RENAME TABLE tu_users TO tu_users_20b10;

RENAME TABLE tu_users_20b11 TO tu_users;

DROP TABLE IF EXISTS tu_users_20b10;




