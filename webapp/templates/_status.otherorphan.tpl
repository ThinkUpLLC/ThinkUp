
		<li class="individual-tweet{if $t->is_protected} private{/if}">
			<div class="person-info">
				<a href="{$cfg->site_root_path}user/?u={$t->author_username}&i={$i->twitter_username}"><img src="{$t->author_avatar}" width="48" height="48" class="avatar"></a>
				<h3 class="username"><a href="{$cfg->site_root_path}user/?u={$t->author_username}&i={$i->twitter_username}">{$t->author_username}</a></h3>
				<h4 class="follower-count">{$t->follower_count|number_format}</h4>
			</div>
				
			<div class="tweet-body">
				{if $t->link->is_image}<a href="{$t->link->url}"><img src="{$t->link->expanded_url}" style="float:right;background:#eee;padding:5px" /></a>{/if}
				
				<p>{$t->tweet_html|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames}{if $t->in_reply_to_status_id} <a href="{$cfg->site_root_path}status/?t={$t->in_reply_to_status_id}">in reply to</a> {/if}</p>

				{if $t->link->expanded_url}<a href="{$t->link->expanded_url}" title="{$t->link->expanded_url}">{$t->link->title}</a>{/if}

				<h3><a href="{$cfg->site_root_path}status/?t={$t->status_id}">{$t->adj_pub_date|relative_datetime}</a><h3>
				{if $t->author->location}<h4 class="tweetstamp">{$t->author->location}</h4>{/if}
				{if $t->author->description}<h4 class="person-description">{$t->author->description}</h4>{/if}



				<div id="div{$t->status_id}">
				<form action="" class="tweet-setparent">
					
					<select name="pid{$t->status_id}" id="pid{$t->status_id}" onselect>
						<option disabled="disabled">Is in reply to...</option>					
						<option value="0">No particular tweet (standalone)</option>
						{foreach from=$all_tweets key=aid item=a}
						<option value="{$a->status_id}">{$a->tweet_html|truncate_for_select}</option>
						{/foreach}
					</select>
					<input type="submit" name="submit" class="button" id="{$t->status_id}" value="Save" />
				</form>
				</div>
				
			<div id="">
			<form action="">

			</form>
			</div>

			</div>
		
		</li>

