{*
Render a post with an author but without replies or retweet counts.

Included in multiple plugin templates which render lists of posts.

Parameters:
$post (required) Post object
$scrub_reply_username (optional) If set or not false, scrub the @reply username from the post_text
$username_link (optional) If set to 'internal', render username link to internal user page.
$show_distance (optional) If set to 'true', show the location and reply/retweet distance from original post.
$unit (optional) If $show_distance='true', unit should be 'mi' or 'km' for miles or kilometers of distance.
*}
<div class="clearfix article">
<div class="individual-tweet post clearfix{if $post->is_protected} private{/if}">
    <div class="grid_2 alpha">
      <div class="avatar-container">
        {if $post->network == 'twitter'} <a href="http://twitter.com/{$post->author_username}" title="{$post->author_username} on Twitter">{/if}
        <img src="{$post->author_avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon"/>
        {if $post->network == 'twitter'}</a>{/if}
      </div>
    </div>
    <div class="grid_3 small">
      {if $post->network == 'twitter' && $username_link != 'internal'}
        <a href="http://twitter.com/{$post->author_username}" title="{$post->author_username} on Twitter">{$post->author_username}</a>
      {else}
        {$post->author_username}
      {/if}

      {if ($post->author && $post->author->follower_count > 0)}
        <div class="gray">{$post->author->follower_count|number_format} followers</div>
      {else}
      <br>
      {/if}
        {if $post->network == 'twitter'}
            {if $post->is_reply_by_friend or $post->is_retweet_by_friend}
                <a href="http://twitter.com/{$post->author_username}" title="Friend"><span class="sprite ui-icon-contact"></span></a>
            {else}
                <a href="http://twitter.com/{$post->author_username}" title="{$post->author_username} on Twitter"><span class="sprite ui-icon-person"></span></a>
            {/if}
        {/if}
    </div>
    <div class="grid_12 omega">
      {if $post->link->image_src}
         <div class="pic"><a href="{$post->link->url}"><img src="{$post->link->image_src}" /></a></div>
      {/if}
      <div class="post">
        {if $post->post_text}
          {if $scrub_reply_username}
            <div class="reply_text" id="reply_text-{$smarty.foreach.foo.iteration}">
            {$post->post_text|filter_xss|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames_to_twitter}
            </div>
          {else}
            {$post->post_text|filter_xss|link_usernames_to_twitter}
          {/if}
        {else}
          <span class="no-post-text">No post text</span>
        {/if}
        {if !$post && $post->in_reply_to_post_id }
          <a href="{$site_root_path}post/?t={$post->in_reply_to_post_id}">&larr;</a>
        {/if}
      {if $post->link->expanded_url and !$post->link->image_src and ($post->link->expanded_url != $post->link->url)}
        <small>
          <a href="{$post->link->expanded_url}" title="{$post->link->expanded_url}">{$post->link->expanded_url}</a>
        </small>
      {/if}
      <div class="small gray">
      {if $post->is_protected}
        <span class="sprite icon-locked"></span>
      {/if}
      
       <span class="metaroll">
        <a href="{$site_root_path}post/?t={$post->post_id}&n={$post->network}">{$post->adj_pub_date|relative_datetime} ago</a>
        <!--{if $post->network == 'twitter'}
         - <a href="http://twitter.com/?status=@{$post->author_username}%20&in_reply_to_status_id={$post->post_id}&in_reply_to={$post->author_username}" target="_blank">Reply on Twitter</a><span class="ui-icon ui-icon-newwin"></span>
        {/if}-->
        {if $post->is_geo_encoded < 2}
        from 
        {if $show_distance}
            {if $unit eq 'km'}
              {$post->reply_retweet_distance|number_format} kms away in
              {else}
              {$post->reply_retweet_distance|number_format} miles away in 
            {/if}
        {/if}
        {$post->location|truncate:60:' ...'}
       {/if}
      {if $post->network == 'twitter'}
      <a href="http://twitter.com/intent/tweet?in_reply_to={$post->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-w" title="reply"></a>
      <a href="http://twitter.com/intent/retweet?tweet_id={$post->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-e" title="retweet"></a>
      <a href="http://twitter.com/intent/favorite?tweet_id={$post->post_id}"><span class="ui-icon ui-icon-star" title="favorite"></a>
      {/if}
       </span>&nbsp;</div>
      </div>
    </div>
  </div>
</div>