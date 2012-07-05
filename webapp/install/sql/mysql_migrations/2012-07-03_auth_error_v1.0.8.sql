DROP TABLE IF EXISTS tu_owner_instances_1_0_8;

CREATE TABLE tu_owner_instances_1_0_8 (
  id int(20) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  owner_id int(10) NOT NULL COMMENT 'Owner ID.',
  instance_id int(10) NOT NULL COMMENT 'Instance ID.',
  oauth_access_token varchar(255) DEFAULT NULL COMMENT 'OAuth access token (optional).',
  oauth_access_token_secret varchar(255) DEFAULT NULL COMMENT 'OAuth secret access token (optional).',
  auth_error varchar(255) DEFAULT NULL COMMENT 'Last authorization error, if there was one.',
  PRIMARY KEY (id),
  UNIQUE KEY owner_instance_id (owner_id, instance_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Service user auth tokens per owner.';

INSERT IGNORE INTO tu_owner_instances_1_0_8 (SELECT *, null FROM tu_owner_instances)#rollback=2;

RENAME TABLE tu_owner_instances TO tu_owner_instances_1_0_7;

RENAME TABLE tu_owner_instances_1_0_8 TO tu_owner_instances;

DROP TABLE IF EXISTS tu_owner_instances_1_0_7;
