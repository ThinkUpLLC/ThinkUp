<div class="">
  <div class="help-container">{insert name="help_link" id=$display}</div>
  {if $description}
    <i>{$description} 
      {if $is_searchable}
        <br /><a href="#" class="grid_search" title="Search" onclick="return false;"><span id="grid_search_icon">Search</span></a> 
      {/if}
      {if $logged_in_user and $display eq 'all_gplus_posts'} | <a href="{$site_root_path}post/export.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">Export</a>{/if}
    </i>
    {/if}
</div>

{if ($display eq 'all_gplus_posts' and not $gplus_posts) or 
    ($display eq 'most_replied_to_posts' and not $gplus_posts) }
  <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No posts to display.
    </p>
  </div>
{/if}

{if $gplus_posts}
<div id="all-posts-div">
  {foreach from=$gplus_posts key=tid item=t name=foo}
    {include file="_post.tpl" t=$t}
  {/foreach}
</div>
{/if}
