--
-- Comment encoded_locations table
--
CREATE TABLE IF NOT EXISTS `tu_encoded_locations_1_1` (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  short_name varchar(255) NOT NULL COMMENT 'Short name of a location, such as NYC.',
  full_name varchar(255) NOT NULL COMMENT 'Full name of location, such as New York, NY, USA.',
  latlng varchar(50) NOT NULL COMMENT 'Latitude and longitude coordinates of a place, comma-delimited.',
  PRIMARY KEY (id),
  KEY short_name (short_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Geo-encoded locations.';

INSERT INTO `tu_encoded_locations_1_1` (SELECT * FROM tu_encoded_locations)#rollback=1;

RENAME TABLE tu_encoded_locations TO `tu_encoded_locations_1_0`;

RENAME TABLE `tu_encoded_locations_1_1` TO tu_encoded_locations;

DROP TABLE IF EXISTS `tu_encoded_locations_1_0`;


--
-- Comment invites table
--
CREATE TABLE IF NOT EXISTS `tu_invites_1_1` (
  invite_code varchar(10) DEFAULT NULL COMMENT 'Invitation code.',
  created_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time the invitation was created, used to calculate expiration time.'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Individual user registration invitations.';

INSERT INTO `tu_invites_1_1` (SELECT * FROM tu_invites)#rollback=1;

RENAME TABLE tu_invites TO `tu_invites_1_0`;

RENAME TABLE `tu_invites_1_1` TO tu_invites;

DROP TABLE IF EXISTS `tu_invites_1_0`;


--
-- Comment options table
--
CREATE TABLE IF NOT EXISTS `tu_options_1_1` (
  option_id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique internal ID.',
  namespace varchar(50) NOT NULL COMMENT 'Option namespace, ie, application or specific plugin.',
  option_name varchar(50) NOT NULL COMMENT 'Name of option or setting.',
  option_value varchar(255) NOT NULL COMMENT 'Value of option.',
  last_updated datetime NOT NULL COMMENT 'Last time option was updated.',
  created datetime NOT NULL COMMENT 'When option was created.',
  PRIMARY KEY (option_id),
  KEY namespace_key (namespace),
  KEY name_key (option_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Application and plugin options or settings.';

INSERT INTO `tu_options_1_1` (SELECT * FROM tu_options)#rollback=1;

RENAME TABLE tu_options TO `tu_options_1_0`;

RENAME TABLE `tu_options_1_1` TO tu_options;

DROP TABLE IF EXISTS `tu_options_1_0`;


--
-- Comment plugins table
--
CREATE TABLE IF NOT EXISTS `tu_plugins_1_1` (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  name varchar(255) NOT NULL COMMENT 'Plugin display name, such as Hello ThinkUp.',
  folder_name varchar(255) NOT NULL COMMENT 'Name of folder where plugin lives.',
  description varchar(255) DEFAULT NULL COMMENT 'Plugin description.',
  author varchar(255) DEFAULT NULL COMMENT 'Plugin author.',
  homepage varchar(255) DEFAULT NULL COMMENT 'Plugin homepage URL.',
  version varchar(255) DEFAULT NULL COMMENT 'Plugin version.',
  is_active tinyint(4) NOT NULL COMMENT 'Whether or not the plugin is activated (1 if so, 0 if not.)',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Application plugins.';

INSERT INTO `tu_plugins_1_1` (SELECT * FROM tu_plugins)#rollback=1;

RENAME TABLE tu_plugins TO `tu_plugins_1_0`;

RENAME TABLE `tu_plugins_1_1` TO tu_plugins;

DROP TABLE IF EXISTS `tu_plugins_1_0`;