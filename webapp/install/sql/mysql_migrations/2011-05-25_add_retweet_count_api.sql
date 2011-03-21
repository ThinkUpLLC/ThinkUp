
ALTER TABLE tu_posts ADD retweet_count_api int(11) NOT NULL DEFAULT '0' COMMENT 'The total number of native retweets as reported by Twitter API. [Twitter-specific]';
ALTER TABLE  `tu_posts` CHANGE  `retweet_count_cache`  `retweet_count_cache` INT( 11 ) NOT NULL DEFAULT  '0' COMMENT  'Manual count of native retweets as determined by ThinkUp. [Twitter-specific]';

