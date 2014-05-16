
    <div id="menu">
      <ul class="list-unstyled menu-options">
        <li><a href="{$site_root_path}" {if !$controller_title}class="active"{/if}>Home</a></li>

  {if isset($logged_in_user)}
        <li class="service {$facebook_connection_status}"><a href="{$site_root_path}account/?p=facebook" {if $smarty.get.p eq 'facebook'}class="active"{/if}>Facebook<i class="fa fa-{if $facebook_connection_status eq 'active'}check-circle{elseif $facebook_connection_status eq 'error'}exclamation-triangle{else}facebook-square{/if} icon"></i></a></li>
        <li class="service {$twitter_connection_status}"><a href="{$site_root_path}account/?p=twitter" {if $smarty.get.p eq 'twitter'}class="active"{/if}>Twitter<i class="fa fa-{if $twitter_connection_status eq 'active'}check-circle{elseif $twitter_connection_status eq 'error'}exclamation-triangle{else}twitter{/if} icon"></i></a></li>
        <!--
        <li class="service inactive"><a href="{$site_root_path}account/?p=instagram">Instagram <i class="fa fa-instagram icon"></i></a></li>
        -->

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

      <nav class="navbar navbar-default" role="navigation">

        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">

          <button class="btn menu-trigger">
            <i class="fa fa-bars"></i>
          </button>
          <a class="navbar-brand" href="{$site_root_path}"><strong>Think</strong>Up</span></a>

            {if $logged_in_user && !$smarty.get.m && !$smarty.get.p && $instances && !isset($thinkupllc_endpoint)}

                <!--search posts-->
                <form class="navbar-form navbar-search dropdown hidden-xs" style="" method="get" action="javascript:searchMe('{$site_root_path}search.php?u={$instances[0]->network_username|urlencode}&amp;n={$instances[0]->network|urlencode}&amp;c=posts&amp;q=');">

                    <input type="text" id="search-keywords" class="search-query dropdown-toggle" data-toggle="dropdown" autocomplete="off" {if $smarty.get.q}value="{$smarty.get.q}"{else}placeholder="Search"{/if} />

                    <ul id="search-refine" class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                    {foreach from=$instances key=tid item=i}
                        <li><a onclick="searchMe('{$site_root_path}search.php?u={$i->network_username|urlencode}&amp;n={$i->network|urlencode}&amp;c=posts&amp;q=');" href="#"><i class="fa fa-{$i->network}{if $i->network eq 'google+'} fa-google-plus{/if} icon-muted fa-2x"></i> Find <span class="searchterm"></span> in {if $i->network eq 'twitter'}@{/if}{$i->network_username}'s {if $i->network eq 'twitter'}tweets{elseif $i->network eq 'foursquare'}Foursquare check-ins{else}{$i->network|ucwords} posts{/if}</a></li>
                        {if $i->network eq 'twitter'}
                            <li><a onclick="searchMe('{$site_root_path}search.php?u={$i->network_username|urlencode}&amp;n=twitter&amp;c=followers&amp;q=');" href="#"><i class="fa fa-twitter icon-muted fa-2x"></i> Search @{$i->network_username}'s followers' bios for <span class="searchterm"></span></a></li>
                            <li><a onclick="searchMe('{$site_root_path}search.php?u={$i->network_username|urlencode}&amp;n=twitter&amp;c=followers&amp;q=name:');" href="#"><i class="fa fa-twitter icon-muted fa-2x"></i> Search @{$i->network_username}'s followers for people named <span class="searchterm"></span></a></li>
                        {/if}
                    {/foreach}
                    {foreach from=$saved_searches key=tid item=i}
                        <li ><a onclick="searchMe('{$site_root_path}search.php?u={$i.network_username|urlencode}&amp;n=twitter&amp;c=searches&amp;k={$i.hashtag|urlencode}&amp;q=');" href="#"><i class="fa fa-twitter icon-muted fa-2x"></i> Search tweets which contain {$i.hashtag} for <span class="searchterm"></span></a></li>
                    {/foreach}
                    </ul>

                </form>

            {/if}

        </div>
      </nav>
