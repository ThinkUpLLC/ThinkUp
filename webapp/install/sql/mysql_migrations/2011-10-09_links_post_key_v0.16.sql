--
-- Change tu_links foreign key to post_key (tu_posts.id) from post_id, network
--

ALTER TABLE  tu_links ADD  post_key INT( 11 ) NOT NULL
COMMENT  'Internal ID of the post in which this link appeared.' AFTER  clicks;

UPDATE tu_links, tu_posts SET tu_links.post_key = tu_posts.id 
WHERE tu_links.post_id = tu_posts.post_id AND tu_links.network = tu_posts.network;

DROP TABLE IF EXISTS tu_links_b16;

CREATE TABLE tu_links_b16 LIKE tu_links;

ALTER TABLE  tu_links_b16 DROP INDEX  post_id;

ALTER TABLE  tu_links_b16 DROP INDEX  url;

ALTER TABLE  tu_links_b16 ADD INDEX  post_key (  post_key );

ALTER TABLE  tu_links_b16 ADD UNIQUE INDEX url (  url ,  post_key );

INSERT IGNORE INTO tu_links_b16 (SELECT * FROM tu_links) #rollback=5;

ALTER TABLE  tu_links_b16 DROP  post_id, DROP  network ;

RENAME TABLE tu_links to tu_links_b15;

RENAME TABLE tu_links_b16 TO tu_links;

DROP TABLE IF EXISTS tu_links_b15;
