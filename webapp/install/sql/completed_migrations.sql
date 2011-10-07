CREATE TABLE `tu_completed_migrations` (
  `migration` varchar(255) NOT NULL COMMENT 'Completed migration - filename-index',
  `date_ran` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT 'The time the migration ran',
  `sql_ran` text COMMENT 'The migration sql that was executed',
  PRIMARY KEY (`migration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
