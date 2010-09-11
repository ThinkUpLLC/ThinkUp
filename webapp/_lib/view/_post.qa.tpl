{if $smarty.foreach.foo.first}
  <div class="header clearfix">
    <div class="grid_1 alpha">&#160;</div>
    <div class="grid_3 right">name</div>
    <div class="grid_3 right">followers</div>
    <div class="grid_3 right">date</div>
    <div class="grid_11 omega">post</div>
  </div>
{/if}

<div class="individual-tweet post clearfix"{if $smarty.foreach.foo.index % 2 == 1} style="background-color:#EEE"{/if}>
  <div class="grid_1 alpha">
    <a href="{$site_root_path}user/?u={$r.questioner_username}&n={$r.network}&i={$logged_in_user}"><img src="{$r.questioner_avatar}" class="avatar" alt="{$logged_in_user}"/><img src="{$site_root_path}plugins/{$r.network|get_plugin_path}/assets/img/favicon.ico" class="service-icon"/></a>
  </div>
  <div class="grid_3 right small">
    <a href="{$site_root_path}user/?u={$r.questioner_username}&n={$r.network}&i={$logged_in_user}">{if $r.questioner_username eq $i->network_username}You{else}{$r.questioner_username}{/if}</a>
  </div>
  <div class="grid_3 right small">
    {$r.questioner_follower_count|number_format}
  </div>
  <div class="grid_3 right small">
    <a href="{$site_root_path}post/?t={$r.question_post_id}&n={$r.network}">{$r.question_adj_pub_date|relative_datetime}</a>
  </div>
  <div class="grid_12 omega">
    <p>{$r.question|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames:$i->network_username:$r.network}</p>
    {if $r.location}
      <div class="small gray">{$r.location}</div>
    {/if}
    {if $r.description}
      <div class="small gray">{$r.description}</div>
    {/if}
  </div>
</div>

<div class="individual-tweet reply clearfix"{if $smarty.foreach.foo.index % 2 == 1} style="background-color:#EEE"{/if}>
  <div class="grid_1 alpha">
    <a href="{$site_root_path}user/?u={$r.answerer_username}&n={$r.network}&i={$logged_in_user}"><img src="{$r.answerer_avatar}" class="avatar" alt="{$logged_in_user}"/><img src="{$site_root_path}plugins/{$r.network|get_plugin_path}/assets/img/favicon.ico" class="service-icon"/></a>
  </div>
  <div class="grid_3 right small">
    <a href="{$site_root_path}user/?u={$r.answerer_username}&n={$r.network}&i={$logged_in_user}">{if $r.answerer_username eq $i->network_username}You{else}{$r.answerer_username}{/if}</a>
  </div>
  <div class="grid_3 right small">
    {$r.answerer_follower_count|number_format}
  </div>
  <div class="grid_3 right small">
     <a href="{$site_root_path}post/?t={$r.answer_post_id}&n={$r.network}">{$r.answer_adj_pub_date|relative_datetime}</a>
  </div>
  <div class="grid_12 omega">
    <p>{$r.answer|regex_replace:"/^@[a-zA-Z0-9_]+/":""|link_usernames:$i->network_username:$r.network}</p>
    {if $r.location}
      <div class="small gray">{$r.location}</div>
    {/if}
  </div>
</div>