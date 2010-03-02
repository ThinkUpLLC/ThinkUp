{if $smarty.foreach.foo.first}
	<div class="header clearfix"> 
        <div class="grid_3 alpha">&nbsp;</div> 
        <div class="grid_3 right">name</div>        
        <div class="grid_2 right">date</div>
         
        <div class="grid_11 omega">post</div> 
    </div> 
{/if}

<div class="individual-tweet post clearfix">
    <div class="grid_2 alpha">
        <a href="{$cfg->site_root_path}user/?u={$r.questioner}&i={$i->twitter_username}"><img src="{$r.questioner_avatar}" class="avatar"></a>
    </div>
    <div class="grid_3 prefix_1 right">
		<h3 class="username"><a href="{$cfg->site_root_path}user/?u={$r.questioner}&i={$i->twitter_username}">{if $r.questioner eq $instance->twitter_username}You{else}{$r.questioner}{/if}</a></h3>
		<h4 class="follower-count">{$t->follower_count|number_format}</h4>
    </div>
    <div class="grid_2 right">
        <h3><a href="{$cfg->site_root_path}status/?t={$r.status_id}">{$r->question_adj_pub_date|relative_datetime}</a><h3>
    </div>
	<div class="grid_14 omega">
		<p>{$r.question|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames}</p>
		
		{if $r.location}<h4 class="tweetstamp">{$r->location}</h4>{/if}
		{if $r.description}<h4 class="person-description">{$r->description}</h4>{/if}
    </div>
    
</div>

<div class="individual-tweet reply clearfix">
	<div class="grid_2 prefix_1 alpha">
		<a href="{$cfg->site_root_path}user/?u={$r.answerer}&i={$i->twitter_username}"><img src="{$r.answerer_avatar}" width="48" height="48" class="avatar"></a>
    </div>
    <div class="grid_3 right">
		<h3 class="username"><a href="{$cfg->site_root_path}user/?u={$r.answerer}&i={$i->twitter_username}">{if $r.answerer eq $instance->twitter_username}You{else}{$r.answerer}{/if}</a></h3>
	</div>
	<div class="grid_2 right">
	   <h3><a href="{$cfg->site_root_path}status/?t={$r.status_id}">{$r.answer_adj_pub_date|relative_datetime}</a><h3>
	</div>
		
	<div class="grid_14 omega">
		<p>{$r.answer|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames} </p>
		
		{if $r.location}<h4 class="tweetstamp">{$r.location}</h4>{/if}
	</div>

</div>