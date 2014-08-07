--
-- Create the user_verions table
--

CREATE TABLE tu_user_versions (
    id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Internal unique ID.',
    user_key INT ( 11 ) NOT NULL COMMENT 'Internal ID of the user this version applies to.',
    field_name varchar(100) NOT NULL COMMENT 'Field name from the users table.',
    field_value text comment 'Field value from the users table.',
    crawl_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When this version was captured.',
    index (user_key, crawl_time)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Version history of user data.';
