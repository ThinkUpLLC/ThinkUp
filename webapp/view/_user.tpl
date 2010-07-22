{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_1 alpha">&#160;</div>
    <div class="grid_3 right">name</div>
    <div class="grid_3 right">followers</div>
    <div class="grid_3 right">friends</div>
    <div class="grid_12 omega">detail</div>
  </div>
{/if}

<div class="individual-tweet clearfix{if $t.is_protected} private{/if}">
  <div class="grid_1 alpha">
    <a href="{$site_root_path}user/?u={$f.user_name}&n={$t->network}&i={$smarty.session.network_username}"><img src="{$f.avatar}" class="avatar" alt="{$smarty.session.network_username}"></a>
  </div>
  <div class="grid_3 small right">
    <a href="{$site_root_path}user/?u={$f.user_name}&n={$t->network}&i={$smarty.session.network_username}">{$f.user_name}</a>
  </div>
  <div class="grid_3 small right">
    {$f.follower_count|number_format}
  </div>
  <div class="grid_3 small right">
    {$f.friend_count|number_format}
  </div>
  <div class="grid_12 omega">
    <p>{if $f.description}{$f.description}{else}&#160;{/if}</p>
    <div class="small gray">Averages {$f.avg_tweets_per_day} updates per day; {$f.post_count|number_format} total.</div>
    {if $f.tweet_count > 0}<div class="small gray">Last post: {$f.last_post|relative_datetime}</div>{/if}
    {if $f.location}<div class="small gray">Location: {$f.location}</div>{/if}
    <div class="small gray">Joined: {$f.joined|relative_datetime} on {$f.joined|date_format:"%D"}</div>
  </div>
</div>
