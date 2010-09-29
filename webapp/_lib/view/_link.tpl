{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_1 alpha">&nbsp;</div>
    <div class="grid_3 right">name</div>
    <div class="grid_3 right">date</div>
    <div class="grid_11">post</div>
    <div class="grid_2 center omega">replies</div>
  </div>
{/if}

<div class="individual-tweet post clearfix">
  <div class="grid_1 alpha">
    <a href="http://twitter.com/{$l->container_post->author_username}"><img src="{$l->container_post->author_avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$l->container_post->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon"/></a>
  </div>
  <div class="grid_3 right small">
    <a href="http://twitter.com/{$l->container_post->author_username}">{$l->container_post->author_username}</a>
  </div>
  <div class="grid_3 right small">
    <a href="http://twitter.com/{$l->container_post->author_username}/post/{$l->container_post->post_id}">{$l->container_post->adj_pub_date|relative_datetime}</a>
  </div>
  <div class="grid_11">
    {if $l->is_image}
      <a href="{$l->url}"><div class="pic"><img src="{$l->expanded_url}" /></div></a>
    {else}
      {if $l->expanded_url}
        <a href="{$l->expanded_url}" title="{$l->expanded_url}">{$l->title}</a>
      {/if}
    {/if}
    <p>
      {if $l->container_post->post_text}
        {$l->container_post->post_text|link_usernames:$i->network_username:$t->network}
      {else}
        <span class="no-post-text">No post text</span>
      {/if}
      {if $l->container_post->in_reply_to_post_id}
        [<a href="{$site_root_path}post/?t={$t->in_reply_to_post_id}&n={$t->network}">in reply to</a>]
      {/if}
    </p>
    <h3></h3>
    {if $l->container_post->location}
      <div class="small gray">{$l->container_post->location}</div>
    {/if}
  </div>
  <div class="grid_2 center omega"> 
    {if $l->container_post->reply_count_cache > 0}
      <span class="reply-count"><a href="{$site_root_path}post/?t={$t->post_id}&n={$t->network}">{$l->container_post->reply_count_cache}</a></span>
    {/if}
  </div>
</div>
