ALTER TABLE  tu_instances_twitter DROP  last_page_fetched_tweets;

ALTER TABLE  tu_instances_twitter ADD  last_reply_id VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  'Last reply post ID to the instance saved.';

ALTER TABLE  tu_instances_twitter DROP  last_page_fetched_replies;