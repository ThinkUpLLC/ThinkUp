{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_1 alpha">&#160;</div>
    <div class="grid_3 right">name</div>
    <div class="grid_3 right">followers</div>
    <div class="grid_3 right">date</div>
    <div class="grid_10 omega">post</div>
  </div>
{/if}

<div class="clearfix">
  <div class="individual-tweet clearfix{if $t->is_protected} private{/if}{if $t->in_reply_to_post_id} reply{/if}">
    <div class="grid_1 alpha">
      <a href="{$site_root_path}user/?u={$t->author_username}&n={$t->network}&i={$logged_in_user}">
        <img src="{$t->author_avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$t->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon"/>
      </a>
    </div>
    <div class="grid_3 right small">
      <a href="{$site_root_path}user/?u={$t->author_username}&n={$t->network}&i={$logged_in_user}">
        {$t->author_username}
      </a>
    </div>
    <div class="grid_3 right small">
      {$t->author->follower_count|number_format}
    </div>
    <div class="grid_3 right small">
      <a href="{$site_root_path}post/?t={$t->post_id}&n={$t->network}">
        {$t->adj_pub_date|relative_datetime} ago
      </a>
    </div>
    <div class="grid_10 omega">
        {if $t->link->is_image}
          <a href="{$t->link->url}">
            <img src="{$t->link->expanded_url}" style="float:right;background:#eee;padding:5px" />
          </a>
        {/if}
      <div class="post">
          {if $t->post_text}
            {$t->post_text|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames:$i->network_username:$t->network}
          {else}
            <span class="no-post-text">No post text</span>
          {/if}
          {if $t->in_reply_to_post_id}
            <a href="{$site_root_path}post/?t={$t->in_reply_to_post_id}&n={$t->network}">in reply to</a>
          {/if}
        <div class="small gray">
        <span class="metaroll">
            {if $t->network == 'twitter'}
            <a href="http://twitter.com/?status=@{$t->author_username}%20&in_reply_to_status_id={$t->post_id}&in_reply_to={$t->author_username}" target="_blank">Reply</a>
            {/if}
            {if $t->is_geo_encoded < 2}
            Location: 
            {$t->location|truncate:60:' ...'}
            {/if}
        {if $t->author->description}
          <div class="small gray">Description: {$t->author->description}</div>
        {/if}
        {if $t->link->expanded_url}
          <ul>
            <li><a href="{$t->link->expanded_url}" title="{$t->link->expanded_url}">
              {if $t->link->title}{$t->link->title}{else}{$t->link->expanded_url}{/if}
            </a></li>
          </ul>
        {/if}</span>&nbsp;
        </div>
      </div>
      </div>
    </div>
  </div>
</div>