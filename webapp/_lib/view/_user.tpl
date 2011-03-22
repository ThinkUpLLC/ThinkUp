<!--
{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_2 alpha">&#160;</div>
    <div class="grid_4">&#160;</div>
    <div class="grid_12 omega">detail</div>
  </div>
{/if}
-->
<div class="individual-tweet prepend_20 clearfix{if $t.is_protected} private{/if}">
  <div class="grid_2 alpha">
    <div class="avatar-container">    
      <a href="{$site_root_path}user/?u={$f.user_name}&n={$instance->network}&i={$instance->network_username}"><img src="{$f.avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$f.network|get_plugin_path}/assets/img/favicon.ico" class="service-icon"/></a>
    </div>
  </div>
  <div class="grid_4 small">
    <a href="{$site_root_path}user/?u={$f.user_name}&n={$instance->network}&i={$instance->network_username}">{$f.user_name}</a>
    <div class="small gray">
      {$f.follower_count} followers<br>
      {$f.friend_count} friends<br>
    </div>
  </div>
  <div class="grid_12 omega">
    {if $f.description}<p>{$f.description}</p>{else}&#160;{/if}
    <span class="small gray">
      {if $f.location}{$f.location}{/if}
      {$f.avg_tweets_per_day|number_format} posts per day over the past {$f.joined|relative_datetime}
      {if $f.follower_count > $f.friend_count and $f.friend_count > 0}
        {assign var='follower' value=`$f.follower_count/$f.friend_count`}
        <br>{$follower|number_format}x more followers than friends
      {/if}
    </span>
  </div>
</div>
