
    <div id="menu">
      <ul class="list-unstyled menu-options">
        <li><a href="{$site_root_path}" {if !$controller_title}class="active"{/if}>Home</a></li>

  {if isset($logged_in_user)}
        <li class="service {$facebook_connection_status}"><a href="{$site_root_path}account/?p=facebook" {if $smarty.get.p eq 'facebook'}class="active"{/if}>Facebook<i class="fa fa-{if $facebook_connection_status eq 'active'}check-circle{elseif $facebook_connection_status eq 'error'}exclamation-triangle{else}facebook-square{/if} icon"></i></a></li>
        <li class="service {$twitter_connection_status}"><a href="{$site_root_path}account/?p=twitter" {if $smarty.get.p eq 'twitter'}class="active"{/if}>Twitter<i class="fa fa-{if $twitter_connection_status eq 'active'}check-circle{elseif $twitter_connection_status eq 'error'}exclamation-triangle{else}twitter{/if} icon"></i></a></li>
        <li class="service {$instagram_connection_status}"><a href="{$site_root_path}account/?p=instagram" {if $smarty.get.p eq 'instagram'}class="active"{/if}>Instagram <i class="fa fa-{if $instagram_connection_status eq 'active'}check-circle{elseif $instagram_connection_status eq 'error'}exclamation-triangle{else}instagram{/if} icon"></i></a></li>
      {if !isset($thinkupllc_endpoint)}

        <li class="service"><a href="{$site_root_path}account/?m=manage"{if $smarty.get.m eq "manage"} class="active"{/if}>Settings<i class="fa fa-cogs icon"></i></a></li>
        <li class="service"><a href="{$site_root_path}crawler/updatenow.php{if $developer_log}?log=full{/if}" id="refresh-data" title="Refresh data">Refresh data <i class="fa fa-refresh icon"></i></a></li>
      {else}
        <li><a href="{$thinkupllc_endpoint}settings.php">Settings</a></li>
        <li><a href="{$thinkupllc_endpoint}membership.php">Membership</a></li>
      {/if}

  {/if}

  {if isset($logged_in_user)}
        <li class="user-info logged-in">
          <img src="https://www.gravatar.com/avatar/{$logged_in_user|lower|md5}" class="user-photo img-circle">
          <div class="current-user">
            <div class="label">Logged in as</div>
            {$logged_in_user}
          </div>
        </li>
        <li><a href="{$site_root_path}session/logout.php">Log out</a></li>
  {else}
        <li><a href="{$site_root_path}session/login.php{if isset($redirect_url)}?redirect={$redirect_url}{/if}">Log in</a></li>
  {/if}
      </ul>
    </div>

    <div id="page-content">

      <nav class="navbar navbar-default{if !isset($logged_in_user) and isset($thinkupllc_endpoint)} is-logged-out{/if}" role="navigation">

        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">

          {if isset($logged_in_user) or !isset($thinkupllc_endpoint)}
          <button class="btn menu-trigger">
            <i class="fa fa-bars"></i>
          </button>

          {else}
          <div class="navbar-actions">
            <a href="{$thinkupllc_endpoint}" class="btn btn-sm btn-default btn-login btn-transparent">Login</a>
            <a href="https://thinkup.com/" class="btn btn-sm btn-default btn-signup">Sign Up</a>
          </div>
          {/if}

          {if isset($logged_in_user) && $display_search_box}
          <button type="button" class="navbar-toggle collapsed btn btn-lg" data-toggle="collapse" data-target="#search-form" aria-expanded="false" id="search-toggle">
            <span class="sr-only">Toggle navigation</span>
            <span class="fa fa-search"></span>
          </button>
          {/if}

          {*
            If any visitor is logged in or on a permalink page, link to the stream.
            If the user is on logged out, on thinkup.com, and visiting the stream, link to the homepage.
          *}
          {if isset($logged_in_user) or !isset($thinkupllc_endpoint) or count($insights) eq 1}
            {assign var='logo_link' value=$site_root_path}
          {else}
            {assign var='logo_link' value='https://thinkup.com'}
          {/if}
          <a class="navbar-brand" href="{$logo_link}"><strong>Think</strong>Up</span></a>

            {if $logged_in_user && $instances}

              {assign var='do_show_search' value=false}

              {if $display_search_box}
                {foreach from=$instances key=tid item=i}
                    {if $i->network eq 'twitter' || $i->network eq 'instagram'}
                      {assign var='do_show_search' value=true}
                      {assign var='default_username' value=$i->network_username}
                    {/if}
                {/foreach}
              {/if}

              {if $do_show_search}

              <!--search box-->
              <div class="navbar-collapse collapse in" id="search-form">
                <form class="navbar-form navbar-search" style="" method="get" action="{$site_root_path}search.php">

                    <input type="text" id="search-keywords" name="q" class="search-query" autocomplete="off" {if $smarty.get.q}value="{$smarty.get.q}" autofocus="true"{else}placeholder="Search followers"{/if} />

                </form>
              </div>
              {/if}<!-- // do_show_search -->

            {else}<!-- not logged in
                <form class="navbar-form navbar-search dropdown hidden-xs">
                  <input type="search" id="search-keywords" class="search-query" autocomplete="off" placeholder="Search" data-toggle="popover" data-trigger="click focus" title="<a href='{$site_root_path}session/login.php{if isset($redirect_url)}?redirect={$redirect_url}{/if}' class='btn btn-default btn-signup btn-sm'>Log in</a> to search" data-html="true" data-content="Not a member yet? <a href='https://thinkup.com/?utm_source=permalink_tout&utm_medium=banner&utm_campaign=touts' style='text-decoration: underline;' >Join now!</a>" data-placement="bottom" onfocus="$('[data-toggle=popover]').popover()" />
                </form>-->
            {/if}

        </div>
      </nav>
