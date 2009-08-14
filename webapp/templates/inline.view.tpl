<h1>{$header}</h1>

	{if $all_tweets and $display eq 'tweets-all'}
		{foreach from=$all_tweets key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.mine.tpl" t=$t}
		</div>
		{/foreach}
	{/if}
	
	{if $most_replied_to_tweets}
		{foreach from=$most_replied_to_tweets key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.mine.tpl" t=$t}
		</div>
		{/foreach}
	{/if}


	{if $author_replies}
		{foreach from=$author_replies key=tahrt item=r}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.qa.tpl" t=$t}
		</div>
		{/foreach}
	{/if}
	
	{if $orphan_replies}
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
		{foreach from=$all_replies key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.other.tpl" t=$t}
		</div>
		{/foreach}
	{/if}

	{if $standalone_replies}
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


	{if $people}
		{foreach from=$people key=fid item=f}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_user.tpl" t=$f}
		</div>
		{/foreach}	
	{/if}
		
	<script type="text/javascript" src="{$cfg->site_root_path}cssjs/linkify.js"></script>
	<script type="text/javascript" src="{$cfg->site_root_path}cssjs/bitly.js"></script>	