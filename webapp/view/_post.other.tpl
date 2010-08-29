{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_1 alpha">&#160;</div>
    <div class="grid_3 right">name</div>
    <div class="grid_3 right">followers</div>
    <div class="grid_3 right">date</div>
    <div class="grid_12 omega">post</div>
  </div>
{/if}

{if $t->in_reply_to_post_id}
<div class="clearfix" id="locationReplies">
{else}
<div class="clearfix" id="locationRetweets">
{/if}
  <div class="individual-tweet clearfix{if $t->is_protected} private{/if}{if $t->in_reply_to_post_id} reply{/if}
  {if $t->short_location}{$t->short_location|escape:'url'|replace:'%':''|replace:'.':''}{else}__NULL__{/if}">
    <div class="grid_1 alpha">
      <a href="{$site_root_path}user/?u={$t->author_username}&n={$t->network}&i={$smarty.session.network_username}">
        <img src="{$t->author_avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$t->network}/assets/img/favicon.ico" class="service-icon"/>
      </a>
    </div>
    <div class="grid_3 right small">
      <a href="{$site_root_path}user/?u={$t->author_username}&n={$t->network}&i={$smarty.session.network_username}">
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
    <div class="grid_12 omega">
      <div class="tweet-body">
        {if $t->link->is_image}
          <a href="{$t->link->url}">
            <img src="{$t->link->expanded_url}" style="float:right;background:#eee;padding:5px" />
          </a>
        {/if}
        <p>
          {if $t->post_text}
            {$t->post_text|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames:$i->network_username:$t->network}
          {else}
            <span class="no-post-text">No post text</span>
          {/if}
          {if $t->in_reply_to_post_id}
            <a href="{$site_root_path}post/?t={$t->in_reply_to_post_id}&n={$t->network}">in reply to</a>
          {/if}
        </p>
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
        {if $t->author->description}
          <div class="small gray">Description: {$t->author->description}</div>
        {/if}
        {if $t->link->expanded_url}
          <ul>
            <li><a href="{$t->link->expanded_url}" title="{$t->link->expanded_url}">
              {if $t->link->title}{$t->link->title}{else}{$t->link->expanded_url}{/if}
            </a></li>
          </ul>
        {/if}
      </div>
    </div>
  </div>
</div>