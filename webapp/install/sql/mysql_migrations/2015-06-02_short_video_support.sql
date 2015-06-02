ALTER TABLE tu_photos ADD is_short_video INT(1) NOT NULL DEFAULT '0'
COMMENT 'Whether or not this is a short video (1 if so, 0 if not).' , ADD INDEX (is_short_video) ;

ALTER TABLE tu_photos COMMENT = 'Photo and short video posts.';