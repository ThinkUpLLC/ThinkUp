--
-- Add a column to the posts table for storing the permalink of the post
--
DROP TABLE IF EXISTS tu_posts_20b9;

CREATE TABLE tu_posts_20b9 LIKE tu_posts;

ALTER TABLE tu_posts_20b9 ADD permalink TEXT DEFAULT NULL COMMENT 'Link to this post on the respective service.'#rollback=2;

INSERT IGNORE INTO tu_posts_20b9 (SELECT *, null FROM tu_posts)#rollback=3;

RENAME TABLE tu_posts TO tu_posts_20b8;

RENAME TABLE tu_posts_20b9 TO tu_posts;

DROP TABLE IF EXISTS tu_posts_20b8;
