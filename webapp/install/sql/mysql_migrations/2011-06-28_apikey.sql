-- Add API key field
ALTER TABLE tu_owners ADD api_key VARCHAR(32) NULL DEFAULT NULL COMMENT 'Key for API controller auth';
UPDATE tu_owners SET api_key = sha1( concat( sha1( concat( pwd, 'ab194d42da0dff4a5c01ad33cb4f650a7069178b' ) ), 'ab194d42da0dff4a5c01ad33cb4f650a7069178b' ) );
ALTER TABLE tu_owners CHANGE api_key api_key VARCHAR(32) NOT NULL COMMENT 'Key for API controller auth';

ALTER TABLE tu_owners ADD  pwd_salt VARCHAR( 256 ) DEFAULT 'ab194d42da0dff4a5c01ad33cb4f650a7069178b' COMMENT  'Salt for securely storing the users password' AFTER  pwd;
UPDATE tu_owners SET pwd_salt = 'ab194d42da0dff4a5c01ad33cb4f650a7069178b' WHERE 1;

-- While we're ALTERing the tu_owners table, might as well fill in missing field comments

CREATE TABLE tu_owners_b14 (
  id int(20) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  full_name varchar(200) NOT NULL COMMENT 'User full name.',
  pwd varchar(256) NOT NULL COMMENT 'Hashed user password.',
  pwd_salt VARCHAR( 256 ) DEFAULT 'ab194d42da0dff4a5c01ad33cb4f650a7069178b' COMMENT  'Salt for securely hashing the users password',
  email varchar(200) NOT NULL COMMENT 'User email.',
  activation_code int(10) NOT NULL DEFAULT '0' COMMENT 'User activation code.',
  joined date NOT NULL DEFAULT '0000-00-00' COMMENT 'Date user registered for an account.',
  is_activated int(1) NOT NULL DEFAULT '0' COMMENT 'If user is activated, 1 for true, 0 for false.',
  is_admin int(1) NOT NULL DEFAULT '0' COMMENT 'If user is an admin, 1 for true, 0 for false.',
  last_login date NOT NULL DEFAULT '0000-00-00' COMMENT 'Last time user logged into ThinkUp.',
  password_token varchar(64) DEFAULT NULL COMMENT 'Password reset token.',
  failed_logins int(11) NOT NULL DEFAULT '0' COMMENT 'Current number of failed login attempts.',
  account_status varchar(150) NOT NULL DEFAULT '' COMMENT 'Description of account status, i.e., "Inactive due to excessive failed login attempts".',
  api_key varchar(32) NOT NULL COMMENT 'Key to authorize API calls.',
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'ThinkUp user account details.';

INSERT INTO tu_owners_b14 (SELECT id, full_name, pwd, pwd_salt, email, activation_code, joined, is_activated, is_admin, last_login, password_token, failed_logins, account_status, api_key FROM tu_owners);

RENAME TABLE tu_owners TO tu_owners_b13;

RENAME TABLE tu_owners_b14 TO tu_owners;

DROP TABLE tu_owners_b13;


