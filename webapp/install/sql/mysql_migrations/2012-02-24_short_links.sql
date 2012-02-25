CREATE TABLE  tu_links_short (
    id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT  'Internal unique ID.',
    link_id INT( 11 ) NOT NULL COMMENT  'Expanded link ID in links table.',
    short_url VARCHAR( 100 ) NOT NULL COMMENT  'Shortened URL.',
    click_count INT( 11 ) NOT NULL COMMENT  'Total number of clicks as reported by shortening service.',
    first_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT  'Time of short URL capture.',
    INDEX (  link_id ),
    KEY `short_url` ( short_url )
) ENGINE = MYISAM COMMENT =  'Shortened URLs, potentially many per link.';


INSERT INTO tu_links_short (link_id, short_url) SELECT id, url FROM tu_links l WHERE l.url != l.expanded_url;
