-- Add comments to the post table. 

ALTER TABLE  `tu_posts` CHANGE  `id`  `id` INT( 11 ) NOT NULL AUTO_INCREMENT COMMENT  'ThinkUp''s internal unique post id.';

ALTER TABLE  `tu_posts` CHANGE  `post_id`  `post_id` BIGINT( 20 ) UNSIGNED NOT NULL COMMENT  'The id of the post inside the respective service.';

ALTER TABLE  `tu_posts` CHANGE  `author_user_id`  `author_user_id` BIGINT( 11 ) NOT NULL COMMENT  'The user''s id inside the respective service, e.g. Twitter or Facebook user ids.';

ALTER TABLE  `tu_posts` CHANGE  `author_username`  `author_username` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  'The user''s username inside the respective service, e.g. Twitter or Facebook user name.';

ALTER TABLE  `tu_posts` CHANGE  `author_fullname`  `author_fullname` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  'The user''s real, full name on a given service, e.g. Gina Trapani.';

ALTER TABLE  `tu_posts` CHANGE  `author_avatar`  `author_avatar` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  'The URL to the user''s avatar for a given service.';

ALTER TABLE  `tu_posts` CHANGE  `author_follower_count`  `author_follower_count` INT( 11 ) NOT NULL COMMENT  'Post author''s follower count. [Twitter-specific]';

ALTER TABLE  `tu_posts` CHANGE  `post_text`  `post_text` VARCHAR( 420 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  'The textual content of a user''s post on a given service.';

ALTER TABLE  `tu_posts` CHANGE  `is_protected`  `is_protected` TINYINT( 4 ) NOT NULL DEFAULT  '1' COMMENT  'Whether or not this post is protected, e.g. not publicly visible.';

ALTER TABLE  `tu_posts` CHANGE  `source`  `source` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT  'Where the post was posted from. e.g. if you post from the Twitter web interface, this will be "web".';

ALTER TABLE  `tu_posts` CHANGE  `place`  `place` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT  'Name of a place from which a post was published, ie, Woodland Hills, Los Angeles.';

-- I don't think this field needs to be a varchar 255... Maybe varchar 32?
ALTER TABLE  `tu_posts` CHANGE  `geo`  `geo` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT  'The post''s geolocation information. Tatitude and longitude coordinates.';

ALTER TABLE  `tu_posts` CHANGE  `pub_date`  `pub_date` TIMESTAMP NOT NULL DEFAULT  '0000-00-00 00:00:00' COMMENT  'The timestamp of what this post was published.';

ALTER TABLE  `tu_posts` CHANGE  `in_reply_to_user_id`  `in_reply_to_user_id` BIGINT( 11 ) NULL DEFAULT NULL COMMENT  'The id of the user that this post is in reply to.';

ALTER TABLE  `tu_posts` CHANGE  `in_reply_to_post_id`  `in_reply_to_post_id` BIGINT( 20 ) UNSIGNED NULL DEFAULT NULL COMMENT  'The id of the post that this post is in reply to.';

ALTER TABLE  `tu_posts` CHANGE  `reply_count_cache`  `reply_count_cache` INT( 11 ) NOT NULL DEFAULT  '0' COMMENT  'The total number of replies this post received in the data store.';

ALTER TABLE  `tu_posts` CHANGE  `is_reply_by_friend`  `is_reply_by_friend` TINYINT( 4 ) NOT NULL DEFAULT  '0' COMMENT  'Whether or not this reply was authored by a friend of the original post''s author.';

ALTER TABLE  `tu_posts` CHANGE  `in_retweet_of_post_id`  `in_retweet_of_post_id` BIGINT( 20 ) UNSIGNED NULL DEFAULT NULL COMMENT  'The id of the post that this post is a retweet of. [Twitter-specific]';

ALTER TABLE  `tu_posts` CHANGE  `old_retweet_count_cache`  `old_retweet_count_cache` INT( 11 ) NOT NULL DEFAULT  '0' COMMENT  'Manual count of old-style retweets as determined by ThinkUp. [Twitter-specific]';

ALTER TABLE  `tu_posts` CHANGE  `is_retweet_by_friend`  `is_retweet_by_friend` TINYINT( 4 ) NOT NULL DEFAULT  '0' COMMENT  'Whether or not this retweet was posted by a friend of the original post''s author. [Twitter-specific]';

ALTER TABLE  `tu_posts` CHANGE  `reply_retweet_distance`  `reply_retweet_distance` INT( 11 ) NOT NULL DEFAULT  '0' COMMENT  'The distance (in km) away from the post that this post is in reply or retweet of [Twitter-specific-ish]';

ALTER TABLE  `tu_posts` CHANGE  `network`  `network` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'twitter' COMMENT  'The network that this post belongs to, e.g. twitter or facebook';

ALTER TABLE  `tu_posts` CHANGE  `is_geo_encoded`  `is_geo_encoded` INT( 1 ) NOT NULL DEFAULT  '0' COMMENT  'Whether or not this post has been geo-encoded.';

ALTER TABLE  `tu_posts` CHANGE  `in_rt_of_user_id`  `in_rt_of_user_id` BIGINT( 11 ) NULL DEFAULT NULL COMMENT  'The id of the user that this post is retweeting. [Twitter-specific]';

ALTER TABLE  `tu_posts` CHANGE  `retweet_count_cache`  `retweet_count_cache` INT( 11 ) NOT NULL DEFAULT  '0' COMMENT  'The total number of new-style retweets as reported by Twitter API. [Twitter-specific]';
