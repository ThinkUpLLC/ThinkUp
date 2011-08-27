-- Add a field to posts to track fav/like count cache

ALTER TABLE  tu_posts ADD  favlike_count_cache INT( 11 ) NOT NULL DEFAULT  '0' COMMENT  'The total number of favorites or likes this post received.';