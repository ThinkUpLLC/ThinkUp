--
-- Table structure for table tu_instances_hashtags
--

CREATE TABLE IF NOT EXISTS tu_instances_hashtags (
  id INT(20) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  instance_id INT(11) NOT NULL COMMENT 'Instance ID.',
  hashtag_id INT(11) NOT NULL COMMENT 'Hashtag ID.',
  last_post_id VARCHAR(80) NOT NULL DEFAULT 0 COMMENT 'Last network post ID fetched for this hashtag search.',
  earliest_post_id VARCHAR(80) NOT NULL DEFAULT 0 COMMENT 'Earliest network post ID fetched for this hashtag search.',
  PRIMARY KEY (id),
  UNIQUE KEY instance_id (instance_id,hashtag_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT = 'Hashtags an instance saved to capture search results.';