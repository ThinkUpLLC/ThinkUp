-- Convert posts.pub_date from TIMESTAMP to DATETIME to prevent automatic timezone conversions
ALTER TABLE tu_posts MODIFY pub_date DATETIME NOT NULL 
    COMMENT 'The date/time when this post was published.';
    
-- Convert every pub_date from localtime to UTC
UPDATE tu_posts 
SET pub_date = 
CONVERT_TZ(pub_date, '+00:00', 
    TIME_FORMAT( SEC_TO_TIME( UNIX_TIMESTAMP( NOW() ) - UNIX_TIMESTAMP( UTC_TIMESTAMP() ) ), '%H:%i')
);
