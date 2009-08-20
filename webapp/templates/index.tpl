
{include file="_header.tpl"}


{if not $instance->total_users_in_system }
<!-- //TODO this is hacky way to determine if the crawler has run and should be improved -->
<div align="center" style="border:solid red 1px;background:white;margin:10px;"><b>There's nothing to see here. Yet! First the crawler has to run to load all that tasty Twitter data.</b></div>{/if}

<div id="bd" role="main">
	<div id="yui-main">
	<div class="yui-b">
	<div role="application" class="yui-g" id="tabs">

		<ul>
			<li><a href="#updates">Tweets</a></li>
			<li><a href="#mentions">Mentions</a></li>
			<li><a href="#followers">Followers</a></li>
			<li><a href="#friends">Friends</a></li>

		</ul>		


<div class="section" id="updates">
	
	<div id="top">
	<table border="0" width="100%"><tr>
	<td valign="top"  width="100%">
		<div id="tweets_content"></div>
	</td>
	<td valign="top" align="left">
		<ul id="menu">
			<li id="tweets-all">All</li>
			<li id="tweets-mostreplies">Most&nbsp;Replied-To</li>
			<li id="tweets-convo">Conversations</li>
		</ul>
		<div id="loading_mentions"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>
	</td>		
	</tr></table>
	<span class="clear"></span>
	</div>


</div>

<div class="section" id="mentions">

	<div id="top">
	<table border="0" width="100%"><tr>
	<td valign="top" width="100%">
		<div id="mentions_content"></div>
	</td>
	<td valign="top" align="left">
		<ul id="menu">
			<li id="mentions-all">All</li>
			<li id="mentions-orphan">Orphan</li>
			<li id="mentions-standalone">Standalone</li>
		</ul>
		<div id="loading"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>
	</td>		
	</tr></table>
	<span class="clear"></span>
	</div>
</div>

<div class="section" id="followers">
	<div id="top">
	<table border="0" width="100%"><tr>
	<td valign="top" width="100%">
		<div id="followers_content"></div>
	</td>
	<td valign="top" align="left">
		<ul id="menu">
			<li id="followers-mostfollowed">Most-Followed</li>
			<li id="followers-leastlikely">Least&nbsp;Likely</li>
			<li id="followers-earliest">Earliest</li>
			<li id="followers-former">Former</li>
			
		</ul>
		<div id="loading_followers"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>
	</td>		
	</tr></table>
	<span class="clear"></span>
	</div>
	
</div>


<div class="section" id="friends">
	<div id="top">
	<table border="0" width="100%"><tr>
	<td valign="top" width="100%">
		<div id="friends_content"></div>
	</td>
	<td valign="top" align="left">
		<ul id="menu">
			<li id="friends-mostactive">Most&nbsp;Active</li>
			<li id="friends-leastactive">Least&nbsp;Active</li>
			<li id="friends-mostfollowed">Most&nbsp;Followed</li>
			<li id="friends-former">Former</li>
			<li id="friends-notmutual">Not&nbsp;Mutual</li>			
		</ul>
		<div id="loading_friends"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>
	</td>		
	</tr></table>
	<span class="clear"></span>
	</div>


</div>



	</div>
	</div>
	</div>


	<div role="contentinfo" id="keystats" class="yui-b">

	<h2>Key Stats</h2>

<ul>
	<li>{$owner_stats.follower_count|number_format} Followers<br /><small>{if $total_follows_protected>0}{$total_follows_protected|number_format} protected{if $total_follows_with_errors>0},{/if}{/if}{if $total_follows_with_errors>0} {$total_follows_with_errors|number_format} suspended{/if}</small></li>
	<li>{$owner_stats.friend_count|number_format} Friends<br /><small>{if $total_friends_protected}{$total_friends_protected|number_format} protected{/if}{if $total_friends_protected and $total_friends_with_errors},{/if}{if $total_friends_with_errors>0} {$total_friends_with_errors|number_format} suspended{/if}</small></li>
	<li>{$owner_stats.tweet_count|number_format} Tweets <small><a href="{$cfg->site_root_path}status/export.php?u={$instance->twitter_username}">(export)</a></small><br /><small>{$owner_stats.avg_tweets_per_day} per day since {$owner_stats.joined|date_format:"%D"}</small></li>
	<li>{$instance->total_replies_in_system|number_format} Replies in System<br />{if $instance->total_replies_in_system > 0}<small>{$instance->avg_replies_per_day} per day since {$instance->earliest_reply_in_system|date_format:"%D"}</small>{/if}</li>
	<li>
</ul>
<br /><br />
<h2>System Progress</h2>
<ul>
	<li>{$percent_tweets_loaded|number_format}% of Your Tweets Loaded<br /><small>({$instance->total_tweets_in_system|number_format} of {$owner_stats.tweet_count|number_format})</small></li>
	<li>{$percent_followers_loaded|number_format}% of Your Followers Loaded<br /><small>({$total_follows_with_full_details|number_format} loaded)</small></li>
	<li>{$percent_friends_loaded|number_format}% of Your Friends Loaded<br ><small>({$total_friends|number_format} loaded)</small></li>
</ul>
{if sizeof($instances) > 1 }
<br /><br />
<h2>Twitter Accounts</h2>
<ul>
	{foreach from=$instances key=tid item=i}
	{if $i->twitter_user_id != $instance->twitter_user_id}
	<li><a href="?u={$i->twitter_username}">{$i->twitter_username}</a><br /><small>updated {$i->crawler_last_run|relative_datetime}</small></li>
	{/if}
	{/foreach}	
	<li><a href="{$cfg->site_root_path}account/">Add an account&rarr;</a></li>
</ul>
{/if}
</div>
	



<br clear="all">

	<div id="ft" role="contentinfo">
		<p>it is nice to be nice</p>
	</div>
</div>





{include file="_footer.tpl"}
