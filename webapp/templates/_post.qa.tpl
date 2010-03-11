{if $smarty.foreach.foo.first}
	<div class="header clearfix">
	    <div class="grid_2 alpha">&nbsp;</div> 
        <div class="grid_3 right">name</div>        
        <div class="grid_3 right">followers</div>
        <div class="grid_3 right">date</div>
        <div class="grid_11 omega">post</div> 
    </div> 
{/if}

<div class="individual-tweet post clearfix">
    <div class="grid_2 alpha">
        <a href="{$cfg->site_root_path}user/?u={$r.questioner_username}&i={$i->twitter_username}"><img src="{$r.questioner_avatar}" class="avatar"></a>
    </div>
    <div class="grid_3 alpha small">
		<a href="{$cfg->site_root_path}user/?u={$r.questioner_username}&i={$i->twitter_username}">{if $r.questioner_username eq $instance->twitter_username}You{else}{$r.questioner_username}{/if}</a>
    </div>
    <div class="grid_3 right small">
		{$r.questioner_follower_count|number_format}
    </div>
    <div class="grid_3 right small">
        <a href="{$cfg->site_root_path}post/?t={$r.question_post_id}">{$r.question_adj_pub_date|relative_datetime}</a>
    </div>
	<div class="grid_11 omega">
		<p>{$r.question|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames}</p>
		
		{if $r.location}{$r.location}{/if}
		{if $r.description}{$r.description}{/if}
    </div>
    
</div>

<div class="individual-tweet reply clearfix">
    <div class="grid_2 alpha">
		<a href="{$cfg->site_root_path}user/?u={$r.answerer_username}&i={$i->twitter_username}"><img src="{$r.answerer_avatar}" width="48" height="48" class="avatar"></a>
    </div>
	<div class="grid_3 alpha small">
		<a href="{$cfg->site_root_path}user/?u={$r.answerer_username}&i={$i->twitter_username}">{if $r.answerer_username eq $i->twitter_username}You{else}{$r.answerer_username}{/if}</a>
    </div>
    <div class="grid_3 right small">&nbsp;???
	</div>
	<div class="grid_3 right small">
	   <a href="{$cfg->site_root_path}post/?t={$r.answer_post_id}">{$r.answer_adj_pub_date|relative_datetime}</a>
	</div>
	<div class="grid_11 omega">
		<p>{$r.answer|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames} </p>
		
		{if $r.location}{$r.location}{/if}
	</div>

</div>