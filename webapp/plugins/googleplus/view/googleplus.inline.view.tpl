          {if $post}
            <div class="clearfix alert stats">
{include file="post.index._top-post.tpl"}

            <div class="grid_6 center keystats omega">
              <div class="big-number">
               {if $post->favlike_count_cache}
                  <h1>{$post->favlike_count_cache}</h1>
                  <h3>+1{if $post->reply_count_cache neq 1}s{/if}
                    
                     in {$post->adj_pub_date|relative_datetime}</h3>
              </div>
            </div>
        {/if}
        
        </div>
        {/if}
    
    {if $display eq 'all_gplus_posts' or $gplus_posts}

<div class="section">

{if $description}<h2>{$description}</h2>{/if}

<div class="header">
  
      {if $is_searchable}<a href="#" class="grid_search" title="Search" onclick="return false;"><span id="grid_search_icon">Search</span></a>{/if}
      {if $logged_in_user and $display eq 'all_gplus_posts'} | <a href="{$site_root_path}post/export.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">Export</a>{/if}

</div>


    {if ($display eq 'all_gplus_posts' and not $gplus_posts) or 
        ($display eq 'most_replied_to_posts' and not $gplus_posts) }
      <div class="alert urgent"> 
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
    
    <div class="view-all" id="older-posts-div">
      {if $next_page}
        <a href="{$site_root_path}?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$next_page}" id="next_page">&#60; Older Posts</a>
      {/if}
      {if $last_page}
        | <a href="{$site_root_path}?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$last_page}" id="last_page">Newer Posts  &#62;</a>
      {/if}
    </div>
</div>

{/if}
    