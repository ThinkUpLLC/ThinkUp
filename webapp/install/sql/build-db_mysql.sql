-- 
-- ThinkUp Database Creation Script
-- Auto-generated by thinkup/extras/scripts/migratedb script on 2011-06-28
--

ALTER DATABASE DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

--
-- Table structure for table tu_encoded_locations
--

CREATE TABLE tu_encoded_locations (
  id int(11) NOT NULL AUTO_INCREMENT,
  short_name varchar(255) NOT NULL,
  full_name varchar(255) NOT NULL,
  latlng varchar(50) NOT NULL,
  PRIMARY KEY (id),
  KEY short_name (short_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table tu_favorites
--

CREATE TABLE tu_favorites (
  post_id bigint(20) unsigned NOT NULL COMMENT 'Post ID on a given network.',
  author_user_id bigint(11) NOT NULL COMMENT 'User ID of favorited post author on a given network.',
  fav_of_user_id bigint(11) NOT NULL COMMENT 'User ID who favorited post on a given network.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  fav_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time post was favorited.',
  UNIQUE KEY post_faving_user (post_id,fav_of_user_id,network),
  KEY post_id (post_id,network),
  KEY author_user_id (author_user_id,network),
  KEY fav_of_user_id (fav_of_user_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Favorite posts.';

--
-- Table structure for table tu_follower_count
--

CREATE TABLE tu_follower_count (
  network_user_id bigint(11) NOT NULL,
  network varchar(20) NOT NULL,
  date date NOT NULL,
  count int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table tu_follows
--

CREATE TABLE tu_follows (
  user_id bigint(11) NOT NULL,
  follower_id bigint(11) NOT NULL,
  last_seen timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  active int(11) NOT NULL DEFAULT '1',
  network varchar(20) NOT NULL DEFAULT 'twitter',
  debug_api_call varchar(255) NOT NULL,
  UNIQUE KEY user_id (network,follower_id,user_id),
  KEY active (network,active,last_seen),
  KEY network (network,last_seen)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table tu_hashtags
--

CREATE TABLE tu_hashtags (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  hashtag varchar(255) NOT NULL COMMENT 'Hash tag, i.e., #thinkup.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network this hashtag appeared on in lower-case, e.g. twitter or facebook.',
  count_cache int(11) NOT NULL DEFAULT '0' COMMENT 'A count of times this hashtag was captured.',
  PRIMARY KEY (id),
  UNIQUE KEY network_hashtag (network,hashtag)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Hashtags captured in the datastore.';

--
-- Table structure for table tu_hashtags_posts
--

CREATE TABLE tu_hashtags_posts (
  post_id bigint(20) unsigned NOT NULL COMMENT 'Post ID on a given network.',
  hashtag_id int(11) NOT NULL COMMENT 'Internal hashtag ID.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network this post appeared on in lower-case, e.g. twitter or facebook.',
  UNIQUE KEY hashtag_post (hashtag_id,post_id),
  KEY post_id (network,post_id),
  KEY hashtag_id (hashtag_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Hashtags captured per post.';

--
-- Table structure for table tu_instances
--

CREATE TABLE tu_instances (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  network_user_id bigint(11) NOT NULL COMMENT 'User ID on a given network, like a user''s Twitter ID or Facebook user ID.',
  network_viewer_id bigint(11) NOT NULL DEFAULT '1' COMMENT 'Network user ID of the viewing user (which can affect permissions).',
  network_username varchar(255) NOT NULL COMMENT 'Username on a given network, like a user''s Twitter username or Facebook user name.',
  last_post_id bigint(20) unsigned NOT NULL COMMENT 'Last network post ID fetched for this instance.',
  crawler_last_run timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The last time the crawler completed a run for this instance.',
  total_posts_by_owner int(11) DEFAULT '0' COMMENT 'Total posts by this instance as reported by service API.',
  total_posts_in_system int(11) DEFAULT '0' COMMENT 'Total posts in datastore authored by this instance.',
  total_replies_in_system int(11) DEFAULT NULL COMMENT 'Total replies in datastore authored by this instance.',
  total_follows_in_system int(11) DEFAULT NULL COMMENT 'Total active follows where instance is the followed user.',
  posts_per_day decimal(7,2) DEFAULT NULL COMMENT 'Average posts per day by instance.',
  posts_per_week decimal(7,2) DEFAULT NULL COMMENT 'Average posts per week by instance.',
  percentage_replies decimal(4,2) DEFAULT NULL COMMENT 'Percent of an instance''s posts which are replies.',
  percentage_links decimal(4,2) DEFAULT NULL COMMENT 'Percent of an instance''s posts which contain links.',
  earliest_post_in_system datetime DEFAULT NULL COMMENT 'Date and time of the earliest post authored by the instance in the datastore.',
  earliest_reply_in_system datetime DEFAULT NULL COMMENT 'Date and time of the earliest reply authored by the instance in the datastore.',
  is_archive_loaded_replies int(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not all the instance''s replies have been backfilled.',
  is_archive_loaded_follows int(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not all the instance''s follows have been backfilled.',
  is_public int(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not instance is public in ThinkUp, that is, viewable when no ThinkUp user is logged in.',
  is_active int(1) NOT NULL DEFAULT '1' COMMENT 'Whether or not the instance user is being actively crawled (0 if it is paused).',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The lowercase name of the source network, i.e., twitter or facebook.',
  favorites_profile int(11) DEFAULT '0' COMMENT 'Total instance favorites as reported by the service API.',
  owner_favs_in_system int(11) DEFAULT '0' COMMENT 'Total instance favorites saved in the datastore.',
  PRIMARY KEY (id),
  KEY network_user_id (network_user_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Authed network user for which ThinkUp archives data.';

--
-- Table structure for table tu_instances_twitter
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
-- Table structure for table tu_invites
--

CREATE TABLE tu_invites (
  invite_code varchar(10) DEFAULT NULL,
  created_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='User invitation codes.';

--
-- Table structure for table tu_links
--

CREATE TABLE tu_links (
  id int(11) NOT NULL AUTO_INCREMENT,
  url varchar(255) NOT NULL,
  expanded_url varchar(255) NOT NULL,
  title varchar(255) NOT NULL,
  clicks int(11) NOT NULL DEFAULT '0',
  post_id bigint(20) unsigned NOT NULL,
  network varchar(20) NOT NULL DEFAULT 'twitter',
  is_image tinyint(4) NOT NULL DEFAULT '0',
  error varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY url (url,post_id,network),
  KEY is_image (is_image),
  KEY post_id (post_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table tu_mentions
--

CREATE TABLE tu_mentions (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  user_id bigint(11) NOT NULL COMMENT 'The user ID inside the respective service, e.g. Twitter or Facebook user IDs.',
  user_name varchar(255) NOT NULL COMMENT 'The user''s name inside the respective service, e.g. Twitter or Facebook user name.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network that this post belongs to in lower-case, e.g. twitter or facebook.',
  count_cache int(11) NOT NULL DEFAULT '0' COMMENT 'A count of mentions a given user on a network has in the datastore.',
  PRIMARY KEY (id),
  UNIQUE KEY user_id (network,user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Mentions captured per user. One row per user.';

--
-- Table structure for table tu_mentions_posts
--

CREATE TABLE tu_mentions_posts (
  post_id bigint(20) unsigned NOT NULL COMMENT 'Post ID on a given network.',
  author_user_id bigint(11) NOT NULL COMMENT 'Author user ID of the post which contains the mention on a given network.',
  mention_id int(11) NOT NULL COMMENT 'Internal mention ID.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network which the mentioning post and mention comes from.',
  UNIQUE KEY mention_post (mention_id,post_id),
  KEY post_id (network,post_id),
  KEY author_user_id (author_user_id),
  KEY mention_id (mention_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Mentions captured per post.';

--
-- Table structure for table tu_options
--

CREATE TABLE tu_options (
  option_id int(11) NOT NULL AUTO_INCREMENT,
  namespace varchar(50) NOT NULL,
  option_name varchar(50) NOT NULL,
  option_value varchar(255) NOT NULL,
  last_updated datetime NOT NULL,
  created datetime NOT NULL,
  PRIMARY KEY (option_id),
  KEY namespace_key (namespace),
  KEY name_key (option_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table tu_owner_instances
--

CREATE TABLE tu_owner_instances (
  id int(20) NOT NULL AUTO_INCREMENT,
  owner_id int(10) NOT NULL,
  instance_id int(10) NOT NULL,
  oauth_access_token varchar(255) DEFAULT NULL,
  oauth_access_token_secret varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table tu_owners
--

CREATE TABLE tu_owners (
  id int(20) NOT NULL AUTO_INCREMENT,
  full_name varchar(200) NOT NULL,
  pwd varchar(256) DEFAULT NULL,
  email varchar(200) NOT NULL,
  activation_code int(10) NOT NULL DEFAULT '0',
  joined date NOT NULL DEFAULT '0000-00-00',
  is_activated int(1) NOT NULL DEFAULT '0',
  is_admin int(1) NOT NULL DEFAULT '0',
  last_login date NOT NULL DEFAULT '0000-00-00',
  password_token varchar(64) DEFAULT NULL,
  failed_logins int(11) NOT NULL DEFAULT '0',
  account_status varchar(150) NOT NULL DEFAULT '',
  salt varchar(256) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table tu_places
--

CREATE TABLE tu_places (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  place_id varchar(100) DEFAULT NULL COMMENT 'Place ID on a given network.',
  place_type varchar(100) DEFAULT NULL COMMENT 'Type of place.',
  name varchar(100) DEFAULT NULL COMMENT 'Short name of a place.',
  full_name varchar(255) DEFAULT NULL COMMENT 'Full name of a place.',
  country_code varchar(2) DEFAULT NULL COMMENT 'Country code where the place is located.',
  country varchar(100) DEFAULT NULL COMMENT 'Country where the place is located.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network this place appears on in lower-case, e.g. twitter or facebook.',
  longlat point DEFAULT NULL COMMENT 'Longitude/lattitude point.',
  bounding_box polygon DEFAULT NULL COMMENT 'Bounding box of place.',
  PRIMARY KEY (id),
  UNIQUE KEY place_id (place_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Places on a given network.';

--
-- Table structure for table tu_places_posts
--

CREATE TABLE tu_places_posts (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  longlat point NOT NULL COMMENT 'Longitude/lattitude point.',
  post_id bigint(20) unsigned NOT NULL COMMENT 'Post ID on a given network.',
  place_id varchar(100) DEFAULT NULL COMMENT 'Place ID on a given network.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network this post appeared on in lower-case, e.g. twitter or facebook.',
  PRIMARY KEY (id),
  UNIQUE KEY post_id (network,post_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Place where a post was published from. One row per post.';

--
-- Table structure for table tu_plugins
--

CREATE TABLE tu_plugins (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  folder_name varchar(255) NOT NULL,
  description varchar(255) DEFAULT NULL,
  author varchar(255) DEFAULT NULL,
  homepage varchar(255) DEFAULT NULL,
  version varchar(255) DEFAULT NULL,
  is_active tinyint(4) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Table structure for table tu_post_errors
--

CREATE TABLE tu_post_errors (
  id int(11) NOT NULL AUTO_INCREMENT,
  post_id bigint(20) unsigned NOT NULL,
  network varchar(20) NOT NULL DEFAULT 'twitter',
  error_code int(11) NOT NULL,
  error_text varchar(255) NOT NULL,
  error_issued_to_user_id bigint(11) NOT NULL,
  PRIMARY KEY (id),
  KEY post_id (post_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table tu_posts
--

CREATE TABLE tu_posts (
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

--
-- Table structure for table tu_stream_data
--

CREATE TABLE tu_stream_data (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  data text NOT NULL COMMENT 'Raw stream data.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Unprocessed stream data. InnoDB for sel/del transactions.';

--
-- Table structure for table tu_stream_procs
--

CREATE TABLE tu_stream_procs (
  process_id int(11) NOT NULL COMMENT 'Stream process ID.',
  email varchar(100) NOT NULL COMMENT 'Email address of the user running the stream process.',
  instance_id int(11) NOT NULL COMMENT 'Internal instance ID receiving stream data.',
  last_report timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Process heartbeat''s last beat time.',
  PRIMARY KEY (process_id),
  UNIQUE KEY owner_instance (email,instance_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Running stream process details.';

--
-- Table structure for table tu_user_errors
--

CREATE TABLE tu_user_errors (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id bigint(11) NOT NULL,
  error_code int(11) NOT NULL,
  error_text varchar(255) NOT NULL,
  error_issued_to_user_id bigint(11) NOT NULL,
  network varchar(20) NOT NULL DEFAULT 'twitter',
  PRIMARY KEY (id),
  KEY user_id (user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table tu_users
--

CREATE TABLE tu_users (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id bigint(11) NOT NULL,
  user_name varchar(255) NOT NULL,
  full_name varchar(255) NOT NULL,
  avatar varchar(255) NOT NULL,
  location varchar(255) DEFAULT NULL,
  description text,
  url varchar(255) DEFAULT NULL,
  is_protected tinyint(1) NOT NULL,
  follower_count int(11) NOT NULL,
  friend_count int(11) NOT NULL DEFAULT '0',
  post_count int(11) NOT NULL,
  last_updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  found_in varchar(100) DEFAULT NULL,
  last_post timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  joined timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  last_post_id bigint(20) unsigned NOT NULL,
  network varchar(20) NOT NULL DEFAULT 'twitter',
  favorites_count int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY user_id (user_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- Dump completed on 2011-06-28 18:47:26

--
-- Insert DB Version
--
INSERT INTO tu_options (namespace, option_name, option_value, last_updated, created)
VALUES ('application_options', 'database_version', '0.13', NOW(), NOW()); 

--
-- Insert default plugin(s)
--

INSERT INTO tu_plugins (name , folder_name, description, author, homepage, version, is_active )
VALUES ('Twitter', 'twitter', 'Twitter support', 'Gina Trapani', 'http://thinkupapp.com', '0.01', '1'); 
