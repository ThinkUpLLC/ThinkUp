{if $enable_bootstrap}

    <div class="navbar navbar-static-top">
      <div class="navbar-inner">
        <div class="container">

          <a href="{$site_root_path}" class="brand"><span style="color : #00AEEF; font-weight : 800;">Think</span><span style="color : black; font-weight : 200;">Up</span></a>
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></a>

          <div class="nav-collapse">

      {if $logged_in_user}

<!--search posts-->

<ul class="nav pull-right" style="border-left : none;">

    	<form class="navbar-search">
    		<input type="text" id="search-keywords" class="search-query" placeholder="Search" />
        	
        </form>
    <li class="dropdown">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown" style="border-left : none;"><i class="icon-search"></i></a>
        <ul class="dropdown-menu">
        {foreach from=$instances key=tid item=i}
            <li><a onclick="searchMe('{$site_root_path}search.php?u={$i->network_username}&n={$i->network}&q=');" href="#">{if $i->network eq 'twitter'}@{/if}{$i->network_username}'s {if $i->network eq 'twitter'}tweets{elseif $i->network eq 'foursquare'}checkins{else}{$i->network|ucwords} posts{/if}</a></li>
        {/foreach}
        </ul>
     </li>
  {literal}
    <script type="text/javascript">
      function searchMe(_baseu) {
        var _mu = $("input#search-keywords").val();
        if (_mu != "null") {
          document.location.href = _baseu + _mu;
        }
      }
    </script>
  {/literal}

    {if $user_is_admin}<li><script src="{$site_root_path}install/checkversion.php"></script></li>{/if}
    <li><a href="{$site_root_path}crawler/updatenow.php{if $developer_log}?log=full{/if}" id="refresh-data"><i class="icon-refresh"></i></a></li>

    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
          {$logged_in_user}{if $user_is_admin} <span class="label label-info">admin</span>{/if}
          <b class="caret"></b>
        </a>
        <ul class="dropdown-menu">
          <li class="{if $smarty.get.m eq "manage"}active{/if}"><a href="{$site_root_path}account/?m=manage">Settings</a></li>
          <li><a href="{$site_root_path}session/logout.php">Log Out</a></li>
        </ul>
    </li>
</ul> 

    

  
      {else}
<ul class="nav pull-right">
    <li><a href="http://thinkupapp.com/" >Get ThinkUp</a></li>
    <li><a href="{$site_root_path}session/login.php" >Log In</a></li>
</ul>
      {/if}
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

{else}

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
              <option value="{$site_root_path}dashboard.php?u={$i->network_username|urlencode}&n={$i->network|urlencode}">{$i->network_username} - {$i->network|capitalize}</option>
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
        <li><a href="{$site_root_path}" class="linkbutton" style="background: #31C22D;color:white;">Insights (New!)</a></li>
      {if $logged_in_user}
        <li>Logged in as{if $user_is_admin} admin{/if}: {$logged_in_user} {if $user_is_admin}<script src="{$site_root_path}install/checkversion.php"></script>{/if}<a href="{$site_root_path}account/?m=manage" class="linkbutton">Settings</a> <a href="{$site_root_path}session/logout.php" class="linkbutton">Log Out</a></li>
        <script>var logged_in_user = '{$logged_in_user}';</script>
      {else}
      
        <li><a href="http://thinkupapp.com/" class="linkbutton">Get ThinkUp</a> <a href="{$site_root_path}session/login.php" class="linkbutton"    >Log In</a></li>
      {/if}
    </ul>
  </div> <!-- .status-bar-right -->

  
</div> <!-- #status-bar -->

<div id="page-bkgd">

<div class="container clearfix">
  
  <div id="app-title"><a href="{$site_root_path}">
    <h1><span id="headerthink">Think</span><span id="headerup">Up</span></h1>
  </a></div> <!-- end #app-title -->
  
</div> <!-- end .container -->

{/if}
