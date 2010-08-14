{if $smarty.foreach.foo.first && $headings != "NONE"}
  <div class="header clearfix">
    <div class="grid_1 alpha">&#160;</div>
    <div class="grid_3 right">name</div>
    <div class="grid_3 right">{if $sort eq 'no'}date{else}<a href="{$site_root_path}public.php">date</a>{/if}</div>
    <div class="grid_7">post</div>
    <div class="grid_2">{if $sort eq 'no'}&#160;{else}<a href="{$site_root_path}public.php?v=photos">w/ photos</a>{/if}</div>
    <div class="grid_2">{if $sort eq 'no'}&#160;{else}<a href="{$site_root_path}public.php?v=links">w/ links</a>{/if}</div>
    <div class="grid_2 center">{if $sort eq 'no'}replies{else}<a href="{$site_root_path}public.php?v=mostreplies">replies</a>{/if} {if $sort neq 'no'}(<a href="{$site_root_path}public.php?v=mostreplies1wk">7d</a>){/if}</div>
    <div class="grid_2 center omega">{if $sort eq 'no'}forwards{else}<a href="{$site_root_path}public.php?v=mostretweets">fwds</a> {/if}{if $sort neq 'no'}(<a href="{$site_root_path}public.php?v=mostretweets1wk">7d</a>){/if}</div>
  </div>
{/if}

<div class="individual-tweet post clearfix">
  <div class="grid_1 alpha">
    <img src="{$t->author_avatar}" class="avatar">
    {if $t->is_reply_by_friend or $t->is_retweet_by_friend}
       <div class="small gray">Friend</div>
    {/if}
  </div>
  <div class="grid_3 right small">
    {if $t->network == 'twitter' && $username_link != 'internal'}
    <a href="http://twitter.com/{$t->author_username}">{$t->author_username}</a>
    {else}
    <a href="{$site_root_path}public.php?u={$t->author_username|urlencode}&n={$t->network|urlencode}">{$t->author_username}</a>
    {/if}
    {if $t->author->follower_count > 0}
      <br>{$t->author->follower_count|number_format} followers
    {/if}
    {if $t->author->location}
      <div class="small gray">{$t->author->location}</div>
    {/if}
    
  </div>
  <div class="grid_3 right small">
    {if $t->network == 'twitter'}
    <a href="http://twitter.com/{$t->author_username}/statuses/{$t->post_id}">{$t->adj_pub_date|relative_datetime} ago</a>
    {else}
    {$t->adj_pub_date|relative_datetime} ago
    {/if}
  </div>
  <div class="grid_11">
    {if $t->link->is_image}
      <div class="pic"><a href="{$t->link->url}"><img src="{$t->link->expanded_url}" /></a></div>
    {/if}
    <p>
      {$t->post_text|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames_to_twitter}
      {if !$post && $t->in_reply_to_post_id }
        <a href="{$site_root_path}post/?t={$t->in_reply_to_post_id}">&larr;</a>
      {/if}
    </p>
    {if $t->link->expanded_url and !$t->link->is_image and ($t->link->expanded_url != $t->link->url)}
      <small><a href="{$t->link->expanded_url}" title="{$t->link->expanded_url}">{$t->link->expanded_url}</a></small>
    {/if}
  </div>
  <div class="grid_2 center">
    {if $t->reply_count_cache > 0}
      <span class="reply-count"><a href="{$site_root_path}public.php?t={$t->post_id}&n={$t->network}">{$t->reply_count_cache}<!-- repl{if $t->reply_count_cache eq 1}y{else}ies{/if}--></a></span>
    {else}
      &#160;
    {/if}
  </div>
  <div class="grid_2 center omega">
    {if $t->retweet_count_cache > 0}
      <span class="reply-count"><a href="{$site_root_path}public.php?t={$t->post_id}&n={$t->network}#fwds">{$t->retweet_count_cache}<!-- retweet{if $t->retweet_count_cache eq 1}{else}s{/if}--></a></span>
    {else}
      &#160;
    {/if}
  </div>
</div>
