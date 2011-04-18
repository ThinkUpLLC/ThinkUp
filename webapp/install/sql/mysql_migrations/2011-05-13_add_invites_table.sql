CREATE TABLE tu_invites (
invite_code varchar(10) DEFAULT NULL,
created_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MYISAM DEFAULT CHARSET=utf8 COMMENT='User invitation codes.'
