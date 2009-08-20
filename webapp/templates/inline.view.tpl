<h1>{$header}</h1>

	{if ($display eq 'tweets-all' and not $all_tweets) or 
		($display eq 'tweets-mostreplies' and not $most_replied_to_tweets) or
		($display eq 'tweets-convo' and not $author_replies)}
		<div style="padding:15px">No tweets to display.</div>
	{/if}
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

	
	{if ($display eq 'mentions-all' and not $all_replies) or 
		($display eq 'mentions-orphan' and not $orphan_replies) or 
		($display eq 'mentions-standalone' and not $standalone_replies)}
		<div style="padding:15px">No mentions to display.</div>
	{/if}

	{if $orphan_replies}
		{foreach from=$orphan_replies key=tid item=t}
			<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
			{include file="_status.other.tpl" t=$t}

			<div id="div{$t.status_id}">
			<form action="">
				<input type="hidden" id="u{$t.status_id}" name="u" value="{$i->twitter_username}">
			<select name="pid{$t.status_id}" id="pid{$t.status_id}">
				<option value="0">Mark as standalone</option>
				<option disabled>Set as a reply to:</option>
			{foreach from=$all_tweets key=aid item=a}
				<option value="{$a.status_id}">&nbsp;&nbsp;{$a.tweet_html|truncate_for_select}</option>
			{/foreach}
			</select><input type="submit" name="submit" class="button" id="{$t.status_id}" value="Assign" />  
			</form>
			</div>
			
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

<script type="text/javascript">
	{literal}
	$(function() {
		//begin reply assignment actions
		$(".button").click(function() {  
		// validate and process form here  
			var element = $(this);
			var Id = element.attr("id");
			
			var oid = Id;
			var pid = $("select#pid"+Id+" option:selected").val();
			var u = '{/literal}{$i->twitter_username}{literal}';
			
			var t = 'inline.view.tpl';
			var ck = '{/literal}{$i->twitter_username}-{$smarty.session.user}-{$display}{literal}';
			var dataString = 'u='+ u + '&pid=' + pid + '&oid[]=' + oid + '&t=' + t + '&ck=' + ck;  
			//alert (dataString);return false;  
			    $.ajax({  
			      type: "GET",  
			      url: "{/literal}{$cfg->site_root_path}{literal}status/mark-parent.php",  
			      data: dataString,  
			      success: function() {  
				$('#div'+Id).html("<div id='message"+Id+"'></div>");  
				$('#message'+Id).html("<p>Saved!</p>") 
			       .hide()  
			       .fadeIn(1500, function() {  
				 $('#message'+Id);  
			       });  
			    }  
			   });  
			   return false;  
		      });  
	});	

	{/literal}
</script>