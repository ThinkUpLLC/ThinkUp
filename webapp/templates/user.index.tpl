
{include file="_header.tpl" load="no"}

<div id="bd" role="main">
	<div id="yui-main">
	<div class="yui-b">
	<div role="application" class="yui-g" id="tabs">

				<ul>
					<li><a href="#tweets">User</a></li>
					{if $exchanges}<li><a href="#replies">Conversations ({$total_exchanges})</a></li>{/if}
					{if count($mutual_friends) > 0}<li><a href="#mutualfriends">Mutual Friends ({$total_mutual_friends})</a></li>{/if}
					{if count($sources) > 0 }<li><a href="#sources">Clients</a></li>{/if}
					
				</ul>		


		<div class="section" id="tweets">

<img src="{$profile->avatar}" width="48" height="48" style="float:left;margin-right:3px;border:solid black 1px"> <h1><a href="http://twitter.com/{$profile->user_name}">{$profile->user_name}</a></h1> <small>[{$profile->follower_count|number_format} followers, following {$profile->friend_count|number_format}] </small>   {if $profile->description}<br />{$profile->description}{/if}{if $profile->tweet_count > 0}<br /> <small>Last post {$profile->last_post|relative_datetime}{if $profile->location} from {$profile->location}{/if}</small>{/if}<br /><small>Averages {$profile->avg_tweets_per_day} updates per day; {$profile->tweet_count|number_format} since joining {$profile->joined|relative_datetime} on {$profile->joined|date_format:"%D"}</small><br clear="all" />
{if $sources}Most-used Twitter client: {$sources[0].source}{/if}
<br /><br />


<br /><br />
{foreach from=$user_statuses key=tid item=t}
<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
	{include file="_status.mine.tpl" t=$t}
</div>
{/foreach}


	</div>
	{if $exchanges}
	<div class="section" id="replies">

		{foreach from=$exchanges key=tahrt item=r}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.qa.tpl" t=$t}
		</div>
		{/foreach}

	</div>
{/if}


	{if count($mutual_friends > 0)}
	<div class="section" id="mutualfriends">

	{foreach from=$mutual_friends key=tid item=f}
	<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
	</div>
	{/foreach}

	</div>
	{/if}



	{if count($sources > 0)}
	<div class="section" id="sources">

	{foreach from=$sources key=tid item=s}
	<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{$s.total} statuses posted with {$s.source} 
	</div>
	{/foreach}

	</div>
	{/if}

</div>
</div>
</div>
<div role="contentinfo" id="keystats" class="yui-b">

<h2>User Stats</h2>
<ul>
	<li>Last updated {$profile->last_updated|relative_datetime}</li>
	<li><a {if $instance}href="{$cfg->site_root_path}?u={$instance->twitter_username}">{else}href="#" onClick="history.go(-1)">{/if}&larr; back</a></li>
</ul>
</div>


</div>


{include file="_footer.tpl" stats="no"}