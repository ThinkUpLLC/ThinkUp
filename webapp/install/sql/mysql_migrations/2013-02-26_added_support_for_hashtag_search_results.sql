--
-- Table structure for table tu_instances_hashtags
--

DROP TABLE IF EXISTS tu_instances_hashtags;

CREATE TABLE tu_instances_hashtags (
  id INT(20) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  instance_id INT(11) NOT NULL COMMENT 'Instance ID.',
  hashtag_id INT(11) NOT NULL COMMENT 'Hashtag ID.',
  last_post_id VARCHAR(80) NOT NULL DEFAULT 0 COMMENT 'Last network post ID fetched for this hashtag search',
  earliest_post_id VARCHAR(80) NOT NULL DEFAULT 0 COMMENT 'Earliest network post ID fetched for this hashtag search',
  last_page_fetched_tweets INTEGER NOT NULL DEFAULT 1 COMMENT 'Last page of tweets fetched for this instance and hashtag',
  PRIMARY KEY (id)  
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT = 'Hashtags that an instance wants to capture';