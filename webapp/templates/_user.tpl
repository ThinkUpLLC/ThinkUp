{if $smarty.foreach.foo.first}
	<div class="header clearfix"> 
        <div class="grid_2 alpha">&nbsp;</div> 
        <div class="grid_3 right">name</div>        
        <div class="grid_3 right">followers</div>        
        <div class="grid_3 right">friends</div>
         
        <div class="grid_11 omega">&nbsp;</div> 
    </div> 
{/if}

<div class="individual-tweet clearfix{if $t.is_protected} private{/if}">
	<div class="grid_2 alpha">
		<a href="{$cfg->site_root_path}user/?u={$f.user_name}&i={$i->twitter_username}"><img src="{$f.avatar}" class="avatar"></a>
    </div>
    <div class="grid_3 right">
		<h3 class="username"><a href="{$cfg->site_root_path}user/?u={$f.user_name}&i={$i->twitter_username}">{$f.user_name}</a></h3>
    </div>
    <div class="grid_3 right">
		<h3 class="followers">{$f.follower_count|number_format}</h3>
    </div>
    <div class="grid_3 right">
		<h3 class="follower-count">{$f.friend_count|number_format}</h3>
	</div>
		
	<div class="grid_11 omega">
		<p>{if $f.description}{$f.description}{/if}</p>
		<p> Averages {$f.avg_tweets_per_day} updates per day; {$f.tweet_count|number_format} total.
		{if $f.tweet_count > 0}<h3><a href="">Last post {$f.last_post|relative_datetime}</a><h3>{/if}
		{if $f.location}<h4 class="tweetstamp">{$f.location}</h4>{/if}
		<h4 class="person-description">Joined {$f.joined|relative_datetime} on {$f.joined|date_format:"%D"}</h4>

	</div>

</div>
