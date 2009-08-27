
		<li class="individual-tweet{if $t.is_protected} private{/if}">
			<div class="person-info">
				<a href="{$cfg->site_root_path}user/?u={$f.user_name}&i={$i->twitter_username}"><img src="{$f.avatar}" width="48" height="48" class="avatar"></a>
				<h3 class="username"><a href="{$cfg->site_root_path}user/?u={$f.user_name}&i={$i->twitter_username}">{$f.user_name}</a></h3>
				<h3 class="followers">{$f.follower_count|number_format}</h3>
				<h4 class="follower-count">{$f.friend_count|number_format}</h4>
			</div>
				
			<div class="tweet-body">
				<p>{if $f.description}{$f.description}{/if}</p>
				<p> Averages {$f.avg_tweets_per_day} updates per day; {$f.tweet_count|number_format} total.
				{if $f.tweet_count > 0}<h3><a href="">Last post {$f.last_post|relative_datetime}</a><h3>{/if}
				{if $f.location}<h4 class="tweetstamp">{$f.location}</h4>{/if}
				<h4 class="person-description">Joined {$f.joined|relative_datetime} on {$f.joined|date_format:"%D"}</h4>

			</div>
		
		</li>
