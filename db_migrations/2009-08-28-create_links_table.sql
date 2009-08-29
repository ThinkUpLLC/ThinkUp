CREATE TABLE IF NOT EXISTS links (
  id int(11) NOT NULL AUTO_INCREMENT,
  url varchar(255) COLLATE utf8_bin NOT NULL,
  expanded_url varchar(255) COLLATE utf8_bin NOT NULL,
  title varchar(255) COLLATE utf8_bin NOT NULL,
  clicks int(11) NOT NULL DEFAULT '0',
  status_id bigint(11) NOT NULL,
  PRIMARY KEY (id),
  KEY status_id (status_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;