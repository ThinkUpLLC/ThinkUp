
<div class="container small center">

	<div id="ft" role="contentinfo">
        <p>
        {if $stats neq 'no'}
        Status: <strong><cite title="{$instance->total_posts_in_system|number_format} of {$owner_stats->post_count|number_format}">{$percent_tweets_loaded|number_format}%</strong> of your posts loaded: <a href="{$cfg->site_root_path}post/export.php?u={$instance->network_username}">Export &raquo;</a> | <cite title="{$total_follows_with_full_details|number_format} of {$owner_stats->follower_count|number_format}">{$percent_followers_loaded|number_format}%</cite> of your followers' profiles loaded  | <cite title="{$total_friends|number_format} of {$owner_stats->friend_count|number_format}">{$percent_friends_loaded|number_format}%</cite> of your friends' profiles loaded |
        {else}
        <a href="{$cfg->site_root_path}">Go to the public timeline.</a>
        Set up your own <a href="http://thinktankapp.com">ThinkTank</a>.
        {/if}
        It is nice to be nice.</p>
        
	</div> <!-- #ft -->
	
</div> <!-- .content -->

</body>
</html>