{include file="_header.tpl"}
{include file="_statusbar.tpl"}

  <div class="thinkup-canvas round-all container_24">
    <div class="clearfix">
    
      <div class="grid_4 alpha">
      
        <div id="nav-sidebar">

<ul id="top-level-sidenav">
    <li style="list-style: none">
{if $instance}
        <ul>
            <li>
                <ul class="side-subnav">
                    <li{if $smarty.get.v eq ''} class="currentview"{/if}><br /><a href="index.php?u={$instance->network_username|urlencode}&n={$instance->network}">Main Dashboard</a></li>
{/if}
{if $sidebar_menu}
{foreach from=$sidebar_menu key=smkey item=sidebar_menu_item name=smenuloop}
{if $sidebar_menu_item->header}
                        </ul>
                    </li>
                    <li>{$sidebar_menu_item->header}
                        <ul class="side-subnav">
{/if}
                            <li{if $smarty.get.v eq $smkey} class="currentview"{/if}><a href="index.php?v={$smkey}&u={$instance->network_username|urlencode}&n={$instance->network}">{$sidebar_menu_item->name}</a></li>
{/foreach}
                </ul>
            </li>
        </ul>
{/if}
    </li>
</ul>
</div>
      </div>
        
      <div class="grid_20 omega prepend_20 append_20">
      
        {include file="_usermessage.tpl"}
        
        {if $instance}
        <!--begin public user dashboard-->
          {if $user_details}
          <div class="clearfix dashboard-header">
            <div class="grid_2 alpha">
              <div class="avatar-container">
              <img src="{$user_details->avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$user_details->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon2"/>
              </div>
            </div>
            <div class="grid_16 omega">
              <span class="tweet">{$user_details->username} <span style="color:#ccc">{$user_details->network|capitalize}</span></span><br />
              <small>Recently posting about {$instance->posts_per_day|round} times a day{if $latest_clients_usage}, mostly using {foreach from=$latest_clients_usage key=name item=num_posts name=foo}{$name}{if !$smarty.foreach.foo.last} and {/if}{/foreach}{/if} (<a href="{$site_root_path}post/export.php?u={$instance->network_username}&n={$instance->network}">CSV</a>)</small>
            </div>
         </div>
         {/if}

    {if $data_template}
    

        {include file=$data_template}
        <div class="float-l">
        {if $next_page}
            <a href="{$site_root_path}index.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n}&{/if}page={$next_page}" id="next_page">&#60; Older Posts</a>
        {/if}
        {if $last_page}
            | <a href="{$site_root_path}index.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n}&{/if}page={$last_page}" id="last_page">Newer Posts  &#62;</a>
        {/if}
        </div>
        
    {else}
    
        {if $recent_posts}
            {foreach from=$recent_posts key=tid item=t name=foo}
            {include file="_post.lite.tpl" t=$t headings="NONE"}
            {/foreach}
        {/if}

		<div style="float : left; padding-right : 20px;">
        <h2>Follower Count By Day{if !$follower_count_history_by_day.history OR $follower_count_history_by_day.history|@count < 2}<br /><i>Not enough data to display chart</i>{else} {if $follower_count_history_by_day.trend}({if $follower_count_history_by_day.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_day.trend|number_format}</span>/day){/if}</h2>
        <img width="360" height="200" src="http://chart.apis.google.com/chart?chs=360x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{$tid}{if $t eq "no data"} (no data){/if}|{/foreach}1:|{foreach from=$follower_count_history_by_day.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=ls&chco=007733&chd=t:{foreach from=$follower_count_history_by_day.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,cccccc,0,0,0" />
        {/if}
        </div>
        
        <h2>Follower Count By Week{if !$follower_count_history_by_week.history OR $follower_count_history_by_week.history|@count < 2}<br /><i>Not enough data to display chart</i><br clear="all"/>{else} {if $follower_count_history_by_week.trend != 0}({if $follower_count_history_by_week.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_week.trend|number_format}</span>/week){/if}</h2>
        <img width="360" height="200" src="http://chart.apis.google.com/chart?chs=360x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_week.history key=tid item=t name=foo}{if $t eq "no data"}no data{else}{$tid}{/if}|{/foreach}1:|{foreach from=$follower_count_history_by_week.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=ls&chco=007733&chd=t:{foreach from=$follower_count_history_by_week.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,cccccc,0,0,0" />
        {/if}

        <br />
        {if $least_likely_followers}

            <h2 >Most Discerning Followers</h2>
            {foreach from=$least_likely_followers key=uid item=u name=foo}
            <div class="avatar-container" style="float:left;margin:7px;">  
               <a href="http://twitter.com/{$u.user_name}" title="{$u.user_name}"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.ico" class="service-icon2"/></a> 
            </div>
            {/foreach}
            <div style="clear:all;"><br /><br /><br /></div>
        {/if}


        {if $most_replied_to_1wk}

            <h2 >This Week's Most Replied-To Posts</h2>
              {foreach from=$most_replied_to_1wk key=tid item=t name=foo}
                {include file="_post.lite.tpl" t=$t headings="NONE"}
              {/foreach}
        {/if}

        {if $instance->network eq 'twitter' }
            <br /><br />
            <div class="grid_10 alpha">
                <div class="public_user_stats">
                {$instance->percentage_replies|round}% posts are replies<br />
                {$instance->percentage_links|round}% posts contain links<br />
                <img width="250" height="175" src="http://chart.apis.google.com/chart?chxt=x,y&cht=bhg&chd=t:{$instance->percentage_replies|round},{$instance->percentage_links|round}&chco=6184B5&chls=2.0&chs=250x175&chxl=1:|Broadcaster|Conversationalist|0:||20%||60%||100%|&chbh=50" />
                </div>
            </div>
            <div class="grid_10 omega">
              <h2>Client Usage <span class="detail">(all posts)</span></h2>
              <img width="400" height="200" src="http://chart.apis.google.com/chart?cht=p&chd=t:{foreach from=$all_time_clients_usage key=name item=num_posts name=foo}{if $num_posts>0}{math equation="round(x/y*100,2)" x=$num_posts y=$all_time_clients_usage|@array_sum}{else}0{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chs=400x200&chl={foreach from=$all_time_clients_usage key=name item=num_posts name=foo}{$name}+({$num_posts}){if !$smarty.foreach.foo.last}|{/if}{/foreach}&chco=6184B5,E6E6E6"><br /><br />
            </div>
        {/if}

        {if $most_retweeted_1wk}

            <h2 >This Week's Most Retweeted</h2>
            {foreach from=$most_retweeted_1wk key=tid item=t name=foo}
            {include file="_post.lite.tpl" t=$t headings="NONE"}
            {/foreach}
        {/if}

        {/if}
        {/if}
      </div>
    </div>
  </div> <!-- end .thinkup-canvas -->

  <script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
  
{include file="_footer.tpl"}
