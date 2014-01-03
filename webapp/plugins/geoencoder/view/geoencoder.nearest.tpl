   {if $post}
      <div class="clearfix alert stats">
        <div class="grid_2 alpha">
        <div class="avatar-container">
          <img src="{$post->author_avatar}" class="avatar2"/><i class="fa fa-{$post->network}"></i>
         </div>
        </div>
        <div class="{if $retweets}grid_12{else}grid_16{/if}">
          <span class="tweet">
            {if $post->post_text}
              {$post->post_text|link_usernames_to_twitter}
            {else}
              <span class="no-post-text">No post text</span>
            {/if}
          </span>
          {if $post->link->expanded_url and !$post->link->image_src and $post->link->expanded_url != $post->link->url}
            <br><a href="{$post->link->expanded_url}" title="{$post->link->expanded_url}">
              {$post->link->expanded_url}
            </a>
          {/if}
          <div class="grid_6 omega small gray prefix_10">
            {if $post->network eq 'twitter'}
            Posted at <a href="http://twitter.com/{$post->author_username}/statuses/{$post->post_id}">{$post->adj_pub_date}</a>{if $post->source} via {$post->source}{/if}<br>
            {else}
            Posted at {$post->adj_pub_date}{if $post->source} via {$post->source}{/if}<br>
            {/if}
            {if $post->location}From: {$post->location}{/if}
              </div>
            </div>
          </div>
        {/if}
 {if $geoencoder_nearest}
   <div class="append_20 clearfix section">
   <h2>Nearest Replies</h2>
     {foreach from=$geoencoder_nearest key=tid item=t name=foo}
        {if  $smarty.foreach.foo.index > 1}
       {include file="_post.author_no_counts.tpl" post=$t sort='no' show_distance='true' scrub_reply_username='true' unit=$geoencoder_options.distance_unit->option_value}
       {/if}
     {/foreach}
   </div>
{else}
    {assign var='error_msg' value="This post has not been geoencoded yet; cannot display posts by location."}
    {include file="_usermessage.tpl"}
{/if}
