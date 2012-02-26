{if $smarty.foreach.foo.first}

  <div class="header clearfix">
    <div class="grid_2 alpha">&#160;</div>
    <div class="grid_3">&#160;</div>
    <div class="grid_8">&#160;</div>
    <div class="grid_2 center">
      {if $t->network eq 'twitter'}retweets{else}{if $t->network eq 'google+'}+1s{else}likes{/if}{/if}
    </div>
    <div class="grid_2 center omega">
      replies
    </div>
  </div>
{/if}

<div class="clearfix article">
<div class="individual-tweet post clearfix{if $t->is_protected} private{/if}">
    <div class="grid_2 alpha">
      <div class="avatar-container">
        <img src="{$t->author_avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$t->network|get_plugin_path}/assets/img/favicon.png" class="service-icon"/>
        {if $t->is_reply_by_friend or $t->is_retweet_by_friend}
           <div class="small gray">Friend</div>
        {/if}
      </div>
    </div>
    <div class="grid_3 small">
      {if $t->network == 'twitter' && $username_link != 'internal'}
      <a {if $reply_count && $reply_count > $top_20_post_min}id="post_username-{$smarty.foreach.foo.iteration}" {/if}
      href="https://twitter.com/intent/user?user_id={$t->author_user_id}">{$t->author_username}</a>
      {else}
        {$t->author_username}
      {/if}
      {if $t->author->follower_count > 0}
        <div class="small gray">{$t->author->follower_count|number_format} followers</div>
      {/if}
        </div>
    <div class="grid_8">
      <div class="post">
        {if $t->post_text}
          {if $scrub_reply_username}
            {if $reply_count && $reply_count > $top_20_post_min}
                <div class="reply_text" id="reply_text-{$smarty.foreach.foo.iteration}">
            {/if} 
            {$t->post_text|filter_xss|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames_to_twitter}
            {if $reply_count && $reply_count > $top_20_post_min}</div>{/if}
          {else}
           {if $t->network == 'google+'}
            {$t->post_text}
           {else}
            {$t->post_text|filter_xss|link_usernames_to_twitter}
            {/if}
          {/if}
        {/if}
      {if $t->link->expanded_url}
        {if $t->post_text != ''}<br>{/if}
        {if $t->link->image_src}
         <div class="pic" style="float:left;margin-right:5px;margin-top:5px;"><a href="{$t->link->expanded_url}"><img src="{$t->link->image_src}" style="margin-bottom:5px;"/></a></div>
        {/if}
         <span class="small"><a href="{$t->link->url}" title="{$t->link->expanded_url}">{if $t->link->title}{$t->link->title}{else}{$t->link->url}{/if}</a>
        {if $t->link->description}<br><small>{$t->link->description}</small>{/if}</span>
      {/if}
        
        {if !$post && $t->in_reply_to_post_id }
          <a href="{$site_root_path}post/?t={$t->in_reply_to_post_id}&n={$t->network|urlencode}"><span class="ui-icon ui-icon-arrowthick-1-w" title="reply to..."></span></a>
        {/if}

      <span class="small gray">
        <br clear="all">
       <span class="metaroll">
          <a href="{$site_root_path}post/?t={$t->post_id}&n={$t->network|urlencode}">{$t->adj_pub_date|relative_datetime} ago</a>
          {if $t->is_geo_encoded < 2}
            {if $show_distance}
                {if $unit eq 'km'}
                  {$t->reply_retweet_distance|number_format} kms away
                  {else}
                  {$t->reply_retweet_distance|number_format} miles away in 
                {/if}
            {/if}
           from {$t->location|truncate:60:' ...'}
          {/if}
          {if $t->network == 'twitter'}
          <a href="http://twitter.com/intent/tweet?in_reply_to={$t->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-w" title="reply"></a>
          <a href="http://twitter.com/intent/retweet?tweet_id={$t->post_id}"><span class="ui-icon ui-icon-arrowreturnthick-1-e" title="retweet"></a>
          <a href="http://twitter.com/intent/favorite?tweet_id={$t->post_id}"><span class="ui-icon ui-icon-star" title="favorite"></a>
          {/if}
       </span><br>&nbsp;
      </span>
 
      </div>
    </div>
    <div class="grid_2 center">
    {if $t->network eq 'twitter'}
      {if $t->all_retweets > 0}
        <span class="reply-count"><a href="{$site_root_path}post/?t={$t->post_id}&n={$t->network|urlencode}&v=fwds">{$t->all_retweets|number_format}{if $t->rt_threshold}+{/if}<!-- retweet{if $t->retweet_count_cache eq 1}{else}s{/if}--></a></span>
      {/if}
    {else}
        {if $t->favlike_count_cache > 0}
        <span class="reply-count">
            <a href="{$site_root_path}post/?t={$t->post_id}&n={$t->network|urlencode}&v=likes">{$t->favlike_count_cache|number_format}</a>
        </span>
        {else}&nbsp;{/if}
    {/if}
    
    </div>
    <div class="grid_2 center omega">
      {if $t->reply_count_cache > 0}
        <span class="reply-count">
        <a href="{$site_root_path}post/?t={$t->post_id}&n={$t->network|urlencode}">{$t->reply_count_cache|number_format}<!-- repl{if $t->reply_count_cache eq 1}y{else}ies{/if}--></a></span>
      {else}
        &#160;
      {/if}
    </div>
  </div>
</div>