
{include file="_header.tpl"}

{if not $instance->total_users_in_system }
<!-- //TODO this is hacky way to determine if the crawler has run and should be improved -->
<div align="center" style="border:solid red 1px;background:white;margin:10px;"><b>There's nothing to see here. Yet! First the crawler has to run to load all that tasty Twitter data.</b></div>{/if}

<div id="bd" role="main">
	<div id="yui-main">
	<div class="yui-b">
	<div role="application" class="yui-g" id="tabs">

		<ul>
			<li><a href="#tweets">Updates</a></li>
			<li><a href="#replies">Replies</a></li>
			<li><a href="#followers">Followers</a></li>
			<li><a href="#friends">Friends</a></li>

		</ul>		


<div class="section" id="tweets">
	<div role="application" class="yui-h" id="tweetssubtabs">
		<ul>
		<li><a href="#alltweetssub">Your Updates</a></li>
		<li><a href="#mostrepliedtweetssub">Most-Replied-To</a></li>
		<li><a href="#tweetsauthorhasrepliedto">Exchanges</a></li>
		</ul>		
	</div>
	<div class="section" id="alltweetssub">
		<h2>All Tweets</h2>
		
		{foreach from=$all_tweets key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.mine.tpl" t=$t}
		</div>
		{/foreach}
	</div>
	<div class="section" id="mostrepliedtweetssub">
		<h2>Most-Replied</h2>
		{foreach from=$most_replied_to_tweets key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.mine.tpl" t=$t}
		</div>
		{/foreach}
	</div>
	<div class="section" id="tweetsauthorhasrepliedto">
		<h2>Your Replies</h2>
		{foreach from=$author_replies key=tahrt item=r}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.qa.tpl" t=$t}
		</div>
		{/foreach}
	</div>

</div>

<div class="section" id="replies">
	<div role="application" class="yui-h" id="repliessubtabs">
		<ul>
		<li><a href="#orphanrepliessub">Inbox</a></li>
		<li><a href="#standalonerepliessub">Standalone</a>
		<li><a href="#allrepliessub">All</a></li>
		</ul>		
	</div>

	<div class="section" id="orphanrepliessub">
		<h2>Orphan Replies</h2>
		<form action="{$cfg->site_root_path}status/mark-parent.php">
		{foreach from=$orphan_replies key=tid item=t}
			<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.cbox.tpl" t=$t}
			</div>
		{/foreach}
		
		<input type="hidden" value="0" name="pid" />
		<input type="submit" value="mark as standalone" name="mark as standalone" />
		</form>		
	</div>

	<div class="section" id="standalonerepliessub">
		
		<h2>Standalone Replies</h2>
		
		<p>Replies you've marked "standalone", that is, not associated with any particular update.</p><br />
		
		{foreach from=$standalone_replies key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.other.tpl" t=$t}
		</div>
		{/foreach}
		
	</div>

		
	<div class="section" id="allrepliessub">
		<h2>All Replies</h2>
		{foreach from=$all_replies key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.other.tpl" t=$t}
		</div>
		{/foreach}
	</div>

</div>

<div class="section" id="followers">
	<div role="application" class="yui-h" id="followerssubtabs">
		<ul>
		<li><a href="#mostfollowedsub">Most-Followed</a></li>
		<li><a href="#leastlikelyfollowerssub">Least Likely</a></li>
		<li><a href="#earliestjoinerssub">Earliest Joiners</a></li>
		</ul>		
	</div>
	<div class="section" id="mostfollowedsub">
		<h2>Most-Followed Followers</h2>
		{foreach from=$most_followed_followers key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}
	</div>
	<div class="section" id="leastlikelyfollowerssub">
		<h2>Least-Likely Followers</h2>
		{foreach from=$least_likely_followers key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}
	</div>
	
	<div class="section" id="earliestjoinerssub">
		<h2>Earliest Joiners</h2>
		{foreach from=$earliest_joiner_followers key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}
	</div>	
	
</div>


<div class="section" id="friends">
	<div role="application" class="yui-h" id="friendssubtabs">
		<ul>
		<li><a href="#mostactivefriendsssub">Most Active</a></li>
		<li><a href="#leastactivefriendsssub">Least Active</a></li>
		<li><a href="#mostfollowedfriendssub">Most-Followed</a></li>
		</ul>		
	</div>
	<div class="section" id="mostactivefriendsssub">
		<h2>Most Active Friends</h2>
		{foreach from=$most_active_friends key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}
	</div>

	<div class="section" id="leastactivefriendsssub">
		<h2>Least Active Friends</h2>
		{foreach from=$least_active_friends key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}
	</div>



	<div class="section" id="mostfollowedfriendssub">
		<h2>Most-Followed Friends</h2>
		{foreach from=$most_followed_friends key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}
	</div>

</div>



	</div>
	</div>
	</div>


	<div role="contentinfo" id="keystats" class="yui-b">

	<h2>Key Stats</h2>

<ul>
	<li>{$owner_stats.follower_count|number_format} Followers</li>
	<li>{$owner_stats.friend_count|number_format} Friends</li>
	<li>{$owner_stats.tweet_count|number_format} Tweets<br /><small>{$owner_stats.avg_tweets_per_day} per day since {$owner_stats.joined|date_format:"%D"}</small></li>
	<li>{$instance->total_replies_in_system|number_format} Replies in System<br />{if $instance->total_replies_in_system > 0}<small>{$instance->avg_replies_per_day} per day since {$instance->earliest_reply_in_system|date_format:"%D"}</small>{/if}</li>
	<li>
</ul>
<br /><br />
<h2>System Progress</h2>
<ul>
	<li>{$percent_tweets_loaded|number_format}% of Your Tweets Loaded<br /><small>({$instance->total_tweets_in_system|number_format} of {$owner_stats.tweet_count|number_format})</small></li>
	<li>{$percent_followers_loaded|number_format}% of Your Followers Loaded<br /><small>({$total_follows_with_full_details|number_format} loaded{if $total_follows_protected>0}, {$total_follows_protected|number_format} protected{/if}{if $total_follows_with_errors>0}, {$total_follows_with_errors|number_format} suspended{/if})</small></li>
	<li>{$percent_friends_loaded|number_format}% of Your Friends Loaded<br ><small>({$total_friends|number_format} loaded{if $total_friends_protected}, {$total_friends_protected|number_format} protected{/if})</small></li>
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
