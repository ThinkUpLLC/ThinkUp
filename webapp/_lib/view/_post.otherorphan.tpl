{if $smarty.foreach.foo.first}
<!--  <div class="header clearfix">
    <div class="grid_1 alpha">&#160;</div>
    <div class="grid_3 right">name</div>
    <div class="grid_16 omega">post</div>
  </div>-->
{/if}

<div class="individual-tweet clearfix{if $t->is_protected} private{/if}">
  <div class="grid_1 alpha">
    <a href="{$site_root_path}user/?u={$t->author_username}&n={$t->network}&i={$logged_in_user}"><img src="{$t->author_avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$t->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon"/></a>
  </div>
  <div class="grid_3 right small">
    <a href="{$site_root_path}user/?u={$t->author_username}&n={$t->network}&i={$logged_in_user}">{$t->author_username}</a><br>
    <span class="small gray">{$t->author->follower_count|number_format} followers</span>
  </div>
  <div class="grid_16 omega">
      {if $t->link->is_image}
        <div class="pic"><a href="{$t->link->url}"><img src="{$t->link->expanded_url}" alt=""></a></div>
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
      {if $t->link->expanded_url}
        <a href="{$t->link->expanded_url}" title="{$t->link->expanded_url}">{$t->link->title}</a>
      {/if}
        <div class="small gray">
          <span class="metaroll"><a href="{$site_root_path}post/?t={$t->post_id}&n={$t->network}">{$t->adj_pub_date|relative_datetime} ago</a>
          {$t->author->location}
          {if $t->network == 'twitter'}
           - <a href="http://twitter.com/intent/tweet?in_reply_to={$t->post_id}">Reply</a>
           - <a href="http://twitter.com/intent/retweet?tweet_id={$t->post_id}">Retweet</a>
           - <a href="http://twitter.com/intent/favorite?tweet_id={$t->post_id}">Favorite</a>
          {/if}
          </span>&nbsp;
        </div>
        <div class="small gray">
          <span class="metaroll"> {$t->author->description|truncate:100}</span>&nbsp;
        </div>
          
      {if $logged_in_user}
      <div id="div{$t->post_id}">
        <form action="" class="post-setparent">
          <select name="pid{$t->post_id}" id="pid{$t->post_id}" onselect> <!-- what is this onselect? -->
            <option disabled="disabled">Is in reply to...</option>
            <option value="0">No particular post (standalone)</option>
            {foreach from=$all_tweets key=aid item=a}
              <option value="{$a->post_id}">{$a->post_text|truncate_for_select}</option>
            {/foreach}
          </select>
          <input type="submit" name="submit" class="button" id="{$t->post_id}" value="Save">
        </form>
      </div>
      {/if}
    </div>
  </div>
</div>