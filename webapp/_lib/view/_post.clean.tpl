<!--
{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_1 alpha">&#160;</div>
    <div class="grid_4 right">name</div>
    <div class="grid_15 omega">post</div>
  </div>
{/if}
-->
<div class="clearfix">
<div class="individual-tweet post clearfix{if $t->is_protected} private{/if}">
    <div class="grid_2 alpha">
      <div class="avatar-container">
        <a href="{$site_root_path}user/?u={$t->author_username|urlencode}&n={$t->network|urlencode}&i={$selected_instance_username}">
        <img src="{$t->author_avatar}" class="avatar2"/></a><img src="{$site_root_path}plugins/{$t->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon"/>
      </div>
    </div>
    <div class="grid_4 small">
      {if $t->network == 'twitter' && $username_link != 'internal'}
        <a {if $reply_count && $reply_count > $top_20_post_min}id="post_username-{$smarty.foreach.foo.iteration}" {/if}
        href="{$site_root_path}user/?u={$t->author_username|urlencode}&n={$t->network|urlencode}&i={$selected_instance_username}">
        {$t->author_username}</a>
      {else}
        <a href="{$site_root_path}public.php?u={$t->author_username|urlencode}&n={$t->network|urlencode}">{$t->author_username}</a>
      {/if}

      {if $t->author->follower_count > 0}
        <div class="gray">{$t->author->follower_count|number_format} followers</div>
      {/if}
        {if $t->network == 'twitter'}
            {if $t->is_reply_by_friend or $t->is_retweet_by_friend}
                <a href="http://twitter.com/{$t->author_username}" title="Friend"><span class="sprite ui-icon-contact"></span></a>
            {else}
                <a href="http://twitter.com/{$t->author_username}" title="{$t->author_username} on Twitter"><span class="sprite ui-icon-person"></span></a>
            {/if}
        {/if}
      
    </div>
    <div class="grid_12 omega">
      {if $t->link->is_image}
        <div class="pic"><a href="{$t->link->url}"><img src="{$t->link->expanded_url}" /></a></div>
      {/if}
      <div class="post">
        {if $t->post_text}
          {if $scrub_reply_username}
            {if $reply_count && $reply_count > $top_20_post_min}
                <div class="reply_text" id="reply_text-{$smarty.foreach.foo.iteration}">
            {/if} 
            {$t->post_text|filter_xss|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames_to_twitter}
            {if $reply_count && $reply_count > $top_20_post_min}</div>{/if}
          {else}
            {$t->post_text|filter_xss|link_usernames_to_twitter}
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
      {if $t->is_protected}
        <span class="sprite icon-locked"></span>
      {/if}
      
       <span class="metaroll">
        <a href="{$site_root_path}post/?t={$t->post_id}&n={$t->network}">{$t->adj_pub_date|relative_datetime} ago</a>
        <!--{if $t->network == 'twitter'}
         - <a href="http://twitter.com/?status=@{$t->author_username}%20&in_reply_to_status_id={$t->post_id}&in_reply_to={$t->author_username}" target="_blank">Reply on Twitter</a><span class="ui-icon ui-icon-newwin"></span>
        {/if}-->
        {if $t->is_geo_encoded < 2}
        from 
        {if $show_distance}
            {if $unit eq 'km'}
              {$t->reply_retweet_distance|number_format} kms away
              {else}
              {$t->reply_retweet_distance|number_format} miles away in 
            {/if}
        {/if}
        {$t->location|truncate:60:' ...'}
       {/if}
      {if $t->network == 'twitter'}
      <a href="http://twitter.com/intent/tweet?in_reply_to={$t->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-w" title="reply"></a>
      <a href="http://twitter.com/intent/retweet?tweet_id={$t->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-e" title="retweet"></a>
      <a href="http://twitter.com/intent/favorite?tweet_id={$t->post_id}"><span class="ui-icon ui-icon-star" title="favorite"></a>
      {/if}
       
       </span>&nbsp;</div>
      </div>
    </div>
  </div>
</div>