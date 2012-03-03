{if $all_checkins|@count >0}
    <div class="section">
    
        <h2>Your Checkins</h2>
        {foreach from=$all_checkins item=current}
            <div class="clearfix article"> 
            <div class="individual-tweet post clearfix">
        	<div class="grid_5 alpha">
        	<a href="http://maps.google.com/maps?q={$current->geo}"><img src="{$current->place_obj->map_image}"></a>
        	</div>	
        	<div class="grid_6"> 
        	<img src="{$current->place_obj->icon}"> {$current->place} <br> {$current->location} <br>
        	
        	{foreach from=$current->links item=current_link}
        	   <a href="{$current_link->url}"><img src="{$current_link->url}" width=100px height=100px}></a> 
        	{/foreach}
        	
        	</div>
        	<div class="grid_5 omega"/> {$current->post_text} <br> <br> <a href="https://foursquare.com/user/{$current->author_user_id}/checkin/{$current->post_id}"> {$current->pub_date} </a> Days Ago  <br>
        	</div>
        	
        	<div class="grid_1">
                <span class="reply-count">
                <a href="{$site_root_path}post/?t={$current->post_id}&n={$current->network|urlencode}">{$current->reply_count_cache|number_format}</a></span>
    	    </div>
    	</div>
    	</div>
           
        {/foreach}  
    </div>

{else}
    <div class="alert urgent">
        No posts to display. {if $logged_in_user}Update your data and try again.{/if}
    </div>
{/if}

<div class="view-all" id="older-posts-div">
{if $next_page}
    <a href="{$site_root_path}?{if $smarty.get.v}v={$smarty.get.v}&{/if}
    {if $smarty.get.u}u={$smarty.get.u}&{/if}
    {if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$next_page}"id="next_page">&#60; Older Posts</a>
{/if}
{if $last_page}
    | <a href="{$site_root_path}?{if $smarty.get.v}v={$smarty.get.v}&{/if}
    {if $smarty.get.u}u={$smarty.get.u}&{/if}
    {if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$last_page}"id="last_page">Newer Posts  &#62;</a>
{/if}
</div>
