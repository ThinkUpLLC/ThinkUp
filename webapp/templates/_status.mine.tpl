
		<li class="individual-tweet">
			<div class="person-info">
				<img src="{$t->author_avatar}" width="48" height="48" class="avatar">
				<h3 class="username"><a href="http://twitter.com/{$t->author_username}">{$t->author_username}</a></h3>
				{if $t->mention_count_cache > 0}<h4 class="reply-count"><a href="{$cfg->site_root_path}status/?t={$t->status_id}">{$t->mention_count_cache} repl{if $t->mention_count_cache eq 1}y{else}ies{/if}</a></h4>{/if} 
			</div>
				
			<div class="tweet-body">
				<p>{$t->tweet_html|link_usernames} {if $t->in_reply_to_status_id}[<a href="{$cfg->site_root_path}status/?t={$t->in_reply_to_status_id}">in reply to</a>]{/if}</p>
				<h3><a href="http://twitter.com/{$t->author_username}/status/{$t->status_id}">{$t->adj_pub_date|relative_datetime}</a><h3>
				{if $t->location}<h4 class="tweetstamp">{$t->location}</h4>{/if}

			</div>
		
		</li>
