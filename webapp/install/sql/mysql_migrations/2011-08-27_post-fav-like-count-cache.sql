-- Add a field to posts to track fav/like count cache
-- ALTER TABLE  tu_posts ADD  favlike_count_cache INT( 11 ) NOT NULL DEFAULT  '0' COMMENT  'The total number of favorites or likes this post received.';

CREATE TABLE tu_posts_b15 LIKE tu_posts;

ALTER TABLE tu_posts_b15 ADD favlike_count_cache INT( 11 ) NOT NULL DEFAULT  '0' COMMENT  'The total number of favorites or likes this post received.';

INSERT INTO tu_posts_b15 (SELECT *, 0 FROM tu_posts);

RENAME TABLE tu_posts TO tu_posts_b14;

RENAME TABLE tu_posts_b15 TO tu_posts;

DROP TABLE tu_posts_b14;

