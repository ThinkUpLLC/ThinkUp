{if $chatterboxes|@count >1}
	<div class="section">
    <h2>Chatterboxes</h2>
    <div class="article" style="padding-left : 0px; padding-top : 0px;">
    {foreach from=$chatterboxes key=tid item=u name=foo}
      <div class="avatar-container" style="float:left;margin:7px;">
        <a href="http://twitter.com/{$u.user_name}" title="{$u.user_name}"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.ico" class="service-icon2"/></a>
      </div>
    {/foreach}
    <br /><br /><br />
    </div>
    <div class="view-all"><a href="index.php?v=friends-mostactive&u={$instance->network_username}&n=twitter">More...</a></div>
	</div>
{else}
        <div class="alert urgent">No users to display. {if $logged_in_user}Update your data and try again.{/if}</div>
{/if}

{if $deadbeats|@count >1}
	<div class="section">
		<h2>Deadbeats</h2>
		<div class="article" style="padding-left : 0px; padding-top : 0px;">
		{foreach from=$deadbeats key=tid item=u name=foo}
		  <div class="avatar-container" style="float:left;margin:7px;">
			<a href="http://twitter.com/{$u.user_name}" title="{$u.user_name}"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.ico" class="service-icon2"/></a>
		  </div>
		{/foreach}
		<br /><br /><br />
		</div>
		<div class="view-all"><a href="index.php?v=friends-leastactive&u={$instance->network_username}&n=twitter">More...</a></div>
	</div>
{/if}

{if $popular|@count >1}
	<div class="section">
		<h2>Popular</h2>
		<div class="article" style="padding-left : 0px; padding-top : 0px;">
		{foreach from=$popular key=tid item=u name=foo}
		  <div class="avatar-container" style="float:left;margin:7px;">
			<a href="http://twitter.com/{$u.user_name}" title="{$u.user_name}"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.ico" class="service-icon2"/></a>
		  </div>
		{/foreach}
		<br /><br /><br />
		</div>
		<div class="view-all"><a href="index.php?v=friends-mostfollowed&u={$instance->network_username}&n=twitter">More...</a></div>
	</div>
{/if}