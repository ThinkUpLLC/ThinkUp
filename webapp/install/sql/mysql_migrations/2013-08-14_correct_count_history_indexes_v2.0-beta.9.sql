--
-- Improve indexes on count_history for easy weekly charting
--

CREATE TABLE `tu_count_history_2b8` LIKE tu_count_history;

ALTER TABLE  `tu_count_history_2b8` DROP INDEX  type;

ALTER TABLE  `tu_count_history_2b8` DROP INDEX  type_2;

ALTER TABLE  `tu_count_history_2b8` ADD INDEX  network_user_id (  network ,  type ,  network_user_id );

ALTER TABLE  `tu_count_history_2b8` ADD INDEX  post_id (  network ,  type ,  post_id );

ALTER TABLE  `tu_count_history_2b8` ADD INDEX (  date );

INSERT IGNORE INTO `tu_count_history_2b8` (SELECT * FROM tu_count_history) #rollback=6;

RENAME TABLE tu_count_history TO `tu_count_history_2b7`;

RENAME TABLE `tu_count_history_2b8` TO tu_count_history;

DROP TABLE IF EXISTS `tu_count_history_2b7`;
