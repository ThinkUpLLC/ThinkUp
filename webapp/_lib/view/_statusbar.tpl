{if $enable_bootstrap eq 1}

    <div class="navbar navbar-static-top">
      <div class="navbar-inner">
        <div class="container">

          <a href="{$site_root_path}" class="brand span3"><span style="color : #00AEEF; font-weight : 800;">Think</span><span style="color : black; font-weight : 200;">Up</span></a>


            <a class="btn btn-navbar pull-right" data-toggle="collapse" data-target=".nav-collapse"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></a>

            {if $logged_in_user && !$smarty.get.m && !$smarty.get.p && $instances}

                <!--search posts-->
                <form class="navbar-search pull-left dropdown" method="get" action="javascript:searchMe('{$site_root_path}search.php?u={$instances[0]->network_username|urlencode}&n={$instances[0]->network|urlencode}&c=posts&q=');">

                    <input type="text" id="search-keywords" class="search-query span4 dropdown-toggle" data-toggle="dropdown" autocomplete="off" {if $smarty.get.q}value="{$smarty.get.q}"{else}placeholder="Search"{/if} />

                    <ul id="search-refine" class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                    {foreach from=$instances key=tid item=i}
                        <li class="lead"><a onclick="searchMe('{$site_root_path}search.php?u={$i->network_username|urlencode}&n={$i->network|urlencode}&c=posts&q=');" href="#"><i class="icon-{$i->network}{if $i->network eq 'google+'} icon-google-plus{/if} icon-muted icon-2x"></i> Find <span class="searchterm"></span> in {if $i->network eq 'twitter'}@{/if}{$i->network_username}'s {if $i->network eq 'twitter'}tweets{elseif $i->network eq 'foursquare'}Foursquare check-ins{else}{$i->network|ucwords} posts{/if}</a></li>
                        {if $i->network eq 'twitter'}
                            <li class="lead"><a onclick="searchMe('{$site_root_path}search.php?u={$i->network_username|urlencode}&n=twitter&c=followers&q=');" href="#"><i class="icon-twitter icon-muted icon-2x"></i> Search @{$i->network_username}'s followers' bios for <span class="searchterm"></span></a></li>
                            <li class="lead"><a onclick="searchMe('{$site_root_path}search.php?u={$i->network_username|urlencode}&n=twitter&c=followers&q=name:');" href="#"><i class="icon-twitter icon-muted icon-2x"></i> Search @{$i->network_username}'s followers for people named <span class="searchterm"></span></a></li>
                        {/if}
                    {/foreach}
                    {foreach from=$saved_searches key=tid item=i}
                        <li class="lead"><a onclick="searchMe('{$site_root_path}search.php?u={$i.network_username|urlencode}&n=twitter&c=searches&k={$i.hashtag|urlencode}&q=');" href="#"><i class="icon-twitter icon-muted icon-2x"></i> Search tweets which contain {$i.hashtag} for <span class="searchterm"></span></a></li>
                    {/foreach}
                    </ul>

                </form>

            {/if}


            <div class="nav-collapse">

      {if $logged_in_user}

<ul class="nav pull-right" style="border-left : none;">

    {if $user_is_admin}<li><script src="{$site_root_path}install/checkversion.php"></script></li>{/if}
    <li><a href="#" id="notify-insights" title="Enable desktop notifications of new insights!" style="display:none;"><i class="icon-bell"></i></a></li>
    <li><a href="{$site_root_path}crawler/updatenow.php{if $developer_log}?log=full{/if}" id="refresh-data" title="Refresh data"><i class="icon-refresh"></i></a></li>

    <li class="dropdown">
        <a href="#" class="dropdown-toggle hidden-phone" data-toggle="dropdown">
          {$logged_in_user}{if $user_is_admin} <span class="label label-info">admin</span>{/if}
          <b class="caret"></b>
        </a>
        <ul class="dropdown-menu">
          <li class="{if $smarty.get.m eq "manage"}active{/if}"><a href="{$site_root_path}account/?m=manage"><i class="icon-cog icon-muted"></i> Settings</a></li>
          <li><a href="{$site_root_path}session/logout.php"><i class="icon-signout icon-muted"></i> Log Out</a></li>
        </ul>
    </li>
</ul> 

      {else}
<ul class="nav pull-right">
    <li><a href="http://thinkup.com/" >Get ThinkUp</a></li>
    <li><a href="{$site_root_path}session/login.php" ><i class="icon-signin icon-muted"></i> Log In</a></li>
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
      {if $instances|@count} 
        <li><a href="{$site_root_path}" class="linkbutton" style="background: #31C22D;color:white;">Insights (New!)</a></li>
      {/if}
      {if $logged_in_user}
        <li>Logged in as{if $user_is_admin} admin{/if}: {$logged_in_user} {if $user_is_admin}<script src="{$site_root_path}install/checkversion.php"></script>{/if}<a href="{$site_root_path}account/?m=manage" class="linkbutton">Settings</a> <a href="{$site_root_path}session/logout.php" class="linkbutton">Log Out</a></li>
        <script>var logged_in_user = '{$logged_in_user}';</script>
      {else}
      
        <li><a href="http://thinkup.com/" class="linkbutton">Get ThinkUp</a> <a href="{$site_root_path}session/login.php" class="linkbutton"    >Log In</a></li>
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
