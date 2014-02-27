--
-- Create the sessions table
--

CREATE TABLE tu_sessions (
    session_id VARCHAR(100) NOT NULL PRIMARY KEY COMMENT 'Unique $_SESSION ID.',
    data MEDIUMTEXT NOT NULL COMMENT 'Serialized $_SESSION data.',
    updated DATETIME NOT NULL COMMENT 'Last updated time.',
    INDEX(updated)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='PHP $_SESSION data storage.';
