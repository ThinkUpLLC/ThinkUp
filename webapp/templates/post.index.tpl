
{include file="_header.tpl" load="no"}

<div id="bd" role="main">
	<div id="yui-main">
	<div class="yui-b">
	<div role="application" class="yui-g" id="tabs">

				<ul>
					<li><a href="#tweets">Status</a></li>
					{if $retweets}<li><a href="#retweets">Retweets</a></li>{/if}
					{if $likely_orphans}<li><a href="#replies">Likely Replies</a></li>{/if}
					{if $replies}<li><a href="#followers">Public/Republishable Replies</a></li>{/if}
					
				</ul>		


<div class="section" id="tweets">
<h1>{$tweet->post_text}</h1>
<br /><br />
	{foreach from=$replies key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_post.other.tpl" t=$t}

		<div id="div{$t->post_id}">
		<form action="">
			<input type="submit" name="submit" class="button" id="{$t->post_id}" value="Save as Reply To:" />
		<select name="pid{$t->post_id}" id="pid{$t->post_id}">
			<option value="0">No Tweet in Particular (Mark as standalone)</option>
			<option disabled>Set as a reply to:</option>
		{foreach from=$all_tweets key=aid item=a}
			<option value="{$a->post_id}">&nbsp;&nbsp;{$a->post_text|truncate_for_select}</option>
		{/foreach}
		</select>  
		</form>
		</div>
		
		</div>
	{/foreach}
</div>

{if $retweets}
<div class="section" id="retweets">
<h1>{$tweet->post_text}</h1>
<br /><br />
<p>In addition to the original author's followers, this tweet reached {$retweet_reach|number_format} users via retweets.</p>

	{foreach from=$retweets key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_post.other.tpl" t=$t}

		<div id="div{$t->post_id}">
		<form action="">
			<input type="submit" name="submit" class="button" id="{$t->post_id}" value="Save as Reply To:" />
		<select name="pid{$t->post_id}" id="pid{$t->post_id}">
			<option value="0">No Tweet in Particular (Mark as standalone)</option>
			<option disabled>Set as a reply to:</option>
		{foreach from=$all_tweets key=aid item=a}
			<option value="{$a->post_id}">&nbsp;&nbsp;{$a->post_text|truncate_for_select}</option>
		{/foreach}
		</select>  
		</form>
		</div>
		
		</div>
	{/foreach}
</div>
{/if}

{if $likely_orphans}
<div class="section" id="replies">

<h1>{$tweet->post_text}</h1>
<br /><br />
<p>Posted right around the time of this update:</p><br /><br />


	{foreach from=$likely_orphans key=tid item=t}
		<div style="padding:5px;background-color:{cycle values="#eeeeee,#ffffff"}">
		{include file="_post.other.tpl" t=$t}

		<div id="div{$t->post_id}">
		<form action="">
			<input type="submit" name="submit" class="button" id="{$t->post_id}" value="Save as Reply To:" />  
		<select name="pid{$t->post_id}" id="pid{$t->post_id}">
			<option value="0">No Tweet in Particular (Mark as standalone)</option>
		{foreach from=$all_tweets key=aid item=a}
			<option value="{$a->post_id}" {if $a->post_id eq $tweet->post_id} selected="true" {/if}>{$a->post_text|truncate_for_select}</option>
		{/foreach}
		</select>
		</form>
		</div>
		
		</div>
	{/foreach}
</div>
{/if}


{if $replies}
<div class="section" id="followers">

<h1>{$tweet->post_text}</h1>
<br /><br />
{foreach from=$replies key=tid item=t}
{if $t->is_protected}Anonymous says {else}<a href="http://twitter.com/{$t->author_username}">{$t->author_username}</a> <a href="http://twitter.com/{$t->author_username}/post/{$t->post_id}">says</a>{/if}, "{$t->post_text|regex_replace:"/^@[a-zA-Z0-9_]+ /":""}"<br /><br />
{/foreach}
</div>
{/if}

</div>
</div>
</div>




<div role="contentinfo" id="keystats" class="yui-b">

<h2>Tweet Stats</h2>
<ul>
	<li>Posted at {$tweet->pub_date}</li>
	<li>{$reply_count} total replies</li>
	<li>{$private_reply_count} private</li>
	<li><a {if $instance}href="{$cfg->site_root_path}?u={$instance->twitter_username}">{else}href="#" onClick="history.go(-1)">{/if}&larr; back</a></li>
</ul>
</div>


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
			var u = '{/literal}{$instance->twitter_username}{literal}';
			
			var t = 'post.index.tpl';
			var ck = '{/literal}{$tweet->post_id}{literal}';
			var dataString = 'u='+ u + '&pid=' + pid + '&oid[]=' + oid + '&t=' + t + '&ck=' + ck;  
			//alert (dataString);return false;  
			    $.ajax({  
			      type: "GET",  
			      url: "{/literal}{$cfg->site_root_path}{literal}post/mark-parent.php",  
			      data: dataString,  
			      success: function() {  
				$('#div'+Id).html("<div class='success' id='message"+Id+"'></div>");  
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


{include file="_footer.tpl" stats="no"}

