
{include file="_header.tpl"}

<div id="bd" role="main">
	<div id="yui-main">
	<div class="yui-b">
	<div role="application" class="yui-g" id="tabs">

				<ul>
					<li><a href="#tweets">Replies</a></li>
					<li><a href="#replies">Likely Replies</a></li>
					<li><a href="#followers">Public/Republishable Replies</a></li>
					
				</ul>		


		<div class="section" id="tweets">

<h1>{$tweet.tweet_text}</h1>
<br /><br />


<ul>
{foreach from=$replies key=tid item=t}
<li {if $t.is_protected} style="background-color:grey;color:white"{/if}><img src="{$t.author_avatar}" width="48" height="48" style="float:left;margin-right:3px;border:solid black 1px"> <a href="http://twitter.com/{$t.author_username}">{$t.author_username}</a> ({$t.follower_count} followers) <a href="http://twitter.com/{$t.author_username}/status/{$t.status_id}">says</a>: {$t.tweet_html|replace:"@ginatrapani":""}<br /> {$t.adj_pub_date|relative_datetime} <br clear="all"></li>
{/foreach}
</ul>

	</div>
		<div class="section" id="replies">

<h1>{$tweet.tweet_text}</h1>
<br /><br />
<p>These replies have no parent tweet ID, but they were posted right around the time of the tweet.</p><br /><br />
<form action="mark-parent.php"><ul>
{foreach from=$likely_orphans key=tid item=t}
<li {if $t.is_protected} style="background-color:grey;color:white"{/if}><input type="checkbox" value="{$t.status_id}" name="oid[]"}"><img src="{$t.author_avatar}" width="48" height="48" style="float:left;margin-right:3px;border:solid black 1px"> <a href="http://twitter.com/{$t.author_username}">{$t.author_username}</a> ({$t.follower_count} followers) <a href="http://twitter.com/{$t.author_username}/status/{$t.status_id}">says</a>: {$t.tweet_html|replace:"@ginatrapani":""}<br /> {$t.adj_pub_date|relative_datetime}<br clear="all"></li>
{/foreach}
</ul>
<input type="hidden" value="{$tweet.status_id}" name="pid" />
<input type="submit" value="assign" name="assign to parent" />
</form>

</div>

<div class="section" id="followers">

<h1>{$tweet.tweet_text}</h1>
<br /><br />
<ul>
{foreach from=$replies key=tid item=t}
<li>{if $t.is_protected}Anonymous says {else}<a href="http://twitter.com/{$t.author_username}">{$t.author_username}</a> <a href="http://twitter.com/{$t.author_username}/status/{$t.status_id}">says</a>{/if}, "{$t.tweet_html|replace:"@ginatrapani ":""}"</li>
{/foreach}
</ul>


</div>


</div>
</div>
</div>
<div role="contentinfo" id="keystats" class="yui-b">

<h2>Tweet Stats</h2>
<ul>
	<li>Posted at {$tweet.pub_date}</li>
	<li>{$reply_count} total replies</li>
	<li>{$private_reply_count} private</li>

	<li><a href="{$cfg->webapp_home}">&larr; back</a></li>
</ul>
</div>


</div>


{include file="_footer.tpl"}