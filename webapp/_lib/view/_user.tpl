{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_1 alpha">&#160;</div>
    <div class="grid_3 right">name</div>
    <div class="grid_10 omega">detail</div>
  </div>
{/if}

<div class="individual-tweet clearfix{if $t.is_protected} private{/if}">
  <div class="grid_1 alpha">    <a href="{$site_root_path}user/?u={$f.user_name}&n={$instance->network}&i={$instance->network_username}"><img src="{$f.avatar}" class="avatar"/><img src="{$site_root_path}plugins/{$f.network|get_plugin_path}/assets/img/favicon.ico" class="service-icon"/></a>
  </div>
  <div class="grid_3 small right">
    <a href="{$site_root_path}user/?u={$f.user_name}&n={$instance->network}&i={$instance->network_username}">{$f.user_name}</a>
  </div>
  <div class="grid_16 omega">
    <p>{if $f.description}{$f.description}{else}&#160;{/if}{if $f.location}<span class="small gray"> {$f.location}</span>{/if}</p>
    <div class="small gray"></div>
    <div class="small gray">{$f.avg_tweets_per_day|number_format} posts per day over the past {$f.joined|relative_datetime}</div>
    {if $f.follower_count > $f.friend_count and $f.friend_count > 0}
        {assign var='follower' value=`$f.follower_count/$f.friend_count`}
        <div class="small gray">{$follower|number_format}x more followers than friends</div>
    {/if}
  </div>
</div>
