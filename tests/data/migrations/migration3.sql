-- test, comment with semicolon; what!?
CREATE TABLE `tu_test3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO tu_test3 (value) VALUES (1),(2),(3);
-- another comment with a semicolon; and such
UPDATE tu_test3 set value = 5 where value = 3 # rollback=2;

-- test a failed migration, and sthen tart over
DROP TABLE IF EXISTS tu_test3_b16;
CREATE TABLE `tu_test3_b16` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE tu_test3_b16 ADD COLUMN `user_id` INT(11) NOT NULL;

INSERT INTO tu_test3_b16 (SELECT t.*,1000 FROM tu_test3 as t) # rollback=2;

DROP TABLE tu_test3;
RENAME TABLE tu_test3_b16 TO tu_test3;

INSERT INTO tu_test3 (value, user_id) VALUES (6,1001),(7,1002),(8,1003);
