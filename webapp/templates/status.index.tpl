
{include file="_header.tpl"}

<div id="bd" role="main">
	<div id="yui-main">
	<div class="yui-b">
	<div role="application" class="yui-g" id="tabs">

				<ul>
					<li><a href="#tweets">Update</a></li>
					{if $likely_orphans}<li><a href="#replies">Likely Replies</a></li>{/if}
					{if $replies}<li><a href="#followers">Public/Republishable Replies</a></li>{/if}
					
				</ul>		


		<div class="section" id="tweets">

<h1>{$tweet.tweet_text}</h1>
<br /><br />


<ul>
{foreach from=$replies key=tid item=t}
<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
	{include file="_status.other.tpl" t=$t}
	<form action="{$cfg->site_root_path}status/mark-parent.php">
		<input type="hidden" value="{$t.status_id}" name="oid[]" />
		<input type="hidden" value="{$tweet.status_id}" name="fp"		
		<input type="hidden" name="u" value="{$instance->twitter_username}">
	<select name="pid">
		<option value="0">Mark as standalone</option>
		<option disabled>Disassociate from this status and set as a reply to:</option>
	{foreach from=$all_tweets key=aid item=a}
	   {if $a.status_id != $tweet.status_id}
		<option value="{$a.status_id}">&nbsp;&nbsp;{$a.tweet_html|truncate_for_select}</option>
		{/if}
	{/foreach}
	</select> <input value="Save" type="submit"></form>	
</div>
{/foreach}
</ul>

	</div>
	{if $likely_orphans}
		<div class="section" id="replies">

<h1>{$tweet.tweet_text}</h1>
<br /><br />
<p>Posted right around the time of this update:</p><br /><br />

<form action="{$cfg->site_root_path}status/mark-parent.php">
{foreach from=$likely_orphans key=tid item=t}
	<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
	{include file="_status.cbox.tpl" t=$t}
	</div>
{/foreach}

<input type="hidden" value="{$tweet.status_id}" name="pid" />
<input type="submit" value="mark as reply to update" name="mark as reply to this update" />
</form>		
</div>
{/if}
{if $replies}
<div class="section" id="followers">

<h1>{$tweet.tweet_text}</h1>
<br /><br />
<ul>
{foreach from=$replies key=tid item=t}
{if $t.is_protected}Anonymous says {else}<a href="http://twitter.com/{$t.author_username}">{$t.author_username}</a> <a href="http://twitter.com/{$t.author_username}/status/{$t.status_id}">says</a>{/if}, "{$t.tweet_html|regex_replace:"/^@[a-zA-Z0-9_]+ /":""}"<br /><br />
{/foreach}
</ul>
</div>
{/if}

</div>
</div>
</div>
<div role="contentinfo" id="keystats" class="yui-b">

<h2>Tweet Stats</h2>
<ul>
	<li>Posted at {$tweet.pub_date}</li>
	<li>{$reply_count} total replies</li>
	<li>{$private_reply_count} private</li>
	<li><a {if $instance}href="{$cfg->site_root_path}?u={$instance->twitter_username}">{else}href="#" onClick="history.go(-1)">{/if}&larr; back</a></li>
</ul>
</div>


</div>


{include file="_footer.tpl"}