CREATE TABLE IF NOT EXISTS tu_insights (
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

DROP TABLE IF EXISTS tu_follows_1_0_8;
CREATE TABLE tu_follows_1_0_8 LIKE tu_follows;
ALTER TABLE tu_follows_1_0_8 DROP INDEX user_id; 
ALTER TABLE tu_follows_1_0_8 ADD UNIQUE INDEX network_follower_user(network,follower_id,user_id);
ALTER TABLE tu_follows_1_0_8 ADD INDEX user_id ( user_id, network, active );

INSERT IGNORE INTO tu_follows_1_0_8 (SELECT * FROM tu_follows)#rollback=5;

RENAME TABLE tu_follows TO tu_follows_1_0_7;

RENAME TABLE tu_follows_1_0_8 TO tu_follows;

DROP TABLE IF EXISTS tu_follows_1_0_7;
