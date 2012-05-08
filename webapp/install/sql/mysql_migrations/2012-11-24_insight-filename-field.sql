--
-- Add filename field to insights table
--

ALTER TABLE  tu_insights ADD  filename VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT  'Name of file that generates and displays insight.';

--
-- Backfill filename field on existing insights.
--

-- First, dump all dashboard insights earlier than today
DELETE FROM tu_insights WHERE text = '' AND date < DATE(NOW());

--
-- Set insights.filename in existing rows
--
-- archivedposts
UPDATE tu_insights SET filename='archivedposts' WHERE SUBSTR( slug, 1, LENGTH(  'archived_posts_' ) ) =  'archived_posts_';

-- biggestfans
UPDATE tu_insights SET filename='biggestfans' WHERE SUBSTR( slug, 1, LENGTH(  'biggest_fans_last_30_days' ) ) =  'biggest_fans_last_30_days';
UPDATE tu_insights SET filename='biggestfans' WHERE SUBSTR( slug, 1, LENGTH(  'biggest_fans_last_7_days' ) ) =  'biggest_fans_last_7_days';

-- bigreshare
UPDATE tu_insights SET filename='bigreshare' WHERE SUBSTR( slug, 1, LENGTH(  'big_reshare_' ) ) =  'big_reshare_';

-- favoriteflashbacks
UPDATE tu_insights SET filename='favoriteflashbacks' WHERE SUBSTR( slug, 1, LENGTH(  'favorites_year_ago_flashback' ) ) =  'favorites_year_ago_flashback';

-- flashbacks
UPDATE tu_insights SET filename='flashbacks' WHERE SUBSTR( slug, 1, LENGTH(  'posts_on_this_day_flashback' ) ) =  'posts_on_this_day_flashback';

-- followercounthistory
UPDATE tu_insights SET filename='followercounthistory' WHERE SUBSTR( slug, 1, LENGTH(  'follower_count_history_by_month_milestone' ) ) =  'follower_count_history_by_month_milestone';
UPDATE tu_insights SET filename='followercounthistory' WHERE SUBSTR( slug, 1, LENGTH(  'follower_count_history_by_week_milestone' ) ) =  'follower_count_history_by_week_milestone';

-- interestingfollowers
UPDATE tu_insights SET filename='interestingfollowers' WHERE SUBSTR( slug, 1, LENGTH(  'least_likely_followers' ) ) =  'least_likely_followers';

-- listmembership
UPDATE tu_insights SET filename='listmembership' WHERE SUBSTR( slug, 1, LENGTH(  'new_group_memberships' ) ) =  'new_group_memberships';

-- map
UPDATE tu_insights SET filename='map' WHERE SUBSTR( slug, 1, LENGTH(  'geoencoded_replies' ) ) =  'geoencoded_replies';

-- retweetspike
UPDATE tu_insights SET filename='retweetspike' WHERE SUBSTR( slug, 1, LENGTH(  'avg_retweet_count_last_' ) ) =  'avg_retweet_count_last_';
UPDATE tu_insights SET filename='retweetspike' WHERE SUBSTR( slug, 1, LENGTH(  'retweet_spike_' ) ) =  'retweet_spike_';
UPDATE tu_insights SET filename='retweetspike' WHERE SUBSTR( slug, 1, LENGTH(  'retweet_high_' ) ) =  'retweet_high_';


-- wordfrequency
UPDATE tu_insights SET filename='wordfrequency' WHERE SUBSTR( slug, 1, LENGTH(  'replies_frequent_words_' ) ) =  'replies_frequent_words_';

