{if $smarty.foreach.foo.first}
  <div class="header clearfix"> 
    <div class="grid_1 alpha">&#160;</div>
    <div class="grid_3 right">name</div>
    <div class="grid_3 right">date</div>
    <div class="grid_11">post</div>
    <div class="grid_2 center">replies</div>
    <div class="grid_2 center omega">shared</div>
  </div>
{/if}

<div class="individual-tweet post clearfix">
  <div class="grid_1 alpha">
    <a href="{$site_root_path}user/?u={$t->author_username}&i={$smarty.session.network_username}"><img src="{$t->author_avatar}" class="avatar"></a>
  </div>
  <div class="grid_3 right small">
    <a href="{$site_root_path}user/?u={$t->author_username}&i={$smarty.session.network_username}">{$t->author_username}</a>
  </div>
  <div class="grid_3 right small">
    <a href="{$site_root_path}post/?t={$t->post_id}">{$t->adj_pub_date|relative_datetime} ago</a>
  </div>
  <div class="grid_11">
    {if $t->link->is_image}
      <div class="pic"><a href="{$t->link->url}"><img src="{$t->link->expanded_url}" /></a></div>
    {/if}
    <p>
      {$t->post_text|link_usernames}
      {if $t->in_reply_to_post_id}
        [<a href="{$site_root_path}post/?t={$t->in_reply_to_post_id}">in reply to</a>]
      {/if}
    </p>
    {if $t->link->expanded_url}
      <ul>
        <li><a href="{$t->link->expanded_url}" title="{$t->link->expanded_url}" target="_blank">{if $t->link->title neq ''}{$t->link->title}{else}{$t->link->expanded_url}{/if}</a></li>
      </ul>
    {/if}
    {if $t->location}
      <div class="small gray">location: {$t->location}</div>
    {/if}
  </div>
  <div class="grid_2 center">
    {if $t->mention_count_cache > 0}
      <span class="reply-count"><a href="{$site_root_path}post/?t={$t->post_id}">{$t->mention_count_cache}</a></span>
    {else}
      &#160;
    {/if}
  </div>
  <div class="grid_2 center omega">
    {if $t->retweet_count_cache > 0}
      <span class="reply-count"><a href="{$site_root_path}post/?t={$t->post_id}">{$t->retweet_count_cache}</a></span>
    {else}
      &#160;
    {/if}
  </div>
</div>
