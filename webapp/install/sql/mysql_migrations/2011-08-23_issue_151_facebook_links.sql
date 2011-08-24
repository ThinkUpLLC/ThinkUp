-- Add link fields to support Facebook metadata

-- ALTER TABLE  tu_links 
-- ADD description VARCHAR( 255 ) NOT NULL COMMENT  'Link description.' AFTER  title,
-- ADD image_src VARCHAR( 255 ) NOT NULL COMMENT  'URL of a thumbnail image associated with this link.' AFTER  description,
-- ADD caption VARCHAR( 255 ) NOT NULL COMMENT  'Link or image caption.' AFTER  image_src;



CREATE TABLE tu_links_b15 (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  url varchar(255) NOT NULL COMMENT 'Link URL as it appears inside the post, ie, shortened in tweets.',
  expanded_url varchar(255) NOT NULL COMMENT 'Link URL expanded from its shortened form.',
  title varchar(255) NOT NULL COMMENT 'Link title.',
  description varchar(255) NOT NULL COMMENT 'Link description.',
  image_src varchar(255) NOT NULL COMMENT 'URL of a thumbnail image associated with this link.',
  caption varchar(255) NOT NULL COMMENT 'Link or image caption.',
  clicks int(11) NOT NULL DEFAULT '0' COMMENT 'Total known link clicks.',
  post_id bigint(20) unsigned NOT NULL COMMENT 'ID of the post which this link appeared on a given network.',
  network varchar(20) NOT NULL DEFAULT 'twitter' COMMENT 'Network of the post in which the link appeared.',
  is_image tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Whether or not the link represents an image. 1 if yes, 0 if not.',
  error varchar(255) NOT NULL DEFAULT '' COMMENT 'Details of any error expanding a link.',
  PRIMARY KEY (id),
  UNIQUE KEY url (url,post_id,network),
  KEY is_image (is_image),
  KEY post_id (post_id,network)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT 'Links which appear in posts.';

INSERT INTO tu_links_b15 (SELECT id, url, expanded_url, title, null, null, null, clicks, post_id, network, is_image, error FROM tu_links);

RENAME TABLE tu_links TO tu_links_b14;

RENAME TABLE tu_links_b15 TO tu_links;

DROP TABLE tu_links_b14;

