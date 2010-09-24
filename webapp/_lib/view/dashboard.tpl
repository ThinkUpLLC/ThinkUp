{include file="_header.tpl"}
{include file="_statusbar.tpl"}

  <div class="thinkup-canvas round-all container_24">
    <div class="clearfix">
    
      <div class="grid_4 alpha">
      
        <div id="nav-sidebar">
        <ul id="top-level-sidenav"><br />
        {if $instance}
        <ul class="side-subnav">
        <li{if $smarty.get.v eq ''} class="currentview"{/if}><a href="index.php?u={$instance->network_username|urlencode}&n={$instance->network}">Main Dashboard&nbsp;&nbsp;&nbsp;</a></li>
        </ul></li>
        {/if}
        
        {if $sidebar_menu_posts}
            <li><b>Posts</b><ul class="side-subnav">
            {foreach from=$sidebar_menu_posts key=mkey item=mi name=tabloop}
            <li{if $smarty.get.v eq $mkey} class="currentview"{/if}><a href="index.php?v={$mkey}&u={$instance->network_username|urlencode}&n={$instance->network}">{$mi}&nbsp;&nbsp;&nbsp;</a></li>
            {/foreach}</ul></li>
            {/if}
            {if $sidebar_menu_replies}
            <li><b>Replies</b><ul class="side-subnav">
            {foreach from=$sidebar_menu_replies key=mkey item=mi name=tabloop}
            <li{if $smarty.get.v eq $mkey} class="currentview"{/if}><a href="index.php?v={$mkey}&u={$instance->network_username|urlencode}&n={$instance->network}">{$mi}&nbsp;&nbsp;&nbsp;</a></li>
            {/foreach}</ul></li>
            {/if}
            {if $sidebar_menu_friends}
            <li><b>Friends</b><ul class="side-subnav">
            {foreach from=$sidebar_menu_friends key=mkey item=mi name=tabloop}
            <li{if $smarty.get.v eq $mkey} class="currentview"{/if}><a href="index.php?v={$mkey}&u={$instance->network_username|urlencode}&n={$instance->network}">{$mi}&nbsp;&nbsp;&nbsp;</a></li>
            {/foreach}</ul></li>
            {/if}
            {if $sidebar_menu_followers}
            <li><b>Followers</b><ul class="side-subnav">
            {foreach from=$sidebar_menu_followers key=mkey item=mi name=tabloop}
            <li{if $smarty.get.v eq $mkey} class="currentview"{/if}><a href="index.php?v={$mkey}&u={$instance->network_username|urlencode}&n={$instance->network}">{$mi}&nbsp;&nbsp;&nbsp;</a></li>
            {/foreach}</ul></li>
            {/if}
            {if $sidebar_menu_links}
            <li><b>Links</b><ul class="side-subnav">
            {foreach from=$sidebar_menu_links key=mkey item=mi name=tabloop}
            <li{if $smarty.get.v eq $mkey} class="currentview"{/if}><a href="index.php?v={$mkey}&u={$instance->network_username|urlencode}&n={$instance->network}">{$mi}&nbsp;&nbsp;&nbsp;</a></li>
            {/foreach}</ul></li>
        {/if}
        </ul>
        </div>
      
      </div>
        
      <div class="grid_20 omega prepend_20 append_20">
      
        {include file="_usermessage.tpl"}
        
        {if $instance}
        <!--begin public user dashboard-->
          {if $user_details}
          <div class="clearfix">
            <div class="grid_2 alpha">
              <div class="avatar-container">
                <img src="{$user_details->avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$user_details->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon2"/>
              </div>
            </div>
            <div class="grid_18 omega">
              <span class="tweet">{$user_details->username} <span style="color:#ccc">{$user_details->network|capitalize}</span></span><br />
              <small>Recently posting about {$instance->posts_per_day|round} times a day{if $latest_clients_usage}, mostly using {foreach from=$latest_clients_usage key=name item=num_posts name=foo}{$name}{if !$smarty.foreach.foo.last} and {/if}{/foreach}{/if} (<a href="{$site_root_path}post/export.php?u={$instance->network_username}&n={$instance->network}">CSV</a>)</small>
            </div>
         </div>
         {/if}

    {if $data_template}
    
        <br />

        {include file="_pagination.tpl"}

        {include file=$data_template}
        
        {include file="_pagination.tpl"}
                
    {else}
    
        {if $recent_posts}
            <br />
            {foreach from=$recent_posts key=tid item=t name=foo}
                {include file="_post.tpl" t=$t headings="NONE"}
            {/foreach}
        {/if}

        {if $follower_count_history_by_day.history and $follower_count_history_by_week.history}
        <div class="clearfix">
            <br />
            <div class="grid_10 alpha">
                <h2 class="subhead">Follower Count By Day</h2>
                {if $follower_count_history_by_day.history|@count < 2}
                    <i class="gray">Not enough data yet</i>
                {else} 
                    {if $follower_count_by_day_trend != 0}
                        ({if $follower_count_by_day_trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}
                        {$follower_count_by_day_trend|round|number_format}</span> per day)
                    {/if}
                    
                    <img src="http://chart.apis.google.com/chart?chs=390x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{$t.date}|{/foreach}1:|{foreach from=$follower_count_history_by_day.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=ls&chco=0077CC&chd=t:{foreach from=$follower_count_history_by_day.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,76A4FB,0,0,0&chg=33">
                {/if}
            </div>
            <div class="grid_10 omega">
                <h2 class="subhead">Follower Count By Week</h2>
                {if $follower_count_history_by_week.history|@count < 2}
                    <i class="gray">Not enough data yet</i>
                {else} 
                    {if $follower_count_by_week_trend != 0}
                        ({if $follower_count_by_week_trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}
                        {$follower_count_by_week_trend|round|number_format}</span> per week)
                    {/if}
                    
                    <img src="http://chart.apis.google.com/chart?chs=390x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_week.history key=tid item=t name=foo}{$t.date}|{/foreach}1:|{foreach from=$follower_count_history_by_week.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=ls&chco=0077CC&chd=t:{foreach from=$follower_count_history_by_week.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,76A4FB,0,0,0&chg=33">
                {/if}
            </div>
        </div>
        {/if}

        {if $least_likely_followers}
            <br>
            <h2 class="subhead">Star Followers</h2>
            {foreach from=$least_likely_followers key=uid item=u name=foo}
            <div class="avatar-container" style="float:left;margin:7px;">  
               <a href="http://twitter.com/{$u.user_name}" title="{$u.user_name}"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.ico" class="service-icon2"/></a> 
            </div>
            {/foreach}
            <div style="clear:all;"><br /><br /><br /></div>
        {/if}

        {if $most_replied_to_1wk}
            <br>
            <h2 class="subhead">This Week's Most Replied-To Posts</h2>
              {foreach from=$most_replied_to_1wk key=tid item=t name=foo}
                {include file="_post.tpl" t=$t headings="NONE"}
              {/foreach}
        {/if}

        {if $instance->network eq 'twitter' }
        <div class="clearfix">
            <br />
            <div class="grid_7 alpha">
                <h2 class="subhead">Post breakdown</h2>
                <img src="http://chart.apis.google.com/chart?chxt=x,y&cht=bhg&chd=t:{$instance->percentage_replies|round},{$instance->percentage_links|round}&chco=76A4FB&chls=2.0&chs=250x150&chxl=1:|Broadcaster|Conversationalist|0:||20%||60%||100%|&chbh=50" height="150" width="250" />
                <div class="small gray">
                    {$instance->percentage_replies|round}% posts are replies<br />
                    {$instance->percentage_links|round}% posts contain links
                </div>
            </div>
            <div class="grid_13 omega">
                <h2 class="subhead">Client Usage</h2>
                <img src="http://chart.apis.google.com/chart?chs=510x200&cht=p&chd=t:{foreach from=$all_time_clients_usage key=name item=num_posts name=foo}{math equation="round(x/y*100,2)" x=$num_posts y=$all_time_clients_usage|@array_sum}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chl={foreach from=$all_time_clients_usage key=name item=num_posts name=foo}{$name}+({$num_posts}){if !$smarty.foreach.foo.last}|{/if}{/foreach}&chco=76A4FB">
            </div>
        </div>
        {/if}

        {if $most_retweeted_1wk}
            
            <h2 class="subhead">This Week's Most Retweeted</h2>
            {foreach from=$most_retweeted_1wk key=tid item=t name=foo}
                {include file="_post.tpl" t=$t headings="NONE"}
            {/foreach}
            
        {/if}

        {/if}
        {/if}
      </div>
      
    </div> <!-- end .clearfix -->
  </div> <!-- end .thinkup-canvas -->

  <script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
  
{include file="_footer.tpl"}