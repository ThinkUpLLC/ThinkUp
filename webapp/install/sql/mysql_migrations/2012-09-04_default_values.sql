--
-- Set default value for fields that are set to NO NULL
--
ALTER TABLE tu_links CHANGE expanded_url  expanded_url VARCHAR( 255 ) NOT NULL DEFAULT '' COMMENT  'Link URL expanded from its shortened form.';
ALTER TABLE tu_users CHANGE last_post_id  last_post_id VARCHAR( 80 ) NOT NULL DEFAULT '' COMMENT  'Network post ID of the latest post the user authored.';
ALTER TABLE tu_links CHANGE image_src  image_src VARCHAR( 255 ) NOT NULL DEFAULT '' COMMENT  'URL of a thumbnail image associated with this link.'