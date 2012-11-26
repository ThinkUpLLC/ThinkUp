-- 
-- ThinkUp Database Creation Script
-- Auto-generated by thinkup/extras/scripts/migratedb script on 2012-11-26
--

ALTER DATABASE DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

--
-- Table structure for table tu_encoded_locations
--

CREATE TABLE tu_encoded_locations (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  short_name varchar(255) NOT NULL COMMENT 'Short name of a location, such as NYC.',
  full_name varchar(255) NOT NULL COMMENT 'Full name of location, such as New York, NY, USA.',
  latlng varchar(50) NOT NULL COMMENT 'Latitude and longitude coordinates of a place, comma-delimited.',
  PRIMARY KEY (id),
  KEY short_name (short_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Geo-encoded locations.';

--
-- Table structure for table tu_favorites
--

CREATE TABLE tu_favorites (
  post_id varchar(80) NOT NULL COMMENT 'Post ID on a given network.',
  author_user_id varchar(30) NOT NULL COMMENT 'User ID of favorited post author on a given network.',
  fav_of_user_id varchar(30) NOT NULL COMMENT 'User ID who favorited post on a given network.',
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
  network_user_id varchar(30) NOT NULL COMMENT 'User ID on a particular service with a follower count.',
  network varchar(20) NOT NULL COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  date date NOT NULL COMMENT 'Date of follower count.',
  count int(11) NOT NULL COMMENT 'Total number of followers.'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Follower counts by date and time.';

--
-- Table structure for table tu_follows
--

CREATE TABLE tu_follows (
  user_id varchar(30) NOT NULL COMMENT 'User ID on a particular service who has been followed.',
  follower_id varchar(30) NOT NULL COMMENT 'User ID on a particular service who has followed user_id.',
  last_seen timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last time this relationship was seen on the originating network.',
  first_seen timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'First time this relationship was seen on the originating network.',
  active int(11) NOT NULL DEFAULT '1' COMMENT 'Whether or not the relationship is active (1 if so, 0 if not.)',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  debug_api_call varchar(255) NOT NULL COMMENT 'Developer-only field for storing the API URL source of this data point.',
  UNIQUE KEY network_follower_user (network,follower_id,user_id),
  KEY active (network,active,last_seen),
  KEY network (network,last_seen),
  KEY user_id (user_id,network,active)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Service user follow and friend relationships.';

--
-- Table structure for table tu_group_member_count
--

CREATE TABLE tu_group_member_count (
  network varchar(20) NOT NULL COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  member_user_id varchar(30) NOT NULL COMMENT 'User ID on a particular service in a number of groups/lists.',
  date date NOT NULL COMMENT 'Date of group count.',
  count int(10) unsigned NOT NULL COMMENT 'Total number of groups the user is in.',
  KEY member_network (member_user_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Group membership counts by date and time.';

--
-- Table structure for table tu_group_members
--

CREATE TABLE tu_group_members (
  group_id varchar(50) NOT NULL COMMENT 'Group/list ID on the source network.',
  network varchar(20) NOT NULL COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  member_user_id varchar(30) NOT NULL COMMENT 'User ID of group member on a given network.',
  is_active tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Whether or not the user is active in the group (1 if so, 0 if not.)',
  first_seen datetime NOT NULL COMMENT 'First time this user was seen in the group.',
  last_seen datetime NOT NULL COMMENT 'Last time this user was seen in the group.',
  KEY group_network (group_id,network),
  KEY member_network (member_user_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Service users who are members of groups/lists.';

--
-- Table structure for table tu_groups
--

CREATE TABLE tu_groups (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  group_id varchar(50) NOT NULL COMMENT 'Group/list ID on the source network.',
  network varchar(20) NOT NULL COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  group_name varchar(50) NOT NULL COMMENT 'Name of the group or list on the source network.',
  is_active tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Whether or not the group is active (1 if so, 0 if not.)',
  first_seen datetime NOT NULL COMMENT 'First time this group was seen on the originating network.',
  last_seen datetime NOT NULL COMMENT 'Last time this group was seen on the originating network.',
  PRIMARY KEY (id),
  KEY group_network (group_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Groups/lists/circles of users.';

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
  post_id varchar(80) NOT NULL COMMENT 'Post ID on a given network.',
  hashtag_id int(11) NOT NULL COMMENT 'Internal hashtag ID.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network this post appeared on in lower-case, e.g. twitter or facebook.',
  UNIQUE KEY hashtag_post (hashtag_id,post_id),
  KEY post_id (network,post_id),
  KEY hashtag_id (hashtag_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Hashtags captured per post.';

--
-- Table structure for table tu_insight_baselines
--

CREATE TABLE tu_insight_baselines (
  date date NOT NULL COMMENT 'Date of baseline statistic.',
  instance_id int(11) NOT NULL COMMENT 'Instance ID.',
  slug varchar(100) NOT NULL COMMENT 'Unique identifier for a type of statistic.',
  value int(11) NOT NULL COMMENT 'The numeric value of this stat/total/average.',
  UNIQUE KEY unique_base (date,instance_id,slug),
  KEY date (date,instance_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Insight baseline statistics.';

--
-- Table structure for table tu_insights
--

CREATE TABLE tu_insights (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  instance_id int(11) NOT NULL COMMENT 'Instance ID.',
  slug varchar(100) NOT NULL COMMENT 'Identifier for a type of statistic.',
  prefix varchar(255) NOT NULL COMMENT 'Prefix to the text content of the alert.',
  text text NOT NULL COMMENT 'Text content of the alert.',
  related_data text COMMENT 'Serialized related insight data, such as a list of users or a post.',
  date date NOT NULL COMMENT 'Date of insight.',
  emphasis int(11) NOT NULL DEFAULT '0' COMMENT 'Level of emphasis for insight presentation.',
  filename varchar(100) DEFAULT NULL COMMENT 'Name of file that generates and displays insight.',
  PRIMARY KEY (id),
  KEY instance_id (instance_id,slug,date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Insights for a given service user.';

--
-- Table structure for table tu_instances
--

CREATE TABLE tu_instances (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  network_user_id varchar(30) NOT NULL COMMENT 'User ID on a given network, like a user''s Twitter ID or Facebook user ID.',
  network_viewer_id varchar(30) NOT NULL COMMENT 'Network user ID of the viewing user (which can affect permissions).',
  network_username varchar(255) NOT NULL COMMENT 'Username on a given network, like a user''s Twitter username or Facebook user name.',
  last_post_id varchar(80) NOT NULL COMMENT 'Last network post ID fetched for this instance.',
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
  is_archive_loaded_posts tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not all the instance''s posts have been backfilled.',
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
  last_favorite_id varchar(80) DEFAULT NULL COMMENT 'Last favorite post ID of the instance saved.',
  last_unfav_page_checked int(11) DEFAULT '0' COMMENT 'Last page of older favorites checked for backfilling.',
  last_page_fetched_favorites int(11) DEFAULT NULL COMMENT 'Last page of favorites fetched.',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Twitter-specific instance metadata.';

--
-- Table structure for table tu_invites
--

CREATE TABLE tu_invites (
  invite_code varchar(10) DEFAULT NULL COMMENT 'Invitation code.',
  created_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time the invitation was created, used to calculate expiration time.'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Individual user registration invitations.';

--
-- Table structure for table tu_links
--

CREATE TABLE tu_links (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  url varchar(255) NOT NULL COMMENT 'Link URL as it appears inside the post, ie, shortened in tweets.',
  expanded_url varchar(255) NOT NULL DEFAULT '' COMMENT 'Link URL expanded from its shortened form.',
  title varchar(255) NOT NULL COMMENT 'Link title.',
  description varchar(255) NOT NULL COMMENT 'Link description.',
  image_src varchar(255) NOT NULL DEFAULT '' COMMENT 'URL of a thumbnail image associated with this link.',
  caption varchar(255) NOT NULL COMMENT 'Link or image caption.',
  post_key int(11) NOT NULL COMMENT 'Internal ID of the post in which this link appeared.',
  error varchar(255) NOT NULL DEFAULT '' COMMENT 'Details of any error expanding a link.',
  PRIMARY KEY (id),
  UNIQUE KEY url (url,post_key),
  KEY post_key (post_key)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Links which appear in posts.';

--
-- Table structure for table tu_links_short
--

CREATE TABLE tu_links_short (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  link_id int(11) NOT NULL COMMENT 'Expanded link ID in links table.',
  short_url varchar(100) COLLATE utf8_bin NOT NULL COMMENT 'Shortened URL.',
  click_count int(11) NOT NULL COMMENT 'Total number of clicks as reported by shortening service.',
  first_seen timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time of short URL capture.',
  PRIMARY KEY (id),
  KEY link_id (link_id),
  KEY short_url (short_url)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Shortened URLs, potentially many per link.';

--
-- Table structure for table tu_mentions
--

CREATE TABLE tu_mentions (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  user_id varchar(30) NOT NULL COMMENT 'The user ID inside the respective service, e.g. Twitter or Facebook user IDs.',
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
  post_id varchar(80) NOT NULL COMMENT 'Post ID on a given network.',
  author_user_id varchar(30) NOT NULL COMMENT 'Author user ID of the post which contains the mention on a given network.',
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
  option_id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique internal ID.',
  namespace varchar(50) NOT NULL COMMENT 'Option namespace, ie, application or specific plugin.',
  option_name varchar(50) NOT NULL COMMENT 'Name of option or setting.',
  option_value varchar(255) NOT NULL COMMENT 'Value of option.',
  last_updated datetime NOT NULL COMMENT 'Last time option was updated.',
  created datetime NOT NULL COMMENT 'When option was created.',
  PRIMARY KEY (option_id),
  KEY namespace_key (namespace),
  KEY name_key (option_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Application and plugin options or settings.';

--
-- Table structure for table tu_owner_instances
--

CREATE TABLE tu_owner_instances (
  id int(20) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  owner_id int(10) NOT NULL COMMENT 'Owner ID.',
  instance_id int(10) NOT NULL COMMENT 'Instance ID.',
  oauth_access_token varchar(255) DEFAULT NULL COMMENT 'OAuth access token (optional).',
  oauth_access_token_secret varchar(255) DEFAULT NULL COMMENT 'OAuth secret access token (optional).',
  auth_error varchar(255) DEFAULT NULL COMMENT 'Last authorization error, if there was one.',
  PRIMARY KEY (id),
  UNIQUE KEY owner_instance_id (owner_id,instance_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Service user auth tokens per owner.';

--
-- Table structure for table tu_owners
--

CREATE TABLE tu_owners (
  id int(20) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  full_name varchar(200) NOT NULL COMMENT 'User full name.',
  pwd varchar(255) DEFAULT NULL COMMENT 'Hash of the owner password',
  pwd_salt varchar(255) NOT NULL COMMENT 'Salt for securely hashing the owner password',
  email varchar(200) NOT NULL COMMENT 'User email.',
  activation_code int(10) NOT NULL DEFAULT '0' COMMENT 'User activation code.',
  joined date NOT NULL DEFAULT '0000-00-00' COMMENT 'Date user registered for an account.',
  is_activated int(1) NOT NULL DEFAULT '0' COMMENT 'If user is activated, 1 for true, 0 for false.',
  is_admin int(1) NOT NULL DEFAULT '0' COMMENT 'If user is an admin, 1 for true, 0 for false.',
  last_login date NOT NULL DEFAULT '0000-00-00' COMMENT 'Last time user logged into ThinkUp.',
  password_token varchar(64) DEFAULT NULL COMMENT 'Password reset token.',
  failed_logins int(11) NOT NULL DEFAULT '0' COMMENT 'Current number of failed login attempts.',
  account_status varchar(150) NOT NULL DEFAULT '' COMMENT 'Description of account status, i.e., "Inactive due to excessive failed login attempts".',
  api_key varchar(32) NOT NULL COMMENT 'Key to authorize API calls.',
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='ThinkUp user account details.';

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
  icon varchar(255) DEFAULT NULL COMMENT 'URL to an icon which represents the place type.',
  map_image varchar(255) DEFAULT NULL COMMENT 'URL to an image of a map representing the area this place is in.',
  PRIMARY KEY (id),
  UNIQUE KEY place_id (place_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Places on a given network.';

--
-- Table structure for table tu_places_posts
--

CREATE TABLE tu_places_posts (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  longlat point NOT NULL COMMENT 'Longitude/lattitude point.',
  post_id varchar(80) NOT NULL COMMENT 'Post ID on a given network.',
  place_id varchar(100) DEFAULT NULL COMMENT 'Place ID on a given network.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network this post appeared on in lower-case, e.g. twitter or facebook.',
  PRIMARY KEY (id),
  UNIQUE KEY post_id (network,post_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Place where a post was published from. One row per post.';

--
-- Table structure for table tu_plugins
--

CREATE TABLE tu_plugins (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  name varchar(255) NOT NULL COMMENT 'Plugin display name, such as Hello ThinkUp.',
  folder_name varchar(255) NOT NULL COMMENT 'Name of folder where plugin lives.',
  description varchar(255) DEFAULT NULL COMMENT 'Plugin description.',
  author varchar(255) DEFAULT NULL COMMENT 'Plugin author.',
  homepage varchar(255) DEFAULT NULL COMMENT 'Plugin homepage URL.',
  version varchar(255) DEFAULT NULL COMMENT 'Plugin version.',
  is_active tinyint(4) NOT NULL COMMENT 'Whether or not the plugin is activated (1 if so, 0 if not.)',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Application plugins.';

--
-- Table structure for table tu_post_errors
--

CREATE TABLE tu_post_errors (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  post_id varchar(80) NOT NULL COMMENT 'Post ID on the originating service.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  error_code int(11) NOT NULL COMMENT 'Error code issues from the service.',
  error_text varchar(255) NOT NULL COMMENT 'Error text as supplied from the service.',
  error_issued_to_user_id varchar(30) NOT NULL COMMENT 'User ID service issued error to.',
  PRIMARY KEY (id),
  KEY post_id (post_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Errors in response to requests for post information.';

--
-- Table structure for table tu_posts
--

CREATE TABLE tu_posts (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID..',
  post_id varchar(80) NOT NULL COMMENT 'The ID of the post inside the respective service.',
  author_user_id varchar(30) NOT NULL COMMENT 'The user ID inside the respective service, e.g. Twitter or Facebook user IDs.',
  author_username varchar(50) NOT NULL COMMENT 'The user''s username inside the respective service, e.g. Twitter or Facebook user name.',
  author_fullname varchar(50) NOT NULL COMMENT 'The user''s real, full name on a given service, e.g. Gina Trapani.',
  author_avatar varchar(255) NOT NULL COMMENT 'The URL to the user''s avatar for a given service.',
  author_follower_count int(11) NOT NULL COMMENT 'Post author''s follower count. [Twitter-specific]',
  post_text text NOT NULL COMMENT 'The textual content of a user''s post on a given service.',
  is_protected tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Whether or not this post is protected, e.g. not publicly visible.',
  source varchar(255) DEFAULT NULL COMMENT 'The client used to publish this post, e.g. if you post from the Twitter web interface, this will be "web".',
  location varchar(255) DEFAULT NULL COMMENT 'Author-level location, e.g., the author''s location as set in his or her profile. Use author-level location if post-level location is not set.',
  place varchar(255) DEFAULT NULL COMMENT 'Post-level name of a place from which a post was published, ie, Woodland Hills, Los Angeles.',
  place_id varchar(255) DEFAULT NULL COMMENT 'Post-level place ID on a given network.',
  geo varchar(255) DEFAULT NULL COMMENT 'The post''s latitude and longitude coordinates.',
  pub_date datetime NOT NULL COMMENT 'The UTC date/time when this post was published.',
  in_reply_to_user_id varchar(30) DEFAULT NULL COMMENT 'The ID of the user that this post is in reply to.',
  in_reply_to_post_id varchar(80) DEFAULT NULL COMMENT 'The ID of the post that this post is in reply to.',
  reply_count_cache int(11) NOT NULL DEFAULT '0' COMMENT 'The total number of replies this post received in the data store.',
  is_reply_by_friend tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Whether or not this reply was authored by a friend of the original post''s author.',
  in_retweet_of_post_id varchar(80) DEFAULT NULL COMMENT 'The ID of the post that this post is a retweet of. [Twitter-specific]',
  old_retweet_count_cache int(11) NOT NULL DEFAULT '0' COMMENT 'Manual count of old-style retweets as determined by ThinkUp. [Twitter-specific]',
  is_retweet_by_friend tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Whether or not this retweet was posted by a friend of the original post''s author. [Twitter-specific]',
  reply_retweet_distance int(11) NOT NULL DEFAULT '0' COMMENT 'The distance (in km) away from the post that this post is in reply or retweet of [Twitter-specific-ish]',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'The network that this post belongs to in lower-case, e.g. twitter or facebook',
  is_geo_encoded int(1) NOT NULL DEFAULT '0' COMMENT 'Whether or not this post has been geo-encoded.',
  in_rt_of_user_id varchar(30) DEFAULT NULL COMMENT 'The ID of the user that this post is retweeting. [Twitter-specific]',
  retweet_count_cache int(11) NOT NULL DEFAULT '0' COMMENT 'Manual count of native retweets as determined by ThinkUp. [Twitter-specific]',
  retweet_count_api int(11) NOT NULL DEFAULT '0' COMMENT 'The total number of native retweets as reported by Twitter API. [Twitter-specific]',
  favlike_count_cache int(11) NOT NULL DEFAULT '0' COMMENT 'The total number of favorites or likes this post received.',
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Posts by service users on a given network.';

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
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  user_id varchar(30) NOT NULL COMMENT 'User ID on a particular service.',
  error_code int(11) NOT NULL COMMENT 'Error code issues from the service.',
  error_text varchar(255) NOT NULL COMMENT 'Error text as supplied from the service.',
  error_issued_to_user_id varchar(30) NOT NULL COMMENT 'User ID service issued error to.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  PRIMARY KEY (id),
  KEY user_id (user_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Errors in response to requests for user information.';

--
-- Table structure for table tu_users
--

CREATE TABLE tu_users (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  user_id varchar(30) NOT NULL COMMENT 'User ID on a given network.',
  user_name varchar(255) NOT NULL COMMENT 'Username on a given network, like a user''s Twitter username or Facebook user name.',
  full_name varchar(255) NOT NULL COMMENT 'Full name on a given network.',
  avatar varchar(255) NOT NULL COMMENT 'URL to user''s avatar on a given network.',
  location varchar(255) DEFAULT NULL COMMENT 'Service user location.',
  description text COMMENT 'Service user description, like a Twitter user''s profile description.',
  url varchar(255) DEFAULT NULL COMMENT 'Service user''s URL.',
  is_protected tinyint(1) NOT NULL COMMENT 'Whether or not the user is public.',
  follower_count int(11) NOT NULL COMMENT 'Total number of followers a service user has.',
  friend_count int(11) NOT NULL DEFAULT '0' COMMENT 'Total number of friends a service user has.',
  post_count int(11) NOT NULL DEFAULT '0' COMMENT 'Total number of posts the user has authored.',
  last_updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Last time this user''s record was updated.',
  found_in varchar(100) DEFAULT NULL COMMENT 'What data source or API call the last update originated from (for developer debugging).',
  last_post timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'The time of the latest post the user authored.',
  joined timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When the user joined the network.',
  last_post_id varchar(80) NOT NULL DEFAULT '' COMMENT 'Network post ID of the latest post the user authored.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Originating network in lower case, i.e., twitter or facebook.',
  favorites_count int(11) DEFAULT NULL COMMENT 'Total number of posts the user has favorited.',
  PRIMARY KEY (id),
  UNIQUE KEY user_id (user_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Service user details.';


-- Dump completed on 2012-11-26 21:52:47

--
-- Insert DB Version
--
INSERT INTO tu_options (namespace, option_name, option_value, last_updated, created)
VALUES ('application_options', 'database_version', '1.1.1', NOW(), NOW()); 

--
-- Insert default plugin(s)
--

INSERT INTO tu_plugins (name , folder_name, description, author, homepage, version, is_active )
VALUES ('Twitter', 'twitter', 'Twitter support', 'Gina Trapani', 'http://thinkupapp.com', '0.01', '1');

INSERT INTO tu_plugins (name , folder_name, description, author, homepage, version, is_active )
VALUES ('Facebook', 'facebook', 'Facebook support', 'Gina Trapani', 'http://thinkupapp.com', '0.01', '1');

INSERT INTO tu_plugins (name , folder_name, description, author, homepage, version, is_active )
VALUES ('Foursquare', 'foursquare', 'Foursquare support', 'Aaron Kalair', 'http://thinkupapp.com', '0.01', '1');

INSERT INTO tu_plugins (name , folder_name, description, author, homepage, version, is_active )
VALUES ('Google+', 'googleplus', 'Google+ support', 'Gina Trapani', 'http://thinkupapp.com', '0.01', '1');

INSERT INTO tu_plugins (name , folder_name, description, author, homepage, version, is_active )
VALUES ('Expand URLs', 'expandurls', 'Expand shortened links.', 'Gina Trapani', 'http://thinkupapp.com', '0.01', '1'); 
