--
-- Refactors the follower count table for use as a generic count store
--

CREATE TABLE tu_count_history LIKE tu_follower_count;

ALTER TABLE tu_count_history  COMMENT 'Item counts by date on a network.';

ALTER TABLE tu_count_history MODIFY COLUMN network_user_id VARCHAR( 30 ) COMMENT 'User ID on a given network associated with this count.';

ALTER TABLE tu_count_history ADD post_id VARCHAR(80) COMMENT 'Post ID on a given network associated with this count.' DEFAULT NULL AFTER network_user_id;

ALTER TABLE tu_count_history ADD type VARCHAR(80) COMMENT 'Type of item counted.' NOT NULL AFTER network;

ALTER TABLE tu_count_history MODIFY COLUMN count INT COMMENT 'Total number of the item specified in type.' NOT NULL;

ALTER TABLE tu_count_history MODIFY COLUMN date DATE COMMENT 'Date this count was recorded.' NOT NULL;

ALTER TABLE tu_count_history ADD INDEX ( type, network, date, network_user_id );

ALTER TABLE tu_count_history ADD INDEX ( type, network, date, post_id );

INSERT IGNORE INTO tu_count_history (SELECT network_user_id, NULL, network, 'followers', date, count FROM tu_follower_count) #rollback=9;

DROP TABLE IF EXISTS tu_follower_count;
