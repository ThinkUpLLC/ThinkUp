ALTER TABLE tu_places ADD icon VARCHAR ( 255 ) COMMENT  'URL to an icon which represents the place type.';
ALTER TABLE tu_places ADD map_image VARCHAR( 255 ) COMMENT 'URL to an image of a map representing the area this place is in.';
ALTER TABLE tu_instances ADD is_post_archive_loaded INT ( 1 ) COMMENT 'Has the crawler gone back in time and fetched all the old posts?' DEFAULT 0;
