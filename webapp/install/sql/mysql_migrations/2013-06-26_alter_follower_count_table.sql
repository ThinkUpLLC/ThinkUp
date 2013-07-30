--
-- Refactors the follower count table for use as a generic count store
--

RENAME TABLE tu_follower_count TO tu_count_history;
ALTER TABLE tu_count_history ADD post_id VARCHAR(80) COMMENT 'ID of the post this count is associated with' AFTER network_user_id;
ALTER TABLE tu_count_history ADD type VARCHAR(80) COMMENT 'What this is a count of' NOT NULL AFTER network;
ALTER TABLE tu_count_history MODIFY COLUMN count INT COMMENT 'Total number of the item specified in type' NOT NULL;
ALTER TABLE tu_count_history MODIFY COLUMN date DATE COMMENT 'Date this count was recorded' NOT NULL;
UPDATE tu_count_history SET type = 'followers' WHERE TRUE;
