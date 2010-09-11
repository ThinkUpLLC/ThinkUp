ALTER TABLE  tu_follows CHANGE  network  network VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'twitter';
ALTER TABLE  tu_links CHANGE  network  network VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'twitter';
ALTER TABLE  tu_post_errors CHANGE  network  network VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'twitter';
ALTER TABLE  tu_user_errors CHANGE  network  network VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'twitter';
ALTER TABLE  tu_users CHANGE  network  network VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'twitter';
ALTER TABLE  tu_posts CHANGE  network  network VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'twitter';