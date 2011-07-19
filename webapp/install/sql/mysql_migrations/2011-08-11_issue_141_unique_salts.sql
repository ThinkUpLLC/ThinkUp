-- Add password salt field
ALTER TABLE tu_owners ADD pwd_salt VARCHAR( 256 ) COMMENT  'Salt for securely hashing the owner password'  AFTER `pwd`;
-- Fill in default value
UPDATE tu_owners SET pwd_salt = 'ab194d42da0dff4a5c01ad33cb4f650a7069178b' WHERE 1;
-- Should never be null
ALTER TABLE tu_owners CHANGE pwd_salt pwd_salt VARCHAR( 256 ) NOT NULL COMMENT  'Salt for securely hashing the owner password';

ALTER TABLE tu_owners MODIFY pwd varchar( 256 ) COMMENT 'Hash of the owner password';
