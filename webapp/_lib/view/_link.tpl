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
    <a href="http://twitter.com/{$l->other.author_username}"><img src="{$l->other.author_avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$l->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon"/></a>
  </div>
  <div class="grid_3 right small">
    <a href="http://twitter.com/{$l->other.author_username}">{$l->other.author_username}</a>
  </div>
  <div class="grid_3 right small">
    <a href="{$site_root_path}post/?t={$l->other.post_id}&n={$l->network}">{$l->other.adj_pub_date|relative_datetime}</a>
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
      {if $l->other.post_text}
        {$l->other.post_text|link_usernames:$i->network_username:$t->network}
      {else}
        <span class="no-post-text">No post text</span>
      {/if}
      {if $l->other.in_reply_to_post_id}
        [<a href="{$site_root_path}post/?t={$l->in_reply_to_post_id}&n={$l->network}">in reply to</a>]
      {/if}
    </p>
    {if $l->other.location}
      <h4 class="tweetstamp">{$l->other.location}</h4>
    {/if}
  </div>
  <div class="grid_2 center omega"> 
    {if $l->other.reply_count_cache > 0}
      <span class="reply-count"><a href="{$site_root_path}post/?t={$l->post_id}&n={$l->network}">{$l->other.reply_count_cache}</a></span>
    {/if}
  </div>
</div>
