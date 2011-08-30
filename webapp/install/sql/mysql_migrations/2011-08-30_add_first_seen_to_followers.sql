-- Add first_seen field to tu_follows
-- While we're in there, comment all the fields of the new table

CREATE TABLE tu_follows_b15 (
  user_id bigint(11) NOT NULL COMMENT 'User ID on a particular service who has been followed.',
  follower_id bigint(11) NOT NULL COMMENT 'User ID on a particular service who has followed user_id.',
  last_seen timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last time this relationship was seen on the originating network.',
  first_seen timestamp NOT NULL COMMENT 'First time this relationship was seen on the originating network.',
  active int(11) NOT NULL DEFAULT '1' COMMENT 'Whether or not the relationship is active (1 if so, 0 if not.)',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  debug_api_call varchar(255) NOT NULL COMMENT 'Developer-only field for storing the API URL source of this data point.',
  UNIQUE KEY user_id (network,follower_id,user_id),
  KEY active (network,active,last_seen),
  KEY network (network,last_seen)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Service user follow and friend relationships.';

INSERT INTO tu_follows_b15 (SELECT user_id, follower_id, last_seen, last_seen, active, network, debug_api_call FROM tu_follows);

RENAME TABLE tu_follows TO tu_follows_b14;

RENAME TABLE tu_follows_b15 TO tu_follows;

DROP TABLE tu_follows_b14;

