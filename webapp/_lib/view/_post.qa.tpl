{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_1 alpha">&#160;</div>
    <div class="grid_3 right">name</div>
    <div class="grid_12">post</div>
  </div>
{/if}

<div class="individual-tweet post clearfix"{if $smarty.foreach.foo.index % 2 == 1} style="background-color:#EEE"{/if}>

    <div class="grid_1 alpha">
      <img src="{$r.questioner_avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$r.network|get_plugin_path}/assets/img/favicon.ico" class="service-icon"/>
    </div>
    <div class="grid_3 right small">
      {if $r.network == 'twitter'}
      <a {if $reply_count && $reply_count > $top_20_post_min}id="post_username-{$smarty.foreach.foo.iteration}" {/if}
      href="http://twitter.com/{$r.questioner_username}">{$r.questioner_username}</a>
      {else}
      <a href="{$site_root_path}public.php?u={$r.questioner_username|urlencode}&n={$r.network|urlencode}">
        {$r.questioner_username}
      </a>
      {/if}
      {if $r.questioner_follower_count|number_format > 0}
        <div class="small gray">{$r.questioner_follower_count|number_format} followers</div>
      {/if}
    </div>
    <div class="grid_12">
      <div class="post">
        {if $r.question}
           {$r.question|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames:$i->network_username:$r.network}
        {else}
          <span class="no-post-text">No post text</span>
        {/if}
      <div class="small gray">
       <span class="metaroll">
        <a href="{$site_root_path}post/?t={$r.question_post_id}&n={$r.network}">{$r.question_adj_pub_date|relative_datetime} ago</a>
        {if $r.network == 'twitter'}
         - <a href="http://twitter.com/intent/tweet?in_reply_to={$r.question_post_id}">Reply</a>
         - <a href="http://twitter.com/intent/retweet?tweet_id={$r.question_post_id}">Retweet</a>
         - <a href="http://twitter.com/intent/favorite?tweet_id={$r.question_post_id}">Favorite</a>
        {/if}
       </span>&nbsp;</div>
      </div>
    </div>
</div>

<div class="individual-tweet reply clearfix"{if $smarty.foreach.foo.index % 2 == 1} style="background-color:#EEE"{/if}>
  <div class="grid_1 alpha">
      <img src="{$r.answerer_avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$r.network|get_plugin_path}/assets/img/favicon.ico" class="service-icon"/>
    </div>
    <div class="grid_3 right small">
      {if $r.network == 'twitter'}
      <a {if $reply_count && $reply_count > $top_20_post_min}id="post_username-{$smarty.foreach.foo.iteration}" {/if}
      href="http://twitter.com/{$r.answerer_username}">{$r.answerer_username}</a>
      {else}
      <a href="{$site_root_path}public.php?u={$r.questioner_username|urlencode}&n={$r.network|urlencode}">
        {$r.answerer_username}
      </a>
      {/if}
      {if $r.answerer_follower_count|number_format > 0}
        <div class="small gray">{$r.answerer_follower_count|number_format} followers</div>
      {/if}
    </div>
    <div class="grid_12">
      <div class="post">
        {if $r.answer}
          {$r.answer|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames:$i->network_username:$r.network}
        {else}
          <span class="no-post-text">No post text</span>
        {/if}
      <div class="small gray">
       <span class="metaroll">
        <a href="{$site_root_path}post/?t={$r.answer_post_id}&n={$r.network}">{$r.answer_adj_pub_date|relative_datetime} ago</a>
        {if $r.network == 'twitter'}
         - <a href="http://twitter.com/intent/tweet?in_reply_to={$r.answer_post_id}">Reply</a>
         - <a href="http://twitter.com/intent/retweet?tweet_id={$r.answer_post_id}">Retweet</a>
         - <a href="http://twitter.com/intent/favorite?tweet_id={$r.answer_post_id}">Favorite</a>
        {/if}
       </span>&nbsp;</div>
      </div>
    </div>
</div>

