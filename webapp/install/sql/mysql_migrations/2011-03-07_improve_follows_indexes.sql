--
-- Speed up queries on the follows table with better indexes
--

ALTER TABLE  tu_follows DROP INDEX  user_id, ADD UNIQUE  user_id (network, follower_id, user_id);

ALTER TABLE  tu_follows DROP INDEX  active, ADD INDEX  active (network,  active);

ALTER TABLE  tu_follows ADD UNIQUE (network, last_seen);