ALTER TABLE  tu_links ADD  post_key INT( 11 ) NOT NULL
COMMENT  'Internal ID of the post in which this link appeared.' AFTER  clicks;

UPDATE tu_links, tu_posts SET tu_links.post_key = tu_posts.id 
WHERE tu_links.post_id = tu_posts.post_id AND tu_links.network = tu_posts.network;

ALTER TABLE  tu_links DROP INDEX  post_id;

ALTER TABLE  tu_links ADD INDEX  post_key (  post_key );

ALTER TABLE  tu_links DROP INDEX  url ,
ADD UNIQUE  url (  url ,  post_key );

ALTER TABLE  tu_links DROP  post_id ,
DROP  network ;
