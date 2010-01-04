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
			<li><a href="#links">Links</a></li>
		</ul>		


<div class="section" id="updates">
	
	<div id="top">

		<div id="loading_mentions"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>
	
		<ul id="menu">
			<li id="tweets-all">All</li>
			<li id="tweets-mostreplies">Most&nbsp;Replied-To</li>
			<li id="tweets-convo">Conversations</li>
		</ul>

		<div id="tweets_content"></div>

	<span class="clear"></span>
	</div>


</div>

<div class="section" id="mentions">

	<div id="top">

		<div id="loading"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>
	
		<ul id="menu">
			<li id="mentions-all">All</li>
			<li id="mentions-allreplies">Replies</li>
			<li id="mentions-orphan">Not&nbsp;Replies</li>
			<li id="mentions-standalone">Standalone</li>
		</ul>

		
		<div id="mentions_content"></div>

	<span class="clear"></span>
	</div>
</div>

<div class="section" id="followers">
	<div id="top">
		
		<div id="loading_followers"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>

		<ul id="menu">
			<li id="followers-mostfollowed">Most-Followed</li>
			<li id="followers-leastlikely">Least&nbsp;Likely</li>
			<li id="followers-earliest">Earliest</li>
			<li id="followers-former">Former</li>
			
		</ul>

		<div id="followers_content"></div>

	<span class="clear"></span>
	</div>
	
</div>


<div class="section" id="friends">
	<div id="top">

		<div id="loading_friends"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>
	
		<ul id="menu">
			<li id="friends-mostactive">Chatterboxes</li>
			<li id="friends-leastactive">Deadbeats</li>
			<li id="friends-mostfollowed">Popular</li>
			<li id="friends-former">Former</li>
			<li id="friends-notmutual">Not&nbsp;Mutual</li>			
		</ul>

		<div id="friends_content"></div>

	<span class="clear"></span>
	</div>


</div>


<div class="section" id="links">
	<div id="top">

		<div id="loading_links"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>
	
		<ul id="menu">
			<li id="links-friends">From Friends</li>
			<li id="links-favorites">From Your Favorites</li>
			<li id="links-photos">Photos</li>
		</ul>

		<div id="links_content"></div>

	<span class="clear"></span>
	</div>


</div>



	</div>
	</div>
	</div>


	<div role="contentinfo" id="keystats" class="yui-b">

	<h2>Key Stats</h2>

<ul>
	<li>Followers: <cite title="Total followers according to Twitter.com (not necessarily loaded into ThinkTank)">{$owner_stats.follower_count|number_format}</cite><br /> <small>{if $total_follows_protected>0} (<cite title="{$total_follows_protected|number_format} of {$total_follows_with_full_details|number_format} total follower profiles loaded into ThinkTank">{$percent_followers_protected}% protected</cite>)<br />{/if}{if $total_follows_with_errors>0} (<cite title="{$total_follows_with_errors|number_format} of {$total_follows_with_full_details|number_format} follower profiles loaded into ThinkTank">{$percent_followers_suspended}% suspended</cite>){/if}</small></li>
	<li>Friends: {$owner_stats.friend_count|number_format} <br /> <small>{if $total_friends_protected}({$total_friends_protected|number_format} protected)<br />{/if}{if $total_friends_with_errors>0} ({$total_friends_with_errors|number_format} suspended){/if}</small></li>
	<li>{$owner_stats.tweet_count|number_format} Tweets <small></small><br /><small>{$owner_stats.avg_tweets_per_day} per day since {$owner_stats.joined|date_format:"%D"}</small></li>
	<li>{$instance->total_replies_in_system|number_format} Replies in System<br />{if $instance->total_replies_in_system > 0}<small>{$instance->avg_replies_per_day} per day since {$instance->earliest_reply_in_system|date_format:"%D"}</small>{/if}</li>
	<li>
</ul>


	<ul id="sidemenu">
		<li>Conversations
			<ul class="submenu">
				<li>Your Tweets</li>
				<li>Mentions</li>
				<li>Messages</li>
				<li>Recent Links</li>
				<li>Favorited</li>
				<li>Retweets</li>
			</ul>
		</li>
		<li>Stats
			<ul class="submenu">
				<li>Followers Over Time</li>
				<li>Tweets per Day</li>
				<li>Replies per Day</li>
				<li>Retweets per Day</li>
				<li>Mentions per Day</li>
				<li>Noise Level by Day</li>
			</ul>		
		</li>
		<li>People
			<ul class="submenu">
				<li>Most Popular Followers</li>
				<li>Least Likely</li>
				<li>Chatterboxes</li>
				<li id="friends-leastactive">Deadbeats</li>
				<li>Repliers</li>
				<li>Messagers</li>
				<li>Messagees</li>
				<li>Favoritees</li>
			</ul>		
		</li>
		<li>Relationships
			<ul class="submenu">
				<li>Former Followers</li>
				<li>Not-Mutual</li>
			</ul>		
		</li>
	</ul>
<br /><br />

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
	





{include file="_footer.tpl"}
