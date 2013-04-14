ALTER TABLE tu_owners DROP activation_code;
ALTER TABLE tu_owners ADD activation_token varchar(64) DEFAULT NULL COMMENT 'Activation token.';