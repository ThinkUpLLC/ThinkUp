{if $all_checkins|@count >0}
<div class="section">

	
    <h2>Your Checkins</h2>
    {foreach from=$all_checkins item=current}
   <div class="clearfix article"> 
    <div class="individual-tweet post clearfix">
	<div class="grid_5 alpha">
	<a href="http://maps.google.co.uk/maps?q={$current->geo}"><img src="{$current->place_obj->map_image}"></a>
	</div>	
	<div class="grid_7"> 
	<img src="{$current->place_obj->icon}"> {$current->place} <br> {$current->location} <br>
	
	 {foreach from=$current->links item=current_link}
	 <a href="{$current_link->url}"><img src="{$current_link->url}" width=100px height=100px}></a>
	 
	 {/foreach}
	
	</div>
	<div class="grid_5 omega"/> {$current->post_text} <br> <br> {$current->pub_date} <br>
	
	{if $current->reply_count_cache > 0}
        <span class="reply-count">
        <a href="{$site_root_path}post/?t={$current->post_id}&n={$current->network|urlencode}">{$current->reply_count_cache|number_format}</a></span>
      {else}
        &#160;
      {/if}
	
	</div>
	</div>
	</div>
   
     
    
    {/foreach}
    </center>
    
</div>
{else}
<div class="alert urgent">
    No posts to display. {if $logged_in_user}Update your data and try again.{/if}
</div>
{/if}
