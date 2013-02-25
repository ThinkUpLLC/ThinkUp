-- Instead of altering the table, create a new one, transfer rows, and drop old one
-- ALTER TABLE tu_instances_twitter DROP last_unfav_page_checked;
-- ALTER TABLE tu_instances_twitter DROP last_page_fetched_favorites;

CREATE TABLE tu_instances_twitter_20b3 (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  last_page_fetched_replies int(11) NOT NULL DEFAULT '1' COMMENT 'Last page of replies fetched for this instance.',
  last_page_fetched_tweets int(11) NOT NULL DEFAULT '1' COMMENT 'Last page of tweets fetched for this instance.',
  last_favorite_id varchar(80) DEFAULT NULL COMMENT 'Last favorite post ID of the instance saved.',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Twitter-specific instance metadata.';

INSERT INTO tu_instances_twitter_20b3 (SELECT id, last_page_fetched_replies, last_page_fetched_tweets, last_favorite_id FROM tu_instances_twitter)#rollback=1;

RENAME TABLE tu_instances_twitter TO tu_instances_twitter20b2;

RENAME TABLE tu_instances_twitter_20b3 TO tu_instances_twitter;

DROP TABLE IF EXISTS tu_instances_twitter20b2;

DELETE FROM tu_options WHERE option_name='favs_older_pages' OR option_name='favs_cleanup_pages';