<div class="">
  <div class="help-container">{insert name="help_link" id=$display}</div>
  {if $description}
    <i>{$description} 
      {if $is_searchable}
        <br /><a href="#" class="grid_search" title="Search" onclick="return false;"><span id="grid_search_icon">Search</span></a> 
      {/if}
      {if $logged_in_user and $display eq 'all_facebook_posts'} | <a href="{$site_root_path}post/export.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">Export</a>{/if}
    </i>
    {/if}
</div>

{if ($display eq 'all_facebook_posts' and not $all_facebook_posts) or 
    ($display eq 'all_facebook_replies' and not $all_facebook_replies) }
  <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No Facebook posts to display.
    </p>
  </div>
{/if}

{if $all_facebook_posts and ($display eq 'all_facebook_posts' OR $display eq 'questions')}
<div id="all-posts-div">
  {foreach from=$all_facebook_posts key=tid item=t name=foo}
    {include file="_post.tpl" t=$t}
  {/foreach}
</div>
{/if}

{if $most_replied_to_posts}
<div id="all-posts-div">
  {foreach from=$most_replied_to_posts key=tid item=t name=foo}
    {include file="_post.tpl" t=$t}
  {/foreach}
</div>
{/if}


{if ($display eq 'followers_mostfollowed' and not $facebook_users) or ($display eq 'friends_mostactive' and not $facebook_users) }
  <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No Facebook users found.
    </p>
  </div>
{/if}

{if $facebook_users}
  {foreach from=$facebook_users key=fid item=f name=foo}
    {include file="_user.tpl" t=$f}
  {/foreach}
{/if}

{if ($display eq 'links_from_friends' and not $links_from_friends)}
  <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;">
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No data to display.
    </p>
  </div>
{/if}

{if $links_from_friends}
  {foreach from=$links_from_friends key=lid item=l name=foo}
    {include file="_link.tpl" t=$f}
  {/foreach}  
{/if}

{if $is_searchable}
    {include file="_grid.search.tpl"}
    <script type="text/javascript" src="{$site_root_path}assets/js/grid_search.js"></script>
    
{/if}

