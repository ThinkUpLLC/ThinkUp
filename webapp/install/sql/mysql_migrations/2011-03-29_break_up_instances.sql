--
-- Create Twitter plugin instances table.
--
CREATE TABLE tu_instances_twitter (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  last_page_fetched_replies int(11) NOT NULL DEFAULT '1' COMMENT 'Last page of replies fetched for this instance.',
  last_page_fetched_tweets int(11) NOT NULL DEFAULT '1' COMMENT 'Last page of tweets fetched for this instance.',
  last_favorite_id bigint(20) unsigned DEFAULT NULL COMMENT 'Last favorite post ID of the instance saved.',
  last_unfav_page_checked int(11) DEFAULT '0' COMMENT 'Last page of older favorites checked for backfilling.',
  last_page_fetched_favorites int(11) DEFAULT NULL COMMENT 'Last page of favorites fetched.',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Twitter-specific instance metadata.';

--
-- Transfer data into plugin table.
--
INSERT INTO tu_instances_twitter 
(SELECT id, last_page_fetched_replies, last_page_fetched_tweets, last_favorite_id, last_unfav_page_checked, 
last_page_fetched_favorites FROM tu_instances WHERE network='twitter');


--
-- Drop Twitter-specific fields from core table.
--
ALTER TABLE  tu_instances DROP  last_page_fetched_replies;
ALTER TABLE  tu_instances DROP  last_page_fetched_tweets;
ALTER TABLE  tu_instances DROP  last_favorite_id;
ALTER TABLE  tu_instances DROP  last_unfav_page_checked;
ALTER TABLE  tu_instances DROP  last_page_fetched_favorites;
