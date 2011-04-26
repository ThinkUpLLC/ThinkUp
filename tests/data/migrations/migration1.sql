CREATE TABLE `tu_test1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO tu_test1 (value) VALUES (1),(2),(3);

UPDATE tu_posts, tu_users SET tu_posts.is_protected=1 WHERE tu_posts.author_user_id = tu_users.user_id AND 
tu_posts.network=tu_users.network AND tu_users.is_protected=1;