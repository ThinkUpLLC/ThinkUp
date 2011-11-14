<div class="individual-tweet prepend_20 clearfix{if $t.is_protected} private{/if} article">
  <div class="grid_2 alpha">
    <div class="avatar-container">
        {if $f.network == 'twitter'}<a href="https://twitter.com/intent/user?user_id={$f.user_id}" title="{$f.user_name} on Twitter">{/if}
      <img src="{$f.avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$f.network|get_plugin_path}/assets/img/favicon.png" class="service-icon"/>
      {if $f.network == 'twitter'}</a>{/if}
    </div>
  </div>
  <div class="grid_4 small">
    {if $f.network == 'twitter'}<a href="https://twitter.com/intent/user?user_id={$f.user_id}" title="{$f.user_name} on Twitter">{/if}
    {$f.user_name}
    {if $f.network == 'twitter'}</a>{/if}
    <div class="small gray">
      {if $f.follower_count > 0 && $f.friend_count > 0}
      {$f.follower_count|number_format} followers, {$f.friend_count|number_format} friends<br>
      {/if}
      {if $f.network eq 'twitter'}<a href="https://twitter.com/intent/user?user_id={$f.user_id}" title="{$f.user_name} on Twitter"><span class="sprite ui-icon-person"></span></a>{/if}
    </div>
  </div>
  <div class="grid_12 omega">
    {if $f.description}<p>{$f.description}</p>{else}&#160;{/if}
    <span class="small gray">
      {if $f.location}{$f.location}{/if}
      {if $f.avg_tweets_per_day >0}{$f.avg_tweets_per_day|number_format} posts per day over the past {$f.joined|relative_datetime}{/if}
      {if $f.follower_count > $f.friend_count and $f.friend_count > 0}
        {assign var='follower' value=`$f.follower_count/$f.friend_count`}
        <br>{$follower|number_format}x more followers than friends
      {/if}
    </span>
  </div>
</div>
