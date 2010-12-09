{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_1 alpha">&#160;</div>
    <div class="grid_3 right">name</div>
    <div class="grid_12">post</div>
    <div class="grid_2 center">
      {if $t->network eq 'twitter'}retweets{/if}
    </div>
    <div class="grid_2 center omega">
      replies
    </div>
  </div>
{/if}

<div class="clearfix">
<div class="individual-tweet post clearfix{if $t->is_protected} private{/if}">
    <div class="grid_1 alpha">
      <img src="{$t->author_avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$t->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon"/>
      {if $t->is_reply_by_friend or $t->is_retweet_by_friend}
         <div class="small gray">Friend</div>
      {/if}
    </div>
    <div class="grid_3 right small">
      {if $t->network == 'twitter' && $username_link != 'internal'}
      <a href="http://twitter.com/{$t->author_username}">{$t->author_username}</a>
      {else}
      <a href="{$site_root_path}public.php?u={$t->author_username|urlencode}&n={$t->network|urlencode}">
        {$t->author_username}
      </a>
      {/if}
      {if $t->author->follower_count > 0}
        <br>{$t->author->follower_count|number_format} followers
      {/if}
        </div>
    <div class="grid_12">
      {if $t->link->is_image}
        <div class="pic"><a href="{$t->link->url}"><img src="{$t->link->expanded_url}" /></a></div>
      {/if}
      <div class="post">
        {if $t->post_text}
          {if $scrub_reply_username}
            {$t->post_text|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames_to_twitter}
          {else}
            {$t->post_text|link_usernames_to_twitter}
          {/if}
        {else}
          <span class="no-post-text">No post text</span>
        {/if}
        {if !$post && $t->in_reply_to_post_id }
          <a href="{$site_root_path}post/?t={$t->in_reply_to_post_id}">&larr;</a>
        {/if}
      {if $t->link->expanded_url and !$t->link->is_image and ($t->link->expanded_url != $t->link->url)}
        <small>
          <a href="{$t->link->expanded_url}" title="{$t->link->expanded_url}">{$t->link->expanded_url}</a>
        </small>
      {/if}
      <div class="small gray">
       <span class="metaroll">
        <a href="{$site_root_path}post/?t={$t->post_id}&n={$t->network}">{$t->adj_pub_date|relative_datetime} ago</a> 
        {if $t->is_geo_encoded < 2}
        {if $show_distance}
            {if $unit eq 'km'}
              {$t->reply_retweet_distance|number_format} kms away
              {else}
              {$t->reply_retweet_distance|number_format} miles away in 
            {/if}
        {/if}
        {$t->location|truncate:60:' ...'}
       {/if}
       </span>&nbsp;</div>
      </div>
    </div>
    <div class="grid_2 center">
    {if $t->network eq 'twitter'}
      {if $t->all_retweets > 0}
        <span class="reply-count"><a href="{$site_root_path}post/?t={$t->post_id}&n={$t->network}&v=fwds">{$t->all_retweets}<!-- retweet{if $t->retweet_count_cache eq 1}{else}s{/if}--></a></span>
      {else}
        &#160;
      {/if}
    {/if}
    </div>
    <div class="grid_2 center omega">
      {if $t->reply_count_cache > 0}
        <span class="reply-count"><a href="{$site_root_path}post/?t={$t->post_id}&n={$t->network}">{$t->reply_count_cache}<!-- repl{if $t->reply_count_cache eq 1}y{else}ies{/if}--></a></span>
      {else}
        &#160;
      {/if}
    </div>
  </div>
</div>