-- Changed the following ALTER to CREATE/INSERT INTO/DROP statements below for better migration performance

-- Convert posts.pub_date from TIMESTAMP to DATETIME to prevent automatic timezone conversions
-- ALTER TABLE tu_posts MODIFY pub_date DATETIME NOT NULL COMMENT 'The UTC date/time when this post was published.';
-- Convert every pub_date from localtime to UTC
-- UPDATE tu_posts SET pub_date = 
-- CONVERT_TZ(pub_date, '+00:00', 
--    TIME_FORMAT( SEC_TO_TIME( UNIX_TIMESTAMP( NOW() ) - UNIX_TIMESTAMP( UTC_TIMESTAMP() ) ), '%H:%i')
-- );

CREATE TABLE tu_posts_b14 (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID..',
  post_id bigint(20) unsigned NOT NULL COMMENT 'The ID of the post inside the respective service.',
  author_user_id bigint(11) NOT NULL COMMENT 'The user ID inside the respective service, e.g. Twitter or Facebook user IDs.',
  author_username varchar(50) NOT NULL COMMENT 'The user''s username inside the respective service, e.g. Twitter or Facebook user name.',
  author_fullname varchar(50) NOT NULL COMMENT 'The user''s real, full name on a given service, e.g. Gina Trapani.',
  author_avatar varchar(255) NOT NULL COMMENT 'The URL to the user''s avatar for a given service.',
  author_follower_count int(11) NOT NULL COMMENT 'Post author''s follower count. [Twitter-specific]',
  post_text varchar(420) NOT NULL COMMENT 'The textual content of a user''s post on a given service.',
  is_protected tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Whether or not this post is protected, e.g. not publicly visible.',
  source varchar(255) DEFAULT NULL COMMENT 'The client used to publish this post, e.g. if you post from the Twitter web interface, this will be "web".',
  location varchar(255) DEFAULT NULL COMMENT 'Author-level location, e.g., the author''s location as set in his or her profile. Use author-level location if post-level location is not set.',
  place varchar(255) DEFAULT NULL COMMENT 'Post-level name of a place from which a post was published, ie, Woodland Hills, Los Angeles.',
  place_id varchar(255) DEFAULT NULL COMMENT 'Post-level place ID on a given network.',
  geo varchar(255) DEFAULT NULL COMMENT 'The post''s latitude and longitude coordinates.',
  pub_date DATETIME NOT NULL COMMENT 'The UTC date/time when this post was published.',
  in_reply_to_user_id bigint(11) DEFAULT NULL COMMENT 'The ID of the user that this post is in reply to.',
  in_reply_to_post_id bigint(20) unsigned DEFAULT NULL COMMENT 'The ID of the post that this post is in reply to.',
  reply_count_cache int(11) NOT NULL DEFAULT '0' COMMENT 'The total number of replies this post received in the data store.',
  is_reply_by_friend tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Whether or not this reply was authored by a friend of the original post''s author.',
  in_retweet_of_post_id bigint(20) unsigned DEFAULT NULL COMMENT 'The ID of the post that this post is a retweet of. [Twitter-specific]',
  old_retweet_count_cache int(11) NOT NULL DEFAULT '0' COMMENT 'Manual count of old-style retweets as determined by ThinkUp. [Twitter-specific]',
  is_retweet_by_friend tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Whether or not this retweet was posted by a friend of the original post''s author. [Twitter-specific]',
  reply_retweet_distance int(11) NOT NULL DEFAULT '0' COMMENT 'The distance (in km) away from the post that this post is in reply or retweet of [Twitter-specific-ish]',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network that this post belongs to in lower-case, e.g. twitter or facebook',
  is_geo_encoded int(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not this post has been geo-encoded.',
  in_rt_of_user_id bigint(11) DEFAULT NULL COMMENT 'The ID of the user that this post is retweeting. [Twitter-specific]',
  retweet_count_cache int(11) NOT NULL DEFAULT '0' COMMENT 'Manual count of native retweets as determined by ThinkUp. [Twitter-specific]',
  retweet_count_api int(11) NOT NULL DEFAULT '0' COMMENT 'The total number of native retweets as reported by Twitter API. [Twitter-specific]',
  PRIMARY KEY (id),
  UNIQUE KEY post_network (post_id,network),
  KEY author_username (author_username),
  KEY pub_date (pub_date),
  KEY author_user_id (author_user_id),
  KEY in_retweet_of_status_id (in_retweet_of_post_id),
  KEY in_reply_to_user_id (in_reply_to_user_id),
  KEY post_id (post_id),
  KEY network (network),
  KEY is_protected (is_protected),
  KEY in_reply_to_post_id (in_reply_to_post_id),
  FULLTEXT KEY post_fulltext (post_text)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Posts by service users on a given network.';

INSERT INTO tu_posts_b14 (SELECT id, post_id, author_user_id, author_username, author_fullname, author_avatar,
author_follower_count, post_text, is_protected, source, location, place, null, geo, 
CONVERT_TZ(pub_date, '+00:00', @@global.time_zone),
in_reply_to_user_id, in_reply_to_post_id, reply_count_cache, is_reply_by_friend, in_retweet_of_post_id,
old_retweet_count_cache, is_retweet_by_friend, reply_retweet_distance, network, is_geo_encoded, in_rt_of_user_id,
retweet_count_cache, retweet_count_api FROM tu_posts);

RENAME TABLE tu_posts TO tu_posts_b13;

RENAME TABLE tu_posts_b14 TO tu_posts;

DROP TABLE tu_posts_b13;
