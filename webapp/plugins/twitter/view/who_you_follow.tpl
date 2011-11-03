{if $chatterboxes|@count >1}
    <h2>Chatterboxes</h2>
    {foreach from=$chatterboxes key=tid item=u name=foo}
      <div class="avatar-container" style="float:left;margin:7px;">
        <a href="https://twitter.com/intent/user?user_id={$u.user_id}" title="{$u.user_name}"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.ico" class="service-icon2"/></a>
      </div>
    {/foreach}
    <div align="right" style="clear:all;padding-top:60px"><a href="index.php?v=friends-mostactive&u={$instance->network_username}&n=twitter">More...</a></div>
{else}
        No users to display. {if $logged_in_user}Update your data and try again.{/if}
{/if}

{if $deadbeats|@count >1}
    <h2>Deadbeats</h2>
    {foreach from=$deadbeats key=tid item=u name=foo}
      <div class="avatar-container" style="float:left;margin:7px;">
        <a href="https://twitter.com/intent/user?user_id={$u.user_id}" title="{$u.user_name}"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.ico" class="service-icon2"/></a>
      </div>
    {/foreach}
    <div align="right" style="clear:all;padding-top:60px"><a href="index.php?v=friends-leastactive&u={$instance->network_username}&n=twitter">More...</a></div>
{/if}

{if $popular|@count >1}
    <h2>Popular</h2>
    {foreach from=$popular key=tid item=u name=foo}
      <div class="avatar-container" style="float:left;margin:7px;">
        <a href="https://twitter.com/intent/user?user_id={$u.user_id}" title="{$u.user_name}"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.ico" class="service-icon2"/></a>
      </div>
    {/foreach}
    <div align="right" style="clear:all;padding-top:60px"><a href="index.php?v=friends-mostfollowed&u={$instance->network_username}&n=twitter">More...</a></div>
{/if}