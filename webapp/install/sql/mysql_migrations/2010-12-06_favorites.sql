
ALTER TABLE `tu_instances` ADD `last_favorite_id` bigint(20) UNSIGNED default NULL;
ALTER TABLE `tu_instances` add `last_unfav_page_checked` int(11) default 0;
ALTER TABLE `tu_instances` ADD `last_page_fetched_favorites` int(11) default NULL;
ALTER TABLE `tu_instances` ADD `favorites_profile` int(11) default 0;
ALTER TABLE `tu_instances` ADD `owner_favs_in_system` int(11) default 0;


ALTER TABLE `tu_users` ADD `favorites_count` int(11) default NULL;

CREATE TABLE IF NOT EXISTS `tu_favorites` (
    `status_id` bigint(20) UNSIGNED NOT NULL,
    `author_user_id` bigint(11) NOT NULL,
    `fav_of_user_id` bigint(11) NOT NULL,
    `network` varchar(20) NOT NULL DEFAULT 'twitter',
    KEY `status_id` (`status_id`),
    KEY `author_user_id` (`author_user_id`),
    KEY `fav_of_user_id` (`fav_of_user_id`),
    UNIQUE KEY `status_id_2` (`status_id`,`fav_of_user_id`,`network`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;