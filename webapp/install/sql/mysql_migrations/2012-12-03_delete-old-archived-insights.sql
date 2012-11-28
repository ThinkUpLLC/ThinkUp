-- Clear out existing archived_posts_ insights and use new format
DELETE FROM tu_insights WHERE SUBSTR( slug, 1, LENGTH(  'archived_posts_' ) ) =  'archived_posts_';

-- Clear out existing retweet spike insights and regenerate using new data format
DELETE FROM tu_insights WHERE SUBSTR( slug, 1, LENGTH(  'avg_retweet_count_last_' ) ) =  'avg_retweet_count_last_';
DELETE FROM tu_insights WHERE SUBSTR( slug, 1, LENGTH(  'retweet_spike_' ) ) =  'retweet_spike_';
DELETE FROM tu_insights WHERE SUBSTR( slug, 1, LENGTH(  'retweet_high_' ) ) =  'retweet_high_';
