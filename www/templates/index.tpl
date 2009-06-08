
{include file="_header.tpl"}

<div id="bd" role="main">
	<div id="yui-main">
	<div class="yui-b">
	<div role="application" class="yui-g" id="tabs">

		<ul>
			<li><a href="#tweets">Tweets</a></li>
			<li><a href="#replies">Replies</a></li>
			<li><a href="#followers">Followers</a></li>
			<li><a href="#friends">Friends</a></li>

		</ul>		


<div class="section" id="tweets">
	<div role="application" class="yui-h" id="tweetssubtabs">
		<ul>
		<li><a href="#alltweetssub">All Tweets</a></li>
		<li><a href="#mostrepliedtweetssub">Most-Replied-To Tweets</a></li>
		</ul>		
	</div>
	<div class="section" id="alltweetssub">
		<h2>All Tweets</h2>
		<ul>
		{foreach from=$all_tweets key=tid item=t}
		<li><img src="{$t.author_avatar}" width="48" height="48" style="float:left;margin-right:3px;border:solid black 1px"> [<a href="replies/?t={$t.status_id}">{$t.reply_count_cache} replies</a>] {$t.tweet_html}<br /> {$t.adj_pub_date|relative_datetime} <br clear="all"></li>
		{/foreach}
		<li><a href="tweets?u={$twitter_username}">All Tweets&rarr;</a></li>
		</ul>
	</div>
	<div class="section" id="mostrepliedtweetssub">
		<h2>Most-Replied</h2>
		<ul>
		{foreach from=$most_replied_to_tweets key=tid item=t}
		<li>[<a href="replies/?t={$t.status_id}">{$t.reply_count_cache} replies</a>] {$t.tweet_html} <br /> {$t.adj_pub_date|relative_datetime} </li>
		{/foreach}
		<li><a href="tweets?u={$twitter_username}">More Tweets with replies&rarr;</a></li>
		</ul>
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
		{foreach from=$orphan_replies key=tid item=t}
		<form action="replies/mark-parent.php"></ul>
		<li><input type="checkbox" value="{$t.status_id}" name="oid[]"}"><img src="{$t.author_avatar}" width="48" height="48" style="float:left;margin-right:3px;border:solid black 1px"> {$t.author_username} says: <a href="replies/?t={$t.status_id}">{$t.tweet_html}</a> {if $t.in_reply_to_status_id}(<a href="replies/?t={$t.in_reply_to_status_id}">in reply to</a>){/if}<br /> {$t.adj_pub_date|relative_datetime} {if $t.description}<br />{$t.description}{/if}<br />{$t.location} <br clear="all"></li>
		{/foreach}</ul>
		
		<input type="hidden" value="0" name="pid" />
		<input type="submit" value="mark as standalone" name="mark as standalone" />
		</form>		
	</div>

	<div class="section" id="standalonerepliessub">
		
		<h2>Standalone Replies</h2>
		
		<p>Tweets marked as "standalone", that is, not associated with any tweet.</p>
		<ul>
		{foreach from=$standalone_replies key=tid item=t}
		<li><img src="{$t.author_avatar}" width="48" height="48" style="float:left;margin-right:3px;border:solid black 1px"> {$t.author_username} says: <a href="replies/?t={$t.status_id}">{$t.tweet_html}</a> {if $t.in_reply_to_status_id}(<a href="replies/?t={$t.in_reply_to_status_id}">in reply to</a>){/if}<br /> {$t.adj_pub_date|relative_datetime} {if $t.description}<br />{$t.description}{/if}<br />{$t.location} <br clear="all"></li>
		{/foreach}</ul>
		
	</div>

		
	<div class="section" id="allrepliessub">
		<h2>All Replies</h2>
		<ul>
		{foreach from=$all_replies key=tid item=t}
		<li><img src="{$t.author_avatar}" width="48" height="48" style="float:left;margin-right:3px;border:solid black 1px"> {$t.author_username} says: <a href="replies/?t={$t.status_id}">{$t.tweet_html}</a> {if $t.in_reply_to_status_id}(<a href="replies/?t={$t.in_reply_to_status_id}">in reply to</a>){/if}<br /> {$t.adj_pub_date|relative_datetime}  {if $t.description}<br />{$t.description}{/if}<br />{$t.location} <br clear="all"></li>
		{/foreach}
		<li><a href="tweets?u={$twitter_username}">All Tweets&rarr;</a></li>
		</ul>
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
		<ul>
		{foreach from=$most_followed_followers key=fid item=f}
		<li><img src="{$f.avatar}" width="48" height="48" style="float:left;margin-right:3px;border:solid black 1px"> <a href="http://twitter.com/{$f.user_name}">{$f.user_name}</a> [{$f.follower_count|number_format} followers, following {$f.friend_count|number_format}]    {if $f.description}<br />{$f.description}{/if}<br />{$f.location}<br />Last Tweet posted: {$f.last_post|relative_datetime}<br clear="all"/></li>
		{/foreach}
		<li><a href="#">More&raquo;</a></li>
		</ul>
	</div>
	<div class="section" id="leastlikelyfollowerssub">
		<h2>Least-Likely Followers</h2>
		<ul>
		{foreach from=$least_likely_followers key=fid item=f}
		<li><img src="{$f.avatar}" width="48" height="48" style="float:left;margin-right:3px;border:solid black 1px"> <a href="http://twitter.com/{$f.user_name}">{$f.user_name}</a> [{$f.follower_count|number_format} followers, following {$f.friend_count|number_format}]    {if $f.description}<br />{$f.description}{/if}<br />{$f.location}<br />Last Tweet posted: {$f.last_post|relative_datetime}<br clear="all"/></li>
		{/foreach}
		<li><a href="#">More&raquo;</a></li>
		</ul>
	</div>
	
	<div class="section" id="earliestjoinerssub">
		<h2>Earliest Joiners</h2>
		<ul>
		{foreach from=$earliest_joiner_followers key=fid item=f}
		<li><img src="{$f.avatar}" width="48" height="48" style="float:left;margin-right:3px;border:solid black 1px"> <a href="http://twitter.com/{$f.user_name}">{$f.user_name}</a> [{$f.follower_count|number_format} followers, following {$f.friend_count|number_format}]    {if $f.description}<br />{$f.description}{/if}<br />{$f.location}<br />Last Tweet posted: {$f.last_post|relative_datetime}<br clear="all"/></li>
		{/foreach}
		<li><a href="#">More&raquo;</a></li>
		</ul>
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
		<li><img src="{$f.avatar}" width="48" height="48" style="float:left;margin-right:3px;border:solid black 1px"> <a href="http://twitter.com/{$f.user_name}">{$f.user_name}</a> [{$f.follower_count|number_format} followers, following {$f.friend_count|number_format}]    {if $f.description}<br />{$f.description}<br />{/if}{$f.location}<br />Last Tweet posted: {$f.last_post|relative_datetime}<br />Average Tweets per day: {$f.avg_tweets_per_day} [{$f.tweet_count|number_format} since joining {$f.joined|relative_datetime}]<br clear="all"/></li>
		{/foreach}
	</div>

	<div class="section" id="leastactivefriendsssub">
		<h2>Least Active Friends</h2>
		{foreach from=$least_active_friends key=fid item=f}
		<li><img src="{$f.avatar}" width="48" height="48" style="float:left;margin-right:3px;border:solid black 1px"> <a href="http://twitter.com/{$f.user_name}">{$f.user_name}</a> [{$f.follower_count|number_format} followers, following {$f.friend_count|number_format}]    {if $f.description}<br />{$f.description}<br />{/if}{$f.location}<br />Last Tweet posted: {$f.last_post|relative_datetime}<br />Average Tweets per day: {$f.avg_tweets_per_day} [{$f.tweet_count|number_format} since joining {$f.joined|relative_datetime}]<br clear="all"/></li>
		{/foreach}
	</div>



	<div class="section" id="mostfollowedfriendssub">
		<h2>Most-Followed Friends</h2>
		{foreach from=$most_followed_friends key=fid item=f}
		<li><img src="{$f.avatar}" width="48" height="48" style="float:left;margin-right:3px;border:solid black 1px"> <a href="http://twitter.com/{$f.user_name}">{$f.user_name}</a> [{$f.follower_count|number_format} followers, following {$f.friend_count|number_format}]    {if $f.description}<br />{$f.description}<br />{/if}{$f.location}<br />Last Tweet posted: {$f.last_post|relative_datetime}<br />Average Tweets per day: {$f.avg_tweets_per_day} [{$f.tweet_count|number_format} since joining {$f.joined|relative_datetime}]<br clear="all"/></li>
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
	<li>{$owner_stats.tweet_count|number_format} Tweets</li>
	<li>{$instance->total_replies_in_system|number_format} Replies</li>
</ul>
<br /><br />
<h2>Progress</h2>
<ul>
	<li>{$percent_tweets_loaded|number_format}% of Your Tweets Loaded ({$instance->total_tweets_in_system|number_format} of {$owner_stats.tweet_count|number_format})</li>
	<li>{$percent_followers_loaded|number_format}% of Your Followers Loaded ({$instance->total_follows_in_system|number_format} of {$owner_stats.follower_count|number_format})</li>
	

</ul>
<br /><br />
<h2>Other Users</h2>
<ul>
	{foreach from=$instances key=tid item=i}
	<li><a href="?u={$i->owner_username}">{$i->owner_username}</a><br />updated {$i->crawler_last_run|relative_datetime}</li>
	{/foreach}	
</ul>
<br /><br />
<h2>System Stats</h2>
<ul>
	<li>{$instance->total_users_in_system|number_format} Users Total</li>
	</ul>
</div>
	



<br clear="all">

	<div id="ft" role="contentinfo">
		<p>it is nice to be nice</p>
	</div>
</div>





{include file="_footer.tpl"}
