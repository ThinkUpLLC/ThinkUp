-- 
-- Fix incomplete data due to Twitter crawler bug that didn't set tu_posts.in_reply_to_user_id correctly
--

CREATE TABLE `tu_posts_2b8` LIKE tu_posts;

INSERT IGNORE INTO `tu_posts_2b8` (SELECT * FROM tu_posts) #rollback=1;

UPDATE `tu_posts_2b8` reply 
JOIN `tu_posts_2b8` post ON (reply.in_reply_to_post_id = post.post_id AND reply.network = post.network) 
SET reply.in_reply_to_user_id = post.author_user_id 
WHERE reply.in_reply_to_user_id is null AND reply.in_reply_to_post_id is not null AND reply.network='twitter';

RENAME TABLE tu_posts TO `tu_posts_2b7`;

RENAME TABLE `tu_posts_2b8` TO tu_posts;

DROP TABLE IF EXISTS `tu_posts_2b7`;
