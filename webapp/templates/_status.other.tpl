
		<li class="individual-tweet{if $t->is_protected} private{/if}{if $t->in_reply_to_status_id} reply{/if}">
			<div class="person-info">
				<a href="{$cfg->site_root_path}user/?u={$t->author_username}&i={$i->twitter_username}"><img src="{$t->author_avatar}" width="48" height="48" class="avatar"></a>
				<h3 class="username"><a href="{$cfg->site_root_path}user/?u={$t->author_username}&i={$i->twitter_username}">{$t->author_username}</a></h3>
				<h4 class="follower-count">{$t->follower_count|number_format}</h4>
			</div>
				
			<div class="tweet-body">
				<p>{$t->tweet_html|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames}{if $t->in_reply_to_status_id} <a href="{$cfg->site_root_path}status/?t={$t->in_reply_to_status_id}">in reply to</a> {/if}</p>
				<h3><a href="{$cfg->site_root_path}status/?t={$t->status_id}">{$t->adj_pub_date|relative_datetime}</a><h3>
				{if $t->location}<h4 class="tweetstamp">{$t->location}</h4>{/if}
				{if $t->description}<h4 class="person-description">{$t->description}</h4>{/if}

			</div>
		
		</li>

