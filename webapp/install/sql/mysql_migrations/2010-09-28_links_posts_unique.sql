
-- Alternate quicker migration, ONLY if following 'alter table' commands do not generate an error:
-- alter table tu_links add UNIQUE KEY `url` (`url`,`post_id`, `network`);
-- alter table tu_posts add UNIQUE KEY `postnetwk` (`post_id`, `network`);


CREATE TABLE links_temp (
  id int(11) NOT NULL AUTO_INCREMENT,
  url varchar(255) NOT NULL,
  expanded_url varchar(255) NOT NULL,
  title varchar(255) NOT NULL,
  clicks int(11) NOT NULL DEFAULT '0',
  post_id bigint(20) UNSIGNED NOT NULL,
  network varchar(20) NOT NULL DEFAULT 'twitter',
  is_image tinyint(4) NOT NULL DEFAULT '0',
  error varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY `url` (`url`,`post_id`, `network`),
  KEY is_image (is_image),
  KEY post_id (post_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT into links_temp SELECT * FROM tu_links WHERE 1 GROUP BY post_id, url, network;
DROP table tu_links;
RENAME table links_temp to tu_links;

CREATE TABLE posts_temp (
  id int(11) NOT NULL AUTO_INCREMENT,
  post_id bigint(20) UNSIGNED NOT NULL,
  author_user_id bigint(11) NOT NULL,
  author_username varchar(50) NOT NULL,
  author_fullname varchar(50) NOT NULL,
  author_avatar varchar(255) NOT NULL,
  author_follower_count int(11) NOT NULL,
  post_text varchar(255) NOT NULL,
  is_protected tinyint(4) NOT NULL DEFAULT '1',
  source varchar(255) DEFAULT NULL,
  location varchar(255) DEFAULT NULL,
  place varchar(255) DEFAULT NULL,
  geo varchar(255) DEFAULT NULL,
  pub_date timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  in_reply_to_user_id bigint(11) DEFAULT NULL,
  in_reply_to_post_id bigint(20) UNSIGNED DEFAULT NULL,
  reply_count_cache int(11) NOT NULL DEFAULT '0',
  is_reply_by_friend tinyint(4) NOT NULL DEFAULT '0',
  in_retweet_of_post_id bigint(20) UNSIGNED DEFAULT NULL,
  retweet_count_cache int(11) NOT NULL DEFAULT '0',
  is_retweet_by_friend tinyint(4) NOT NULL DEFAULT '0',
  reply_retweet_distance int(11) NOT NULL DEFAULT '0',
  network varchar(20) NOT NULL DEFAULT 'twitter',
  is_geo_encoded int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY `postnetwk` (`post_id`, `network`),
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT into posts_temp SELECT * FROM tu_posts WHERE 1 GROUP BY post_id, network;
DROP table tu_posts;
RENAME table posts_temp to tu_posts;
