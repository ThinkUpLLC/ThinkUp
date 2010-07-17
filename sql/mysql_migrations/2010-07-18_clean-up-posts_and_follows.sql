ALTER TABLE  tt_posts DROP INDEX  status_id;

ALTER TABLE  tt_posts ADD INDEX (  post_id ) ;

ALTER TABLE  tt_posts DROP INDEX  tweets_fulltext , ADD FULLTEXT  post_fulltext ( post_text );

ALTER TABLE  tt_posts ADD INDEX  network (  network );

ALTER TABLE  tt_posts ADD  author_follower_count INT NOT NULL AFTER  author_avatar;

ALTER TABLE  tt_follows CHANGE  user_id  user_id BIGINT NOT NULL, CHANGE  follower_id follower_id BIGINT NOT NULL;