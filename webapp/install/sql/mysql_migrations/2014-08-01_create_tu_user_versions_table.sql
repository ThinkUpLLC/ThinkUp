--
-- Create the user_verions table
--

CREATE TABLE tu_user_versions (
    id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Internal unique ID.',
    user_key INT ( 11 ) NOT NULL COMMENT 'Foreign Key to tu_users.id',
    field_name varchar(100) NOT NULL COMMENT 'Field name from tu_users table',
    field_value text comment 'Field value from tu_users table',
    crawl_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When this version was crawled.',
    index (user_key, crawl_time)
);
