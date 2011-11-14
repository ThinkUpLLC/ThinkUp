{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_1 alpha">&nbsp;</div>
    <div class="grid_3 right">name</div>
    <div class="grid_13">post</div>
  </div>
{/if}

<div class="individual-tweet post clearfix article">
  <div class="grid_1 alpha">
    <a href="https://twitter.com/intent/user?user_id={$l->container_post->author_user_id}">
    <img src="{$l->container_post->author_avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$l->container_post->network|get_plugin_path}/assets/img/favicon.png" class="service-icon"/></a>
  </div>
  <div class="grid_3 right small">
    <a href="https://twitter.com/intent/user?user_id={$l->container_post->author_user_id}">{$l->container_post->author_username}</a>
  </div>
  <div class="grid_13">
    {if $l->image_src}
      <a href="{$l->url}"><div class="pic"><img src="{$l->image_src}" /></div></a>
    {else}
      {if $l->expanded_url}
      <small>
        <a href="{$l->expanded_url}" title="{$l->expanded_url}">{if $l->title}{$l->title}{else}{$l->expanded_url}{/if}</a>
      </small>
      {/if}
    {/if}
    <div class="post">
      {if $l->container_post->post_text}
        {$l->container_post->post_text|filter_xss|link_usernames:$i->network_username:$t->network}
      {else}
        <span class="no-post-text">No post text</span>
      {/if}
      {if $l->container_post->in_reply_to_post_id}
        [<a href="{$site_root_path}post/?t={$t->in_reply_to_post_id}&n={$t->network}">in reply to</a>]
      {/if}
      <div class="small gray">
      <span class="metaroll">
      <a href="http://twitter.com/{$l->container_post->author_username}/status/{$l->container_post->post_id}">{$l->container_post->adj_pub_date|relative_datetime}</a>
       {$l->container_post->location}</span>&nbsp;</div>
  </div>
  </div>
</div>
