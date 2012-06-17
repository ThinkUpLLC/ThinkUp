CREATE TABLE tu_insights (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  instance_id int(11) NOT NULL COMMENT 'Instance ID.',
  slug varchar(100) NOT NULL COMMENT 'Identifier for a type of statistic.',
  `text` varchar(255) NOT NULL COMMENT 'Text content of the alert.',
  related_data text COMMENT 'Serialized related insight data, such as a list of users or a post.',
  `date` date NOT NULL COMMENT 'Date of insight.',
  emphasis int(11) NOT NULL DEFAULT '0' COMMENT 'Level of emphasis for insight presentation.',
  PRIMARY KEY (id),
  KEY instance_id (instance_id,slug,date)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Insights for a given service user.';

-- Add follows index to speed up instances table update at the end of crawl:
-- DROP INDEX user_id; ADD UNIQUE INDEX network_follower_user(network,follower_id,user_id);
-- ALTER TABLE  tu_follows ADD INDEX user_id (  user_id ,  network ,  active );

CREATE TABLE tu_follows_1_0_8 (
  user_id varchar(30) NOT NULL COMMENT 'User ID on a particular service who has been followed.',
  follower_id varchar(30) NOT NULL COMMENT 'User ID on a particular service who has followed user_id.',
  last_seen timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last time this relationship was seen on the originating network.',
  first_seen timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'First time this relationship was seen on the originating network.',
  active int(11) NOT NULL DEFAULT '1' COMMENT 'Whether or not the relationship is active (1 if so, 0 if not.)',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  debug_api_call varchar(255) NOT NULL COMMENT 'Developer-only field for storing the API URL source of this data point.',
  UNIQUE KEY network_follower_user (network,follower_id,user_id),
  KEY active (network,active,last_seen),
  KEY network (network,last_seen),
  KEY user_id (user_id,network,active)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Service user follow and friend relationships.';

INSERT IGNORE INTO tu_follows_1_0_8 (SELECT * FROM tu_follows)#rollback=1;

RENAME TABLE tu_follows TO tu_follows_1_0_7;

RENAME TABLE tu_follows_1_0_8 TO tu_follows;

DROP TABLE IF EXISTS tu_follows_1_0_7;
