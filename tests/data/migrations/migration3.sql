-- test, comment with semicolon; what!?
CREATE TABLE `tu_test3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO tu_test3 (value) VALUES (1),(2),(3);
-- another comment with a semicolon; and such
UPDATE tu_test3 set value = 5 where value = 3;