CREATE TABLE tu_options (
  option_id int(11) auto_increment NOT NULL,
  namespace varchar(50) NOT NULL,
  option_name varchar(50) NOT NULL,
  option_value varchar(255) NOT NULL,
  last_updated datetime NOT NULL,
  created datetime NOT NULL,
  PRIMARY KEY (option_id),
  KEY namespace_key (namespace),
  KEY name_key (option_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;