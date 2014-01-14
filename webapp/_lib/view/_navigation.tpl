
    <div id="menu">
      <ul class="list-unstyled menu-options">
        <li><a href="{$site_root_path}" {if !$controller_title}class="active"{/if}>Home</a></li>

      {if !isset($thinkupllc_endpoint)} 
        <li><a href="{$site_root_path}account/?m=manage"{if $smarty.get.m eq "manage"} class="active"{/if}>Settings</a></li>
      {/if}

  {if isset($logged_in_user)}
        <li class="service"><a href="{$site_root_path}account/?p=facebook" class="{if $smarty.get.p eq 'facebook'} active{/if}">Facebook <i class="fa fa-check-circle icon"></i></a></li>
        <li class="service error"><a href="{$site_root_path}account/?p=twitter" class="service error{if $smarty.get.p eq 'twitter'} active{/if}">Twitter <i class="fa fa-exclamation-triangle icon"></i></a></li>
        <!--
        <li class="service inactive"><a href="{$site_root_path}account/?p=instagram">Instagram <i class="fa fa-instagram icon"></i></a></li>
        -->

      {if !isset($thinkupllc_endpoint)} 

        <li><a href="{$site_root_path}account/?m=manage#plugins"><i class="fa fa-list-alt text-muted"></i> Plugins</a></li>
        {if $user_is_admin}
        <li><a id="app-settings-tab" href="{$site_root_path}account/?m=manage#app_settings"><i class="fa fa-cogs text-muted"></i> Application </a></li>
        {/if}
        <li><a href="{$site_root_path}account/?m=manage#instances"><i class="fa fa-lock text-muted"></i> Account </a></li>
        {if $user_is_admin}
        <li><a href="{$site_root_path}account/?m=manage#ttusers"><i class="fa fa-group text-muted"></i> Users </a></li>
        {/if}
        <li><a href="{$site_root_path}crawler/updatenow.php{if $developer_log}?log=full{/if}" id="refresh-data" title="Refresh data"><i class="fa fa-refresh text-muted"></i> Refresh data</a></li>
      {else}
        <li><a href="https://thinkup.com/user/">Membership</a></li>
      {/if}

  {/if}

<!-- 
        <li><a href="#">Subscription</a></li>
        <li><a href="#">Help</a></li>

 -->
  {if isset($logged_in_user)}
        <li class="user-info logged-in">
          <img src="http://avatars.io/{$instances[0]->network}/{$instances[0]->network_user_id}" class="user-photo img-circle">
          <div class="current-user">
            <div class="label">Logged in as</div>
            {$logged_in_user}
          </div>
        </li>
        <li><a href="{$site_root_path}session/logout.php">Log out</a></li>
  {else}
        <li><a href="{$site_root_path}session/login.php">Log in</a></li>
  {/if}
      </ul>
    </div>

    <div id="page-content">
      <nav class="navbar navbar-default" role="navigation">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button class="btn menu-trigger">
            <i class="fa fa-bars"></i>
          </button>
          <a class="navbar-brand" href="{$site_root_path}"><strong>Think</strong>Up</span></a>
        </div>
      </nav>    
