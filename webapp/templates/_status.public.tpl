
		<li class="individual-tweet">
			<div class="person-info">
				<a href="http://twitter.com/{$t->author_username}"><img src="{$t->author_avatar}" width="48" height="48" class="avatar"></a>
				<h3 class="username"><a href="http://twitter.com/{$t->author_username}">{$t->author_username}</a></h3>
				{if $t->reply_count_cache > 0}<h4 class="reply-count"><a href="{$site_root}public.php?t={$t->status_id}">{$t->reply_count_cache} repl{if $t->reply_count_cache eq 1}y{else}ies{/if}</a></h4>{/if} 
			</div>
				
			<div class="tweet-body">
				<p>{$t->tweet_html|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames_to_twitter} {if $t->in_reply_to_status_id}[<a href="{$site_root}status/?t={$t->in_reply_to_status_id}">in reply to</a>]{/if}</p>
				<h3><a href="http://twitter.com/{$t->author_username}/status/{$t->status_id}">{$t->adj_pub_date|relative_datetime}</a><h3>
				{if $t->location}<h4 class="tweetstamp">{$t->location}</h4>{/if}

			</div>
		
		</li>
