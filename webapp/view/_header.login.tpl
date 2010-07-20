{if $statusbar neq 'no'}
  <div id="status-bar" class="clearfix">
    
    <div class="status-bar-left">
      {if $instance}
        <!-- the user has selected a particular one of their instances -->
        {literal}
          <script type="text/javascript">
            $(document).ready(function() {
              $('#choose-instance').click(function() {
                $('#instance-selector').show();
                $('#choose-instance').hide();
              });
              $('#cancel-instance').click(function() {
                $('#instance-selector').hide();
                $('#choose-instance').show();
              });
            });
            function changeMe() {
              var _mu = $("select#instance-select").val();
              if (_mu != "null") {
                document.location.href = _mu;
              }
            }
          </script>
        {/literal}
        Updated: {$instance->crawler_last_run|relative_datetime} ago | 
        <span id="choose-instance"><span class="underline">{$instance->network_username}</span> ({$instance->network|capitalize})</span>
        <span id="instance-selector" style="display:none;">
          <select id="instance-select" onchange="changeMe();">
            <option value="">-- Select an instance --</option>
            {foreach from=$instances key=tid item=i}
              {if $i->network_user_id != $instance->network_user_id}
                <option value="{$site_root_path}?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username} - {$i->network|capitalize} (updated {$i->crawler_last_run|relative_datetime} ago{if !$i->is_active} (paused){/if})</option>
              {/if}
            {/foreach}
          </select>
          <span id="cancel-instance">Cancel</span>
        </span>
      {else}
        <!-- the user has not selected an instance -->
        {if $crawler_last_run}
        Last update: {$crawler_last_run|relative_datetime} ago
        {/if}
      {/if}
    </div> <!-- end .status-bar-left -->
    
    <div class="status-bar-right">
      <ul> 
        {if $logged_in_user}
          <li>Logged in as: {$logged_in_user} | <a href="{$site_root_path}session/logout.php">Log Out</a></li>
        {else}
          <li><a href="{$site_root_path}session/login.php">Log In</a></li>
        {/if}
      </ul>
    </div> <!-- end .status-bar-right -->
  
  </div> <!-- end #status-bar -->
{/if}

<div class="container clearfix">
  
  <div id="app-title"><a href="{$site_root_path}{$logo_link}">
    <h1><span class="bold">Think</span><span class="gray">Tank</span></h1>
    <h2>Ask your friends</h2>
  </a></div> <!-- end #app-title -->
  
  <div id="menu-bar">
    <ul>
      {if $logged_in_user}
        {if $mode eq "public"} <!-- this is the public timeline -->
          <li class="round-tl round-bl"><a href="{$site_root_path}">Private Dashboard</a></li>
        {else}
          {if $instance}<li class="round-tl round-bl"><a href="{$site_root_path}?u=$instance->network_username">{$instance->network_username}{else}Home{/if}</a></li>
          <li><a href="{$site_root_path}public.php">Public Timeline</a></li>
        {/if}
        <li class="round-tr round-br"><a href="{$site_root_path}account/?m=manage">Configuration</a></li>
      {else}
        <li class="round-tr round-br round-tl round-bl"><a href="http://thinktankapp.com/">Get ThinkTank</a></li>
      {/if}
    </ul>
  </div> <!-- end #menu-bar -->
  
</div> <!-- end .container -->
