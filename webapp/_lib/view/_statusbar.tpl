
    <div class="navbar navbar-static-top">

        <div class="container">

            <div class="navbar-header">

                <a href="{$site_root_path}" class="navbar-brand col-md-3"><span style="color : #00AEEF; font-weight : 800;">Think</span><span style="color : black; font-weight : 200;">Up</span></a>

                <a class="btn btn-navbar navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="glyphicon glyphicon-th"></span>
                </a>

            {if $logged_in_user && !$smarty.get.m && !$smarty.get.p && $instances}

                <!--search posts-->
                <form class="navbar-form pull-left dropdown" method="get" action="javascript:searchMe('{$site_root_path}search.php?u={$instances[0]->network_username|urlencode}&amp;n={$instances[0]->network|urlencode}&amp;c=posts&amp;q=');">

                    <input type="text" id="search-keywords" class="search-query span4 dropdown-toggle" data-toggle="dropdown" autocomplete="off" {if $smarty.get.q}value="{$smarty.get.q}"{else}placeholder="Search"{/if} />

                    <ul id="search-refine" class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                    {foreach from=$instances key=tid item=i}
                        <li class="lead"><a onclick="searchMe('{$site_root_path}search.php?u={$i->network_username|urlencode}&amp;n={$i->network|urlencode}&amp;c=posts&q=');" href="#"><i class="icon-{$i->network}{if $i->network eq 'google+'} icon-google-plus{/if} icon-muted icon-2x"></i> Find <span class="searchterm"></span> in {if $i->network eq 'twitter'}@{/if}{$i->network_username}'s {if $i->network eq 'twitter'}tweets{elseif $i->network eq 'foursquare'}Foursquare check-ins{else}{$i->network|ucwords} posts{/if}</a></li>
                        {if $i->network eq 'twitter'}
                            <li class="lead"><a onclick="searchMe('{$site_root_path}search.php?u={$i->network_username|urlencode}&amp;n=twitter&amp;c=followers&amp;q=');" href="#"><i class="icon-twitter icon-muted icon-2x"></i> Search @{$i->network_username}'s followers' bios for <span class="searchterm"></span></a></li>
                            <li class="lead"><a onclick="searchMe('{$site_root_path}search.php?u={$i->network_username|urlencode}&amp;n=twitter&amp;c=followers&amp;q=name:');" href="#"><i class="icon-twitter icon-muted icon-2x"></i> Search @{$i->network_username}'s followers for people named <span class="searchterm"></span></a></li>
                        {/if}
                    {/foreach}
                    {foreach from=$saved_searches key=tid item=i}
                        <li class="lead"><a onclick="searchMe('{$site_root_path}search.php?u={$i.network_username|urlencode}&amp;n=twitter&amp;c=searches&amp;k={$i.hashtag|urlencode}&amp;q=');" href="#"><i class="icon-twitter icon-muted icon-2x"></i> Search tweets which contain {$i.hashtag} for <span class="searchterm"></span></a></li>
                    {/foreach}
                    </ul>

                </form>

            {/if}

            </div>

            <div class="navbar-collapse">

      {if $logged_in_user}

<ul class="nav navbar-nav navbar-right" style="border-left : none;">

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
<ul class="nav navbar-nav navbar-right">
    <li><a href="http://thinkup.com/" >Get ThinkUp</a></li>
    <li><a href="{$site_root_path}session/login.php" ><i class="icon-signin icon-muted"></i> Log In</a></li>
</ul>
      {/if}
          </div><!--/.navbar-collapse -->


        </div>

    </div>

