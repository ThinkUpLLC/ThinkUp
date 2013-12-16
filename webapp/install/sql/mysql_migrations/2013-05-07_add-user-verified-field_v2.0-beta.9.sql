--
-- Add is_verified field to users table
--

ALTER TABLE `tu_users` ADD `is_verified` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not the user is verified by the network.' AFTER `url`;