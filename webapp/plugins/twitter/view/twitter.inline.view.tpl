<div class="section">
<div class="clearfix">
  {insert name="help_link" id=$display}
  <h2>{if $parent_name}<a href="?v={$parent}&u={$instance->network_username}&n=twitter">{$parent_name}</a> &rarr; {/if}{$header}</h2>
  {if $description}<h3>{$description}</h3>{/if}
</div>
{if ($display eq 'tweets-all' and not $all_tweets) or 
    ($display eq 'tweets-mostreplies' and not $most_replied_to_tweets) or
    ($display eq 'tweets-mostretweeted' and not $most_retweeted) or
    ($display eq 'tweets-convo' and not $author_replies)}
  <div class="alert urgent" style=""> 
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No tweets to display. <a href="{$site_root_path}crawler/updatenow.php">Update your data now.</a>
    </p>
  </div>
{/if}

    <div class="header">
    {if $is_searchable}<a href="#" class="grid_search" title="Search" onclick="return false;"><span id="grid_search_icon">Search</span></a>{/if}
    {if $logged_in_user and $display eq 'tweets-all'} | <a href="{$site_root_path}post/export.php?u={$instance->network_username|urlencode}&n={$instance->network}">Export</a>{/if}
    </div>

{if $is_searchable}
    {include file="_grid.search.tpl"}
    <script type="text/javascript" src="{$site_root_path}assets/js/grid_search.js"></script>
{/if}

{if $all_tweets and ($display eq 'tweets-all' or $display eq 'tweets-questions')}
<div id="all-posts-div">
  {foreach from=$all_tweets key=tid item=t name=foo}
    {include file="_post.counts_no_author.tpl" post=$t headings="NONE"}
  {/foreach}
</div>
{/if}

{if $all_tweets and $display eq 'ftweets-all'}
<div id="all-posts-div">
  {foreach from=$all_tweets key=tid item=t name=foo}
    {include file="_post.author_no_counts.tpl" post=$t}
  {/foreach}
</div>
{/if}

{if $all_favd and $display eq 'favd-all'}
  {foreach from=$all_favd key=tid item=t name=foo}
    {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets='true'}
  {/foreach}
{/if}

{if $most_replied_to_tweets}
  {foreach from=$most_replied_to_tweets key=tid item=t name=foo}
    {include file="_post.counts_no_author.tpl" post=$t}
  {/foreach}
{/if}

{if $most_retweeted}
  {foreach from=$most_retweeted key=tid item=t name=foo}
    {include file="_post.counts_no_author.tpl" post=$t}
  {/foreach}
{/if}

{if $author_replies}
  {foreach from=$author_replies key=tahrt item=r name=foo}
    {include file="_post.qa.tpl" t=$t}
  {/foreach}
{/if}

{if $messages_to_you}
<div id="all-posts-div">
  {foreach from=$messages_to_you key=tid item=t name=foo}
    {include file="_post.tpl" t=$t}
  {/foreach}
</div>
{/if}

{if ($display eq 'mentions-all' and not $all_mentions) or 
    ($display eq 'mentions-allreplies' and not $all_replies) or
    ($display eq 'home-timeline' and not $home_timeline) or
    ($display eq 'mentions-orphan' and not $orphan_replies)}
  <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No data to display. <a href="{$site_root_path}crawler/updatenow.php">Update your data now.</a>
    </p>
  </div>
{/if}

{if $orphan_replies}
  {foreach from=$orphan_replies key=tid item=t name=foo}
    {include file="_post.author_no_counts.tpl" post=$t}
  {/foreach}
  </form>
{/if} 

{if $all_mentions}
<div id="all-posts-div">
  {foreach from=$all_mentions key=tid item=t name=foo}
    {include file="_post.author_no_counts.tpl" post=$t}
  {/foreach}
</div>
{/if}

{if $home_timeline}
<div id="all-posts-div">
  {foreach from=$home_timeline key=tid item=t name=foo}
    {include file="_post.tpl" t=$t}
  {/foreach}
</div>
{/if}

{if $all_replies}
  {foreach from=$all_replies key=tid item=t name=foo}
    {include file="_post.author_no_counts.tpl" post=$t}
  {/foreach}
{/if}

{if ($display eq 'friends-mostactive' and not $people) or ($display eq 'friends-leastactive' and not $people) 
or ($display eq 'friends-mostfollowed' and not $people) or ($display eq 'friends-former' and not $people)
or ($display eq 'friends-notmutual' and not $people) 
or ($display eq 'followers-mostfollowed' and not $people) or ($display eq 'followers-leastlikely' and not $people)
or ($display eq 'followers-former' and not $people) or ($display eq 'followers-earliest' and not $people)}
  <div class="alert urgent" style=""> 
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No users found. <a href="{$site_root_path}crawler/updatenow.php">Update your data now.</a>
    </p>
  </div>
{/if}

{if $people}
  {foreach from=$people key=fid item=f name=foo}
    {include file="_user.tpl" t=$f}
  {/foreach}
{/if}

{if ($display eq 'links-friends' and not $links) or ($display eq 'links-favorites' and not $links) or ($display eq 'links-photos' and not $links)}
  <div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;">
    <p>
      <span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
      No data to display. <a href="{$site_root_path}crawler/updatenow.php">Update your data now.</a>
    </p>
  </div>
{/if}

{if $links}
  {foreach from=$links key=lid item=l name=foo}
    {include file="_link.tpl" t=$f}
  {/foreach}  
{/if}

<div class="view-all" id="older-posts-div">
  {if $next_page}
    <a href="{$site_root_path}?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$next_page}" id="next_page">&#60; Older</a>
  {/if}
  {if $last_page}
    | <a href="{$site_root_path}?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$last_page}" id="last_page">Newer &#62;</a>
  {/if}
</div>

</div>