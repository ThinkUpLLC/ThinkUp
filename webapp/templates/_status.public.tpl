
		<li class="individual-tweet">
			<div class="person-info">
				<a href="http://twitter.com/{$t->author_username}"><img src="{$t->author_avatar}" width="48" height="48" class="avatar"></a>
				<h3 class="username"><a href="http://twitter.com/{$t->author_username}">{$t->author_username}</a></h3>
				{if $t->author->follower_count > 0}<h4 class="follower-count">{$t->author->follower_count|number_format}</h4>{/if}

				{if $t->mention_count_cache > 0} <h4 class="reply-count"><a href="{$site_root}public.php?t={$t->status_id}">{$t->mention_count_cache} repl{if $t->mention_count_cache eq 1}y{else}ies{/if}</a></h4>{/if}  
				{if $t->retweet_count_cache > 0} <h4 class="reply-count"><a href="{$site_root}public.php?t={$t->status_id}">{$t->retweet_count_cache} retweet{if $t->retweet_count_cache eq 1}{else}s{/if}</a></h4>{/if}  
			</div>
				
			<div class="tweet-body">
				{if $t->link->is_image}<a href="{$t->link->url}"><img src="{$t->link->expanded_url}" style="float:right;background:#eee;padding:5px" /></a>{/if}
				
				
				<p>{$t->tweet_html|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames_to_twitter}<!-- {if $t->in_reply_to_status_id}[<a href="{$site_root}status/?t={$t->in_reply_to_status_id}">in reply to</a>]{/if}--></p>

				{if $t->link->expanded_url}<a href="{$t->link->expanded_url}" title="{$t->link->expanded_url}">{$t->link->title}</a>{/if}

				<h3><a href="http://twitter.com/{$t->author_username}/status/{$t->status_id}">{$t->adj_pub_date|relative_datetime}</a><h3>
				{if $t->author->location}<h4 class="tweetstamp">{$t->author->location}</h4>{/if}

			</div>
		
		</li>
		<br clear="all">
