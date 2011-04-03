--
-- Fix typo and quad single quotes
--
ALTER TABLE  tu_instances CHANGE  id  id INT( 11 ) NOT NULL AUTO_INCREMENT COMMENT  'Internal unique ID.';
ALTER TABLE  tu_instances CHANGE  network_user_id  network_user_id BIGINT( 11 ) NOT NULL COMMENT  'User ID on a given network, like a user''s Twitter ID or Facebook user ID.';
ALTER TABLE  tu_instances CHANGE  network_username  network_username VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  'Username on a given network, like a user''s Twitter username or Facebook user name.';
ALTER TABLE  tu_instances CHANGE  percentage_links  percentage_links DECIMAL( 4, 2 ) NULL DEFAULT NULL COMMENT  'Percent of an instance''s posts which contain links.';
