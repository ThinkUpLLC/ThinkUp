CREATE TABLE tu_follows_b10 (
  user_id bigint(11) NOT NULL,
  follower_id bigint(11) NOT NULL,
  last_seen timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  active int(11) NOT NULL DEFAULT '1',
  network varchar(20) NOT NULL DEFAULT 'twitter',
  debug_api_call varchar(255) NOT NULL,
  UNIQUE KEY user_id (network,follower_id,user_id),
  KEY active (network,  active, last_seen),
  KEY network (network,last_seen)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO tu_follows_b10 (SELECT * FROM tu_follows);

RENAME TABLE tu_follows TO tu_follows_b9;
RENAME TABLE tu_follows_b10 TO tu_follows;

DROP TABLE tu_follows_b9;