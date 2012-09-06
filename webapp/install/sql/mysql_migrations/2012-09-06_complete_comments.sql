--
-- Comment encoded_locations table
--
CREATE TABLE `tu_encoded_locations_1.1` LIKE tu_encoded_locations;
ALTER TABLE  `tu_encoded_locations_1.1` COMMENT =  'Geo-encoded locations.';
ALTER TABLE  `tu_encoded_locations_1.1` CHANGE  `id`  `id` INT( 11 ) NOT NULL AUTO_INCREMENT COMMENT  'Internal unique ID.';
ALTER TABLE  `tu_encoded_locations_1.1` CHANGE  `short_name`  `short_name` VARCHAR( 255 ) NOT NULL COMMENT  'Short name of a location, such as NYC.';
ALTER TABLE  `tu_encoded_locations_1.1` CHANGE  `full_name`  `full_name` VARCHAR( 255 ) NOT NULL COMMENT  'Full name of location, such as New York, NY, USA.';
ALTER TABLE  `tu_encoded_locations_1.1` CHANGE  `latlng`  `latlng` VARCHAR( 50 ) NOT NULL COMMENT  'Latitude and longitude coordinates of a place, comma-delimited.';

INSERT INTO `tu_encoded_locations_1.1` (SELECT * FROM tu_encoded_locations)#rollback=1;

RENAME TABLE tu_encoded_locations TO `tu_encoded_locations_1.0`;

RENAME TABLE `tu_encoded_locations_1.1` TO tu_encoded_locations;

DROP TABLE IF EXISTS `tu_encoded_locations_1.0`;


--
-- Comment invites table
--
CREATE TABLE `tu_invites_1.1` LIKE tu_invites;
ALTER TABLE  `tu_invites_1.1` COMMENT =  'Individual user registration invitations.';
ALTER TABLE  `tu_invites_1.1` CHANGE  `invite_code`  `invite_code` VARCHAR( 10 ) NULL DEFAULT NULL COMMENT  'Invitation code.';
ALTER TABLE  `tu_invites_1.1` CHANGE  `created_time`  `created_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT  'Time the invitation was created, used to calculate expiration time.';

INSERT INTO `tu_invites_1.1` (SELECT * FROM tu_invites)#rollback=1;

RENAME TABLE tu_invites TO `tu_invites_1.0`;

RENAME TABLE `tu_invites_1.1` TO tu_invites;

DROP TABLE IF EXISTS `tu_invites_1.0`;


--
-- Comment options table
--
CREATE TABLE `tu_options_1.1` LIKE tu_options;
ALTER TABLE  `tu_options_1.1` COMMENT =  'Application and plugin options or settings.';
ALTER TABLE  `tu_options_1.1` CHANGE  `option_id`  `option_id` INT( 11 ) NOT NULL AUTO_INCREMENT COMMENT  'Unique internal ID.';
ALTER TABLE  `tu_options_1.1` CHANGE  `namespace`  `namespace` VARCHAR( 50 ) NOT NULL COMMENT  'Option namespace, ie, application or specific plugin.';
ALTER TABLE  `tu_options_1.1` CHANGE  `option_name`  `option_name` VARCHAR( 50 ) NOT NULL COMMENT  'Name of option or setting.';
ALTER TABLE  `tu_options_1.1` CHANGE  `option_value`  `option_value` VARCHAR( 255 ) NOT NULL COMMENT  'Value of option.';
ALTER TABLE  `tu_options_1.1` CHANGE  `last_updated`  `last_updated` DATETIME NOT NULL COMMENT  'Last time option was updated.';
ALTER TABLE  `tu_options_1.1` CHANGE  `created`  `created` DATETIME NOT NULL COMMENT  'When option was created.';

INSERT INTO `tu_options_1.1` (SELECT * FROM tu_options)#rollback=1;

RENAME TABLE tu_options TO `tu_options_1.0`;

RENAME TABLE `tu_options_1.1` TO tu_options;

DROP TABLE IF EXISTS `tu_options_1.0`;


--
-- Comment plugins table
--
CREATE TABLE `tu_plugins_1.1` LIKE tu_plugins;
ALTER TABLE  `tu_plugins_1.1` COMMENT =  'Application plugins.';
ALTER TABLE  `tu_plugins_1.1` CHANGE  `id`  `id` INT( 11 ) NOT NULL AUTO_INCREMENT COMMENT  'Internal unique ID.';
ALTER TABLE  `tu_plugins_1.1` CHANGE  `name`  `name` VARCHAR( 255 ) NOT NULL COMMENT  'Plugin display name, such as Hello ThinkUp.';
ALTER TABLE  `tu_plugins_1.1` CHANGE  `folder_name`  `folder_name` VARCHAR( 255 ) NOT NULL COMMENT  'Name of folder where plugin lives.';
ALTER TABLE  `tu_plugins_1.1` CHANGE  `description`  `description` VARCHAR( 255 ) NULL DEFAULT NULL COMMENT  'Plugin description.';
ALTER TABLE  `tu_plugins_1.1` CHANGE  `author`  `author` VARCHAR( 255 ) NULL DEFAULT NULL COMMENT  'Plugin author.';
ALTER TABLE  `tu_plugins_1.1` CHANGE  `homepage`  `homepage` VARCHAR( 255 ) NULL DEFAULT NULL COMMENT  'Plugin homepage URL.';
ALTER TABLE  `tu_plugins_1.1` CHANGE  `version`  `version` VARCHAR( 255 ) NULL DEFAULT NULL COMMENT  'Plugin version.';
ALTER TABLE  `tu_plugins_1.1` CHANGE  `is_active`  `is_active` TINYINT( 4 ) NOT NULL COMMENT  'Whether or not the plugin is activated (1 if so, 0 if not.)';

INSERT INTO `tu_plugins_1.1` (SELECT * FROM tu_plugins)#rollback=1;

RENAME TABLE tu_plugins TO `tu_plugins_1.0`;

RENAME TABLE `tu_plugins_1.1` TO tu_plugins;

DROP TABLE IF EXISTS `tu_plugins_1.0`;
