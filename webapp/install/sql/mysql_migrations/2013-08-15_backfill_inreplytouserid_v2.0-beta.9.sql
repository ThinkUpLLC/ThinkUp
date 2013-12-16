-- 
-- Fix incomplete data due to Twitter crawler bug that didn't set tu_posts.in_reply_to_user_id correctly
--

CREATE TABLE `tu_posts_2b8` LIKE tu_posts;

--
-- Fix posts inserted after Jan 1 2013
--
INSERT IGNORE INTO `tu_posts_2b8` (SELECT * FROM tu_posts WHERE pub_date >= '2013-01-01') #rollback=1;

UPDATE `tu_posts_2b8` reply 
JOIN tu_posts post ON (reply.in_reply_to_post_id = post.post_id AND reply.network = post.network) 
SET reply.in_reply_to_user_id = post.author_user_id 
WHERE reply.in_reply_to_user_id is null AND reply.in_reply_to_post_id is not null AND reply.network='twitter';

DELETE FROM tu_posts  WHERE pub_date >= '2013-01-01'; 

INSERT IGNORE INTO tu_posts (SELECT * FROM `tu_posts_2b8`);

DROP TABLE IF EXISTS `tu_posts_2b8`;
