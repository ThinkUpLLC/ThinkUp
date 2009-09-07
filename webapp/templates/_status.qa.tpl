
		<li class="individual-tweet">
			<div class="person-info">
				<a href="{$cfg->site_root_path}user/?u={$r.questioner}&i={$i->twitter_username}"><img src="{$r.questioner_avatar}" width="48" height="48" class="avatar"></a>
				<h3 class="username"><a href="{$cfg->site_root_path}user/?u={$r.questioner}&i={$i->twitter_username}">{if $r.questioner eq $instance->twitter_username}You{else}{$r.questioner}{/if}</a></h3>
				<h4 class="follower-count">{$t->follower_count|number_format}</h4>
			</div>
				
			<div class="tweet-body">
				<p>{$r.question|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames}</p>
				<h3><a href="{$cfg->site_root_path}status/?t={$r.status_id}">{$r->question_adj_pub_date|relative_datetime}</a><h3>
				{if $r.location}<h4 class="tweetstamp">{$r->location}</h4>{/if}
				{if $r.description}<h4 class="person-description">{$r->description}</h4>{/if}

			</div>
		
		</li>



		<li class="individual-tweet reply">
			<div class="person-info">
				<a href="{$cfg->site_root_path}user/?u={$r.answerer}&i={$i->twitter_username}"><img src="{$r.answerer_avatar}" width="48" height="48" class="avatar"></a>
				<h3 class="username"><a href="{$cfg->site_root_path}user/?u={$r.answerer}&i={$i->twitter_username}">{if $r.answerer eq $instance->twitter_username}You{else}{$r.answerer}{/if}</a></h3>

			</div>
				
			<div class="tweet-body">
				<p>{$r.answer|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames} </p>
				<h3><a href="{$cfg->site_root_path}status/?t={$r.status_id}">{$r.answer_adj_pub_date|relative_datetime}</a><h3>
				{if $r.location}<h4 class="tweetstamp">{$r.location}</h4>{/if}

			</div>
		
		</li>