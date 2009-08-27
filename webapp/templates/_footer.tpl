
<br clear="all">

	<div id="ft" role="contentinfo">
		<p>Status: <strong>{$instance->total_tweets_in_system|number_format} ({$percent_tweets_loaded|number_format}%)</strong> of Your {$owner_stats.tweet_count|number_format} Tweets Loaded: <a href="{$cfg->site_root_path}status/export.php?u={$instance->twitter_username}">Export &raquo;</a> &bull; {$total_follows_with_full_details|number_format} ({$percent_followers_loaded|number_format}%) of Your Followers Loaded  &bull; {$total_friends|number_format} ({$percent_friends_loaded|number_format}%) of Your Friends Loaded &bull; it is nice to be nice</p>

	</div>
</div>


</body>
</html>