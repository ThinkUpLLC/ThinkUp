-- Add pwd salt field
ALTER TABLE tu_owners ADD pwd_salt VARCHAR( 256 ) DEFAULT 'ab194d42da0dff4a5c01ad33cb4f650a7069178b' COMMENT  'Salt for securely hashing the owners password'  AFTER `pwd`;
UPDATE tu_owners SET pwd_salt = 'ab194d42da0dff4a5c01ad33cb4f650a7069178b' WHERE 1;
ALTER TABLE tu_owners MODIFY pwd varchar( 256 ) COMMENT 'Hash of the owners password';
