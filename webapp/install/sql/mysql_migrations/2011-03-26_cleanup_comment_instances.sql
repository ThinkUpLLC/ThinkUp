--
-- These two fields hold boolean values; values will only be either 1 or 0, so no need for int to be wider than 1 digit.
--
ALTER TABLE  tu_instances CHANGE  is_archive_loaded_replies  is_archive_loaded_replies INT( 1 ) NOT NULL DEFAULT  '0';
ALTER TABLE  tu_instances CHANGE  is_archive_loaded_follows  is_archive_loaded_follows INT( 1 ) NOT NULL DEFAULT  '0';
--
-- This field is not instance specific, it's application specific so it doesn't belong in this table.
--
ALTER TABLE  tu_instances DROP  total_users_in_system;

--
-- Documentation: Comment tu_instances table fields, noting fields which are Twitter-specific in preparation to separate them out.
--
ALTER TABLE  tu_instances COMMENT =  'Authed network user for which ThinkUp archives data.';
ALTER TABLE  tu_instances CHANGE  id  id INT( 11 ) NOT NULL AUTO_INCREMENT COMMENT  'ThinkUp''s internal unique post ID.';
ALTER TABLE  tu_instances CHANGE  network_user_id  network_user_id BIGINT( 11 ) NOT NULL COMMENT  'User ID on a given network, like a user''s Twitter ID or Facebook user ID.';
ALTER TABLE  tu_instances CHANGE  network_viewer_id  network_viewer_id BIGINT( 11 ) NOT NULL DEFAULT  '1' COMMENT  'Network user ID of the viewing user (which can affect permissions).';
ALTER TABLE  tu_instances CHANGE  network_username  network_username VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  'Username on a given network, like a user''''s Twitter username or Facebook user name.';
ALTER TABLE  tu_instances CHANGE  last_post_id  last_post_id BIGINT( 20 ) UNSIGNED NOT NULL COMMENT  'Last network post ID fetched for this instance.';
ALTER TABLE  tu_instances CHANGE  crawler_last_run  crawler_last_run TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT  'The last time the crawler completed a run for this instance.';
ALTER TABLE  tu_instances CHANGE  last_page_fetched_replies  last_page_fetched_replies INT( 11 ) NOT NULL DEFAULT  '1' COMMENT  'Last page of replies fetched for this instance [Twitter-specific].';
ALTER TABLE  tu_instances CHANGE  last_page_fetched_tweets  last_page_fetched_tweets INT( 11 ) NOT NULL DEFAULT  '1' COMMENT  'Last page of tweets fetched for this instance [Twitter-specific].';
ALTER TABLE  tu_instances CHANGE  total_posts_by_owner  total_posts_by_owner INT( 11 ) NULL DEFAULT  '0' COMMENT  'Total posts by this instance as reported by service API.';
ALTER TABLE  tu_instances CHANGE  total_posts_in_system  total_posts_in_system INT( 11 ) NULL DEFAULT  '0' COMMENT  'Total posts in datastore authored by this instance.';
ALTER TABLE  tu_instances CHANGE  total_replies_in_system  total_replies_in_system INT( 11 ) NULL DEFAULT NULL COMMENT  'Total replies in datastore authored by this instance.';
ALTER TABLE  tu_instances CHANGE  total_follows_in_system  total_follows_in_system INT( 11 ) NULL DEFAULT NULL COMMENT  'Total active follows where instance is the followed user.';
ALTER TABLE  tu_instances CHANGE  posts_per_day  posts_per_day DECIMAL( 7, 2 ) NULL DEFAULT NULL COMMENT  'Average posts per day by instance.';
ALTER TABLE  tu_instances CHANGE  posts_per_week  posts_per_week DECIMAL( 7, 2 ) NULL DEFAULT NULL COMMENT  'Average posts per week by instance.';
ALTER TABLE  tu_instances CHANGE  percentage_replies  percentage_replies DECIMAL( 4, 2 ) NULL DEFAULT NULL COMMENT  'Percent of an instance''s posts which are replies.';
ALTER TABLE  tu_instances CHANGE  percentage_links  percentage_links DECIMAL( 4, 2 ) NULL DEFAULT NULL COMMENT  'Percent of an instance''''s posts which contain links.';
ALTER TABLE  tu_instances CHANGE  earliest_post_in_system  earliest_post_in_system DATETIME NULL DEFAULT NULL COMMENT  'Date and time of the earliest post authored by the instance in the datastore.';
ALTER TABLE  tu_instances CHANGE  earliest_reply_in_system  earliest_reply_in_system DATETIME NULL DEFAULT NULL COMMENT  'Date and time of the earliest reply authored by the instance in the datastore.';
ALTER TABLE  tu_instances CHANGE  is_archive_loaded_replies  is_archive_loaded_replies INT( 1 ) NOT NULL DEFAULT  '0' COMMENT  'Whether or not all the instance''s replies have been backfilled.';
ALTER TABLE  tu_instances CHANGE  is_archive_loaded_follows  is_archive_loaded_follows INT( 1 ) NOT NULL DEFAULT  '0' COMMENT  'Whether or not all the instance''s follows have been backfilled.';
ALTER TABLE  tu_instances CHANGE  is_public  is_public INT( 1 ) NOT NULL DEFAULT  '0' COMMENT  'Whether or not instance is public in ThinkUp, that is, viewable when no ThinkUp user is logged in.';
ALTER TABLE  tu_instances CHANGE  is_active  is_active INT( 1 ) NOT NULL DEFAULT  '1' COMMENT  'Whether or not the instance user is being actively crawled (0 if it is paused).';
ALTER TABLE  tu_instances CHANGE  network  network VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'twitter' COMMENT  'The lowercase name of the source network, i.e., twitter or facebook.';
ALTER TABLE  tu_instances CHANGE  last_favorite_id  last_favorite_id BIGINT( 20 ) UNSIGNED NULL DEFAULT NULL COMMENT  'Last favorite post ID of the instance saved [Twitter-specific].';
ALTER TABLE  tu_instances CHANGE  last_unfav_page_checked  last_unfav_page_checked INT( 11 ) NULL DEFAULT  '0' COMMENT  'Last page of older favorites checked for backfilling [Twitter-specific].';
ALTER TABLE  tu_instances CHANGE  last_page_fetched_favorites  last_page_fetched_favorites INT( 11 ) NULL DEFAULT NULL COMMENT  'Last page of favorites fetched [Twitter-specific].';
ALTER TABLE  tu_instances CHANGE  owner_favs_in_system  owner_favs_in_system INT( 11 ) NULL DEFAULT  '0' COMMENT  'Total instance favorites saved in the datastore.';
ALTER TABLE  tu_instances CHANGE  favorites_profile  favorites_profile INT( 11 ) NULL DEFAULT  '0' COMMENT  'Total instance favorites as reported by the service API.';

