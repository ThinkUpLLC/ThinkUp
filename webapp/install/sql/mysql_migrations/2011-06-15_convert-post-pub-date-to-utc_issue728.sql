-- Convert every pub_date from localtime to UTC
UPDATE tu_posts 
SET pub_date = 
CONVERT_TZ(pub_date, '+00:00', 
    TIME_FORMAT( SEC_TO_TIME( UNIX_TIMESTAMP( NOW() ) - UNIX_TIMESTAMP( UTC_TIMESTAMP() ) ), '%H:%i')
)