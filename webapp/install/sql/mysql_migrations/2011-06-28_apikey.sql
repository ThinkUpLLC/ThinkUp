ALTER TABLE tu_owners ADD api_key VARCHAR(32) NULL DEFAULT NULL COMMENT 'Key for API controller auth';
UPDATE tu_owners SET api_key = sha1( concat( sha1( concat( pwd, 'ab194d42da0dff4a5c01ad33cb4f650a7069178b' ) ), 'ab194d42da0dff4a5c01ad33cb4f650a7069178b' ) );
ALTER TABLE tu_owners CHANGE api_key api_key VARCHAR(32) NOT NULL COMMENT 'Key for API controller auth';