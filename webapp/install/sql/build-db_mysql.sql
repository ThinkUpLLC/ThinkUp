-- 
-- ThinkUp Database Creation Script
-- Auto-generated by thinkup/extras/scripts/migratedb script on 2010-12-06
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
  status_id bigint(20) unsigned NOT NULL,
  author_user_id bigint(11) NOT NULL,
  fav_of_user_id bigint(11) NOT NULL,
  network varchar(20) NOT NULL DEFAULT 'twitter',
  UNIQUE KEY status_id_2 (status_id,fav_of_user_id,network),
  KEY status_id (status_id),
  KEY author_user_id (author_user_id),
  KEY fav_of_user_id (fav_of_user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  UNIQUE KEY user_id (user_id,follower_id,network),
  KEY active (active)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table tu_instances
--

CREATE TABLE tu_instances (
  id int(11) NOT NULL AUTO_INCREMENT,
  network_user_id bigint(11) NOT NULL,
  network_viewer_id bigint(11) NOT NULL DEFAULT '1',
  network_username varchar(255) NOT NULL,
  last_post_id bigint(20) unsigned NOT NULL,
  crawler_last_run timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_page_fetched_replies int(11) NOT NULL DEFAULT '1',
  last_page_fetched_tweets int(11) NOT NULL DEFAULT '1',
  total_posts_by_owner int(11) DEFAULT '0',
  total_posts_in_system int(11) DEFAULT '0',
  total_replies_in_system int(11) DEFAULT NULL,
  total_users_in_system int(11) DEFAULT NULL,
  total_follows_in_system int(11) DEFAULT NULL,
  posts_per_day decimal(7,2) DEFAULT NULL,
  posts_per_week decimal(7,2) DEFAULT NULL,
  percentage_replies decimal(4,2) DEFAULT NULL,
  percentage_links decimal(4,2) DEFAULT NULL,
  earliest_post_in_system datetime DEFAULT NULL,
  earliest_reply_in_system datetime DEFAULT NULL,
  is_archive_loaded_replies int(11) NOT NULL DEFAULT '0',
  is_archive_loaded_follows int(11) NOT NULL DEFAULT '0',
  api_calls_to_leave_unmade_per_minute decimal(11,1) NOT NULL DEFAULT '2.0',
  is_public int(1) NOT NULL DEFAULT '0',
  is_active int(1) NOT NULL DEFAULT '1',
  network varchar(20) NOT NULL DEFAULT 'twitter',
  last_favorite_id bigint(20) unsigned DEFAULT NULL,
  last_unfav_page_checked int(11) DEFAULT '0',
  last_page_fetched_favorites int(11) DEFAULT NULL,
  favorites_profile int(11) DEFAULT '0',
  owner_favs_in_system int(11) DEFAULT '0',
  PRIMARY KEY (id),
  KEY network_user_id (network_user_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  pwd varchar(200) NOT NULL,
  email varchar(200) NOT NULL,
  activation_code int(10) NOT NULL DEFAULT '0',
  joined date NOT NULL DEFAULT '0000-00-00',
  is_activated int(1) NOT NULL DEFAULT '0',
  is_admin int(1) NOT NULL DEFAULT '0',
  last_login date NOT NULL DEFAULT '0000-00-00',
  password_token varchar(64) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table tu_plugin_options
--

CREATE TABLE tu_plugin_options (
  id int(11) NOT NULL AUTO_INCREMENT,
  plugin_id int(11) NOT NULL,
  option_name varchar(255) NOT NULL,
  option_value varchar(255) NOT NULL,
  PRIMARY KEY (id),
  KEY plugin_id (plugin_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  id int(11) NOT NULL AUTO_INCREMENT,
  post_id bigint(20) unsigned NOT NULL,
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
  in_reply_to_post_id bigint(20) unsigned DEFAULT NULL,
  reply_count_cache int(11) NOT NULL DEFAULT '0',
  is_reply_by_friend tinyint(4) NOT NULL DEFAULT '0',
  in_retweet_of_post_id bigint(20) unsigned DEFAULT NULL,
  retweet_count_cache int(11) NOT NULL DEFAULT '0',
  is_retweet_by_friend tinyint(4) NOT NULL DEFAULT '0',
  reply_retweet_distance int(11) NOT NULL DEFAULT '0',
  network varchar(20) NOT NULL DEFAULT 'twitter',
  is_geo_encoded int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY postnetwk (post_id,network),
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
  KEY user_id (user_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- Dump completed on 2010-12-06 16:11:44

--
-- Insert DB Version
--
INSERT INTO tu_options (namespace, option_name, option_value, last_updated, created)
VALUES ('application_options', 'database_version', '0.5', NOW(), NOW()); 

--
-- Insert default plugin(s)
--

INSERT INTO tu_plugins (name , folder_name, description, author, homepage, version, is_active )
VALUES ('Twitter', 'twitter', 'Twitter support', 'Gina Trapani', 'http://thinkupapp.com', '0.01', '1'); 
