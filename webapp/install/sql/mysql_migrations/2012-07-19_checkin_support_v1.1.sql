--
-- Add icon and map_image support to places table.
--
ALTER TABLE tu_places ADD icon VARCHAR ( 255 ) COMMENT  'URL to an icon which represents the place type.';
ALTER TABLE tu_places ADD map_image VARCHAR( 255 ) COMMENT 'URL to an image of a map representing the area this place is in.';

--
-- Add marker for whether or not a post archive has been loaded during a crawl.
--
ALTER TABLE tu_instances ADD  is_archive_loaded_posts TINYINT( 1 ) NOT NULL DEFAULT  0 COMMENT  'Whether or not all the instance''s posts have been backfilled.' AFTER  earliest_reply_in_system;
