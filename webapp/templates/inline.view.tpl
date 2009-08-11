
	{if $all_tweets}
			<h1>All Tweets</h1>
		{foreach from=$all_tweets key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.mine.tpl" t=$t}
		</div>
		{/foreach}
	{/if}
	

	{if $most_replied_to_tweets}
	<h1>Most Replied-To Tweets</h1>
		{foreach from=$most_replied_to_tweets key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.mine.tpl" t=$t}
		</div>
		{/foreach}
	{/if}


	{if $author_replies}
	<h1>Conversations</h1>
		{foreach from=$author_replies key=tahrt item=r}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.qa.tpl" t=$t}
		</div>
		{/foreach}
	{/if}
	
	{if $orphan_replies}
	<h1>Orphan Mentions</h1>
		{foreach from=$orphan_replies key=tid item=t}
			<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.other.tpl" t=$t}
			<form action="{$cfg->site_root_path}status/mark-parent.php">
				<input type="hidden" value="{$t.status_id}" name="oid[]" />
				<input type="hidden" name="u" value="{$instance->twitter_username}">
			<select name="pid">
				<option value="0">Mark as standalone</option>
				<option disabled>Set as a reply to:</option>
			{foreach from=$all_tweets key=aid item=a}
				<option value="{$a.status_id}">&nbsp;&nbsp;{$a.tweet_html|truncate_for_select}</option>
			{/foreach}
			</select> <input value="Save" type="submit">
			</form>
			</div>
		{/foreach}
		</form>		
	{/if}


	{if $all_replies}
	<h1>All Mentions</h1>
		{foreach from=$all_replies key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.other.tpl" t=$t}
		</div>
		{/foreach}
	{/if}

	{if $standalone_replies}
	<h1>Standalone Mentions</h1>
		{foreach from=$standalone_replies key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.other.tpl" t=$t}
			
			<form action="{$cfg->site_root_path}status/mark-parent.php">
				<input type="hidden" value="{$t.status_id}" name="oid[]" />
				<input type="hidden" name="u" value="{$instance->twitter_username}">
			<input value="Move to:" type="submit"><select name="pid">
			{foreach from=$all_tweets key=aid item=a}
				<option value="{$a.status_id}">&nbsp;&nbsp;{$a.tweet_html|truncate_for_select}</option>
			{/foreach}
			</select> </form>
			
			
		</div>
		{/foreach}
		
	{/if}	
	
	{if $most_followed_followers}
		<h1>Most-Followed Followers</h1>
		{foreach from=$most_followed_followers key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}	
	{/if}
	
	{if $least_likely_followers}
		<h1>Least-Likely Followers</h1>
		{foreach from=$least_likely_followers key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}
	{/if}
	
	{if $earliest_joiner_followers}
		<h1>Earliest Joiners</h1>
		{foreach from=$earliest_joiner_followers key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}
	{/if}
	
	{if $former_followers}
		<h1>Former Followers</h1>
		{foreach from=$former_followers key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}	
	{/if}
	
	{if $most_active_friends}
		<h1>Most Active Friends</h1>
		{foreach from=$most_active_friends key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>	
		{/foreach}
	{/if}

	{if $least_active_friends}
		<h1>Least Active Friends</h1>
		{foreach from=$least_active_friends key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}	
	{/if}
	
	{if $most_followed_friends}
		<h1>Most-Followed Friends</h1>
		{foreach from=$most_followed_friends key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}
	{/if}	
	
	
	{if $former_friends}
		<h1>Former Friends</h1>
		{foreach from=$former_friends key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}
	{/if}
	

	{if $not_mutual_friends}
		<h1>Not Mutual Friends</h1>
		{foreach from=$not_mutual_friends key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}
	{/if}
		
	<script type="text/javascript" src="{$cfg->site_root_path}cssjs/linkify.js"></script>
	<script type="text/javascript" src="{$cfg->site_root_path}cssjs/bitly.js"></script>	