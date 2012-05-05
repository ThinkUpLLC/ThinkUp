{literal}
  <script type="text/javascript">
    $(document).ready(function() {
      function changeMe() {
        var _mu = $("select#instance-select").val();
        if (_mu != "null") {
          document.location.href = _mu;
        }
      }
    });
  </script>
{/literal}

<div id="status-bar" class="clearfix"> 

  <div class="status-bar-left">
    {if $instance}
      <!-- the user has selected a particular one of their instances -->
      {literal}
        <script type="text/javascript">
          function changeMe() {
            var _mu = $("select#instance-select").val();
            if (_mu != "null") {
              document.location.href = _mu;
            }
          }
        </script>
      {/literal}
      
      {if $instances|@count > 1 }
      <span id="instance-selector">
        <select id="instance-select" onchange="changeMe();">
          <option value="">-- Switch service user --</option>
          {foreach from=$instances key=tid item=i}
            {if $i->network_user_id != $instance->network_user_id}
              <option value="{$site_root_path}?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username} - {$i->network|capitalize}</option>
            {/if}
          {/foreach}
        </select>
      </span>
    {/if}
    {else}
      <!-- the user has not selected an instance -->
      {if $crawler_last_run}
      Last update: {$crawler_last_run|relative_datetime} ago
      {/if}
    {/if}
    {if $instance} {if $logged_in_user} {if $instances|@count > 1 } {/if} <a href="{$site_root_path}crawler/updatenow.php{if $developer_log}?log=full{/if}" class="linkbutton">Capture Data</a>{/if}{/if}
  </div> <!-- .status-bar-left -->
  
  <div class="status-bar-right text-right">
    <ul> 
      {if $logged_in_user}
        <li>Logged in as{if $user_is_admin} admin{/if}: {$logged_in_user} {if $user_is_admin}<script src="{$site_root_path}install/checkversion.php"></script>{/if}<a href="{$site_root_path}account/?m=manage" class="linkbutton">Settings</a> <a href="{$site_root_path}session/logout.php" class="linkbutton">Log Out</a></li>
      {else}
      
        <li><a href="http://thinkupapp.com/" class="linkbutton">Get ThinkUp</a> <a href="{$site_root_path}session/login.php" class="linkbutton"    >Log In</a></li>
      {/if}
    </ul>
  </div> <!-- .status-bar-right -->

  
</div> <!-- #status-bar -->

<div id="page-bkgd">

<div class="container clearfix">
  
  <div id="app-title"><a href="{$site_root_path}{$logo_link}">
    <h1><span id="headerthink">Think</span><span id="headerup">Up</span></h1>
  </a></div> <!-- end #app-title -->
  
</div> <!-- end .container -->
