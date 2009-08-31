
<br clear="all">

	<div id="ft" role="contentinfo">
		<p>Status: <strong><cite title="{$instance->total_tweets_in_system|number_format} of {$owner_stats.tweet_count|number_format}">{$percent_tweets_loaded|number_format}%</strong> of Your  Tweets Loaded: <a href="{$cfg->site_root_path}status/export.php?u={$instance->twitter_username}">Export &raquo;</a> &bull; <cite title="{$total_follows_with_full_details|number_format} of {$owner_stats.follower_count|number_format}">{$percent_followers_loaded|number_format}%</cite> of Your Followers' Profiles Loaded  &bull; <cite title="{$total_friends|number_format} of {$owner_stats.friend_count|number_format}">{$percent_friends_loaded|number_format}%</cite> of Your Friends' Profiles Loaded &bull; it is nice to be nice</p>

	</div>
</div>


</body>
</html>