--
-- Create the videos table
--

CREATE TABLE tu_videos (
    id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Internal unique ID.',
    post_key INT( 11 )  COMMENT 'Internal ID of the post in which this video appeared.',
    description TEXT COMMENT 'The description of this video.',
    likes INT( 11 ) NOT NULL COMMENT 'Total number of likes this video has received.',
    dislikes INT ( 11 ) NOT NULL COMMENT 'Total number of dislikes this video has received.',
    views INT ( 11 ) NOT NULL COMMENT 'Total number of views on this video.',
    minutes_watched INT( 11 ) NOT NULL COMMENT 'Total number of minutes people have spent watching this video.',
    average_view_duration INT( 11 ) NOT NULL COMMENT 'Average number of seconds people spent watching this video.',
    average_view_percentage FLOAT NOT NULL COMMENT 'Average percentage of this video people watched.',
    favorites_added INT( 11 ) NOT NULL COMMENT 'Number of people who favorited this video.',
    favorites_removed INT ( 11 ) NOT NULL COMMENT 'Number of people who removed this video from their favorites.',
    shares INT ( 11 ) NOT NULL COMMENT 'Number of times people shared this video through the share button.',
    subscribers_gained INT ( 11 ) NOT NULL COMMENT 'Number of people who subscribed to this users channel on this videos page.',
    subscribers_lost INT ( 11 ) NOT NULL COMMENT 'Number of people who unsubscribed to this users channel on this videos page.',
    INDEX(post_key)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Videos which appear in posts.';
