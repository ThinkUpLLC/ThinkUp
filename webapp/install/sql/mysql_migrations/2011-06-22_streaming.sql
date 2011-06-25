-- Add tables and columns to store data from Twitter (and eventually other) streaming sources

CREATE TABLE IF NOT EXISTS tu_mentions (
    id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
    user_id bigint(11) NOT NULL COMMENT 'The user ID inside the respective service, e.g. Twitter or Facebook user IDs.',
    user_name varchar(255) NOT NULL COMMENT 'The user''s name inside the respective service, e.g. Twitter or Facebook user name.',
    network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network that this post belongs to in lower-case, e.g. twitter or facebook.',
    count_cache int(11) NOT NULL DEFAULT 0  COMMENT 'A count of mentions a given user on a network has in the datastore.',
    PRIMARY KEY (id),
    UNIQUE KEY user_id (network, user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Mentions captured per user. One row per user.';

CREATE TABLE IF NOT EXISTS tu_mentions_posts (
    post_id bigint(20) unsigned NOT NULL COMMENT 'Post ID on a given network.',
    author_user_id bigint(11) NOT NULL COMMENT 'Author user ID of the post which contains the mention on a given network.',
    mention_id int(11) NOT NULL COMMENT 'Internal mention ID.',
    network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network which the mentioning post and mention comes from.',
    KEY post_id (network,post_id),
    KEY author_user_id (author_user_id),
    KEY mention_id (mention_id),
    UNIQUE KEY mention_post (mention_id, post_id)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Mentions captured per post.';

CREATE TABLE IF NOT EXISTS tu_hashtags (
    id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
    hashtag varchar(255) NOT NULL COMMENT 'Hash tag, i.e., #thinkup.',
    network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network this hashtag appeared on in lower-case, e.g. twitter or facebook.',
    count_cache int(11) NOT NULL DEFAULT 0 COMMENT 'A count of times this hashtag was captured.',
    PRIMARY KEY (id),
    UNIQUE KEY network_hashtag (network, hashtag)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Hashtags captured in the datastore.';  

CREATE TABLE IF NOT EXISTS tu_hashtags_posts (
    post_id bigint(20) unsigned NOT NULL COMMENT 'Post ID on a given network.',
    hashtag_id int(11) NOT NULL  COMMENT 'Internal hashtag ID.',
    network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network this post appeared on in lower-case, e.g. twitter or facebook.',
    KEY post_id (network, post_id),
    KEY hashtag_id (hashtag_id),
    UNIQUE KEY hashtag_post (hashtag_id,post_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Hashtags captured per post.';

CREATE TABLE IF NOT EXISTS tu_places (
    id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
    place_id varchar(100) COMMENT 'Place ID on a given network.',
    place_type varchar(100) COMMENT 'Type of place.',
    name varchar(100) COMMENT 'Short name of a place.',
    full_name varchar(255) COMMENT 'Full name of a place.',
    country_code varchar(2) COMMENT 'Country code where the place is located.',
    country varchar(100) COMMENT 'Country where the place is located.',
    network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network this place appears on in lower-case, e.g. twitter or facebook.',
    longlat Point COMMENT 'Longitude/lattitude point.',
    bounding_box Polygon COMMENT 'Bounding box of place.',
    PRIMARY KEY (id),
    UNIQUE KEY place_id (place_id, network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Places on a given network.';

CREATE TABLE IF NOT EXISTS tu_places_posts (
    id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
    longlat Point NOT NULL COMMENT 'Longitude/lattitude point.',
    post_id bigint(20) unsigned NOT NULL COMMENT 'Post ID on a given network.',
    place_id varchar(100) COMMENT 'Place ID on a given network.',
    network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network this post appeared on in lower-case, e.g. twitter or facebook.',
    PRIMARY KEY (id),
    UNIQUE KEY post_id (network, post_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Place where a post was published from. One row per post.';

CREATE TABLE IF NOT EXISTS tu_stream_data (
    id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
    data text NOT NULL COMMENT 'Raw stream data.',
    network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT 'Unprocessed stream data. InnoDB for sel/del transactions.';

CREATE TABLE IF NOT EXISTS tu_stream_procs (
    process_id int(11) NOT NULL COMMENT 'Stream process ID.',
    email varchar(100) NOT NULL COMMENT 'Email address of the user running the stream process.',
    instance_id int(11) NOT NULL COMMENT 'Internal instance ID receiving stream data.',
    last_report timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Process heartbeat''s last beat time.',
    PRIMARY KEY (process_id),
    UNIQUE KEY owner_instance (email,instance_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Running stream process details.';

-- Changed this ALTER to CREATE/INSERT INTO/DROP statements below for better migration performance
-- ALTER TABLE tu_favorites ADD fav_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time post was favorited.';

CREATE TABLE tu_favorites_b14 (
  post_id bigint(20) unsigned NOT NULL COMMENT 'Post ID on a given network.',
  author_user_id bigint(11) NOT NULL COMMENT 'User ID of favorited post author on a given network.',
  fav_of_user_id bigint(11) NOT NULL COMMENT 'User ID who favorited post on a given network.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  fav_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time post was favorited.',
  UNIQUE KEY post_faving_user (post_id,fav_of_user_id,network),
  KEY post_id (post_id, network),
  KEY author_user_id (author_user_id, network),
  KEY fav_of_user_id (fav_of_user_id, network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Favorite posts.';

INSERT INTO tu_favorites_b14 (SELECT *, CURRENT_TIMESTAMP FROM tu_favorites);

RENAME TABLE tu_favorites TO tu_favorites_b13;

RENAME TABLE tu_favorites_b14 TO tu_favorites;

DROP TABLE tu_favorites_b13;

-- Changed this ALTER to CREATE/INSERT INTO/DROP statements below for better migration performance
-- ALTER TABLE tu_posts ADD place_id varchar(255) NULL DEFAULT NULL COMMENT 'Place ID on a given network.' AFTER place ;

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
  pub_date timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'The timestamp of when this post was published.',
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO tu_posts_b14 (SELECT id, post_id, author_user_id, author_username, author_fullname, author_avatar,
author_follower_count, post_text, is_protected, source, location, place, null, geo, pub_date, in_reply_to_user_id,
in_reply_to_post_id, reply_count_cache, is_reply_by_friend, in_retweet_of_post_id, old_retweet_count_cache,
is_retweet_by_friend, reply_retweet_distance, network, is_geo_encoded, in_rt_of_user_id, retweet_count_cache,
retweet_count_api FROM tu_posts);

RENAME TABLE tu_posts TO tu_posts_b13;

RENAME TABLE tu_posts_b14 TO tu_posts;

DROP TABLE tu_posts_b13;
