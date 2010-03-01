
		<li class="individual-tweet">
			<div class="person-info">
				<img src="{$l->container_tweet->author_avatar}" width="48" height="48" class="avatar">
				<h3 class="username"><a href="http://twitter.com/{$l->container_tweet->author_username}">{$l->container_tweet->author_username}</a></h3>
				{if $l->container_tweet->mention_count_cache > 0}<h4 class="reply-count"><a href="{$cfg->site_root_path}status/?t={$t->status_id}">{$l->container_tweet->mention_count_cache} repl{if $l->container_tweet->mention_count_cache eq 1}y{else}ies{/if}</a></h4>{/if} 
			</div>
				
			<div class="tweet-body">
				{if $l->is_image}<a href="{$l->url}"><div class="pic"><img src="{$l->expanded_url}" /></div></a>
				{else}
				{if $l->expanded_url}<a href="{$l->expanded_url}" title="{$l->expanded_url}">{$l->title}</a>{/if}
				{/if}
				<p>{$l->container_tweet->tweet_html|link_usernames} {if $l->container_tweet->in_reply_to_status_id}[<a href="{$cfg->site_root_path}status/?t={$t->in_reply_to_status_id}">in reply to</a>]{/if}</p>
				<h3><a href="http://twitter.com/{$l->container_tweet->author_username}/status/{$l->container_tweet->status_id}">{$l->container_tweet->adj_pub_date|relative_datetime}</a><h3>
				{if $l->container_tweet->location}<h4 class="tweetstamp">{$l->container_tweet->location}</h4>{/if}

			</div>
			<br clear="all">
		</li>
