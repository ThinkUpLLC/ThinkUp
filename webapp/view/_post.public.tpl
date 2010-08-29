{if $smarty.foreach.foo.first && $headings != "NONE"}
  <div class="header clearfix">
    <div class="grid_1 alpha">&#160;</div>
    <div class="grid_3 right">name</div>
    <div class="grid_3 right">{if $sort eq 'no'}date{else}<a href="{$site_root_path}public.php">date</a>{/if}</div>
    <div class="grid_7">post</div>
    <div class="grid_2">
      {if $sort eq 'no'}&#160;{else}<a href="{$site_root_path}public.php?v=photos">w/ photos</a>{/if}
    </div>
    <div class="grid_2">
      {if $sort eq 'no'}&#160;{else}<a href="{$site_root_path}public.php?v=links">w/ links</a>{/if}
    </div>
    <div class="grid_2 center">
      {if $sort eq 'no'}replies{else}<a href="{$site_root_path}public.php?v=mostreplies">replies</a>{/if}
      {if $sort neq 'no'}(<a href="{$site_root_path}public.php?v=mostreplies1wk">7d</a>){/if}
    </div>
    <div class="grid_2 center omega">
      {if $sort eq 'no'}forwards{else}<a href="{$site_root_path}public.php?v=mostretweets">fwds</a> {/if}
      {if $sort neq 'no'}(<a href="{$site_root_path}public.php?v=mostretweets1wk">7d</a>){/if}
    </div>
  </div>
{/if}

{if $t->in_reply_to_post_id}
<div class="clearfix" id="locationReplies">
{else}
<div class="clearfix" id="locationRetweets">
{/if}
  <div class="individual-tweet post clearfix
  {if $t->short_location}{$t->short_location|escape:'url'|replace:'%':''|replace:'.':''}{else}__NULL__{/if}">
    <div class="grid_1 alpha">
      <img src="{$t->author_avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$t->network}/assets/img/favicon.ico" class="service-icon"/>
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
    <div class="grid_3 right small">
      {if $t->network == 'twitter'}
      <a href="http://twitter.com/{$t->author_username}/statuses/{$t->post_id}">
        {$t->adj_pub_date|relative_datetime} ago
      </a>
      {else}
      {$t->adj_pub_date|relative_datetime} ago
      {/if}
    </div>
    <div class="grid_11">
      {if $t->link->is_image}
        <div class="pic"><a href="{$t->link->url}"><img src="{$t->link->expanded_url}" /></a></div>
      {/if}
      <p>
        {if $t->post_text}
          {$t->post_text|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames_to_twitter}
        {else}
          <span class="no-post-text">No post text</span>
        {/if}
        {if !$post && $t->in_reply_to_post_id }
          <a href="{$site_root_path}post/?t={$t->in_reply_to_post_id}">&larr;</a>
        {/if}
      </p>
      {if $t->link->expanded_url and !$t->link->is_image and ($t->link->expanded_url != $t->link->url)}
        <small>
          <a href="{$t->link->expanded_url}" title="{$t->link->expanded_url}">{$t->link->expanded_url}</a>
        </small>
      {/if}
      <div class="small gray">
        {if $t->is_geo_encoded < 2}
        Location:
        <a href="#" class="with_tooltip"
        willWorkOnID="{if $t->in_reply_to_post_id}locationReplies{else}locationRetweets{/if}"
        value="{if $t->short_location}{$t->short_location|escape:'url'|replace:'%':''|replace:'.':''}
        {else}__NULL__{/if}"
        title="{if $t->in_reply_to_post_id or $t->in_retweet_of_post_id}
        {if $t->is_geo_encoded eq 1 && $t->reply_retweet_distance eq 0}
        From a very nearby place
        {elseif $t->is_geo_encoded eq 1 && $t->reply_retweet_distance neq -1}
          {if $unit eq 'km'}
          {$t->reply_retweet_distance} kms away from post
          {else}
          {$t->reply_retweet_distance} miles away from post
          {/if}
        {elseif $t->is_geo_encoded eq 0}
        Distance information not available yet
        {/if}
        {/if}">{$t->location|truncate:60:' ...'}</a>
        {else}
        Location: 
        <a href="#" class="with_tooltip"
        willWorkOnID="{if $t->in_reply_to_post_id}locationReplies{else}locationRetweets{/if}"
        value="__NULL__" title="Not Available">Not Available</a>
        {/if}
      </div>
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
</div>