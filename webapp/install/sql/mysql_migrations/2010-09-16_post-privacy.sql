ALTER TABLE  tu_posts ADD  is_protected TINYINT NOT NULL DEFAULT  '1' AFTER  post_text , ADD INDEX (  is_protected );

UPDATE tu_posts, tu_users SET tu_posts.is_protected = tu_users.is_protected WHERE (tu_posts.author_user_id = tu_users.user_id AND tu_posts.network = tu_users.network); 

ALTER TABLE tu_posts ADD INDEX  (in_reply_to_post_id);