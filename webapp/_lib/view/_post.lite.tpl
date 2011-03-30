

{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_14">post</div>
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
    <div class="grid_14">
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
        {$t->location|truncate:60:' ...'}
       {/if}
        {if $t->network == 'twitter'}
         - <a href="http://twitter.com/intent/tweet?in_reply_to={$t->post_id}">Reply</a>
         - <a href="http://twitter.com/intent/retweet?tweet_id={$t->post_id}">Retweet</a>
         - <a href="http://twitter.com/intent/favorite?tweet_id={$t->post_id}">Favorite</a>
        {/if}
      </span>&nbsp;</div>
      </div><!--end post-->
      
    </div>
    <div class="grid_2 center">
    {if $t->network eq 'twitter'}
      {if $t->all_retweets > 0}
        <span class="reply-count"><a href="{$site_root_path}post/?t={$t->post_id}&n={$t->network}&v=fwds">{$t->all_retweets|number_format}{if $t->rt_threshold}+{/if}<!-- retweet{if $t->retweet_count_cache eq 1}{else}s{/if}--></a></span>
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