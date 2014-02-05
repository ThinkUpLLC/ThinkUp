--
-- Create the sessions table
--

CREATE TABLE tu_sessions (
    `session_id` VARCHAR(100) NOT NULL PRIMARY KEY COMMENT 'Session Key',
    `data` MEDIUMTEXT NOT NULL COMMENT 'Serialized session data',
    `updated` DATETIME NOT NULL COMMENT 'Last update',
    INDEX(updated)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Sessions table';
