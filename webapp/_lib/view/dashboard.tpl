{include file="_header.tpl"}
{include file="_statusbar.tpl"}


<div style="float:left;margin:10px" id="nav-sidebar" class="round-all">
<ul id="top-level-sidenav"><br />
{if $instance}
<ul class="side-subnav">
<li{if $smarty.get.v eq ''} class="currentview"{/if}><a href="index.php?u={$instance->network_username|urlencode}&n={$instance->network}">Main Dashboard&nbsp;&nbsp;&nbsp;</a></li>
</ul></li>
{/if}
{if $sidebar_menu_posts}
<li>Posts<ul class="side-subnav">
{foreach from=$sidebar_menu_posts key=mkey item=mi name=tabloop}
   <li{if $smarty.get.v eq $mkey} class="currentview"{/if}><a href="index.php?v={$mkey}&u={$instance->network_username|urlencode}&n={$instance->network}">{$mi}&nbsp;&nbsp;&nbsp;</a></li>
{/foreach}</ul></li>
{/if}
{if $sidebar_menu_replies}
<li>Replies<ul class="side-subnav">
{foreach from=$sidebar_menu_replies key=mkey item=mi name=tabloop}
   <li{if $smarty.get.v eq $mkey} class="currentview"{/if}><a href="index.php?v={$mkey}&u={$instance->network_username|urlencode}&n={$instance->network}">{$mi}&nbsp;&nbsp;&nbsp;</a></li>
{/foreach}</ul></li>
{/if}
{if $sidebar_menu_friends}
<li>Friends<ul class="side-subnav">
{foreach from=$sidebar_menu_friends key=mkey item=mi name=tabloop}
   <li{if $smarty.get.v eq $mkey} class="currentview"{/if}><a href="index.php?v={$mkey}&u={$instance->network_username|urlencode}&n={$instance->network}">{$mi}&nbsp;&nbsp;&nbsp;</a></li>
{/foreach}</ul></li>
{/if}
{if $sidebar_menu_followers}
<li>Followers<ul class="side-subnav">
{foreach from=$sidebar_menu_followers key=mkey item=mi name=tabloop}
   <li{if $smarty.get.v eq $mkey} class="currentview"{/if}><a href="index.php?v={$mkey}&u={$instance->network_username|urlencode}&n={$instance->network}">{$mi}&nbsp;&nbsp;&nbsp;</a></li>
{/foreach}</ul></li>
{/if}
{if $sidebar_menu_links}
<li>Links<ul class="side-subnav">
{foreach from=$sidebar_menu_links key=mkey item=mi name=tabloop}
   <li{if $smarty.get.v eq $mkey} class="currentview"{/if}><a href="index.php?v={$mkey}&u={$instance->network_username|urlencode}&n={$instance->network}">{$mi}&nbsp;&nbsp;&nbsp;</a></li>
{/foreach}</ul></li>
{/if}
</ul>
</div>
  <div class="thinkup-canvas round-all container_24">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
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
            <div class="grid_19">
              <span class="tweet">{$user_details->username} <span style="color:#ccc">{$user_details->network|capitalize}</span></span><br />
              <small>Recently posting about {$instance->posts_per_day|round} times a day{if $latest_clients_usage}, mostly using {foreach from=$latest_clients_usage key=name item=num_posts name=foo}{$name}{if !$smarty.foreach.foo.last} and {/if}{/foreach}{/if} (<a href="{$site_root_path}post/export.php?u={$instance->network_username}&n={$instance->network}">CSV</a>)</small>
            </div>
         </div>
         {/if}

    {if $data_template}
    <br /><br />
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
<br /><br />
          {foreach from=$recent_posts key=tid item=t name=foo}
            {include file="_post.tpl" t=$t headings="NONE"}
          {/foreach}
        {/if}

        {if $follower_count_history_by_day.history and $follower_count_history_by_week.history}
<br /><br />
        <table width="100%"><tr><td>
        Follower Count By Day{if $follower_count_history_by_day.history|@count < 2}<br /><i>Not enough data yet</i>{else} {if $follower_count_by_day_trend != 0}({if $follower_count_by_day_trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_by_day_trend|round|number_format}</span> per day){/if}<br />
        <img src="http://chart.apis.google.com/chart?chs=425x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{$t.date}|{/foreach}1:|{foreach from=$follower_count_history_by_day.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=ls&chco=0077CC&chd=t:{foreach from=$follower_count_history_by_day.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,76A4FB,0,0,0&chg=33">
        {/if}
        </td><td>
        Follower Count By Week{if $follower_count_history_by_week.history|@count < 2}<br /><i>Not enough data yet</i>{else} {if $follower_count_by_week_trend != 0}({if $follower_count_by_week_trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_by_week_trend|round|number_format}</span> per week){/if}<br />
        <img src="http://chart.apis.google.com/chart?chs=425x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_week.history key=tid item=t name=foo}{$t.date}|{/foreach}1:|{foreach from=$follower_count_history_by_week.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=ls&chco=0077CC&chd=t:{foreach from=$follower_count_history_by_week.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,76A4FB,0,0,0&chg=33">
        {/if}
        </td></tr>
        </table>
        {/if}

        <br />

        {if $most_replied_to_1wk}
<hr />
<h2 style="font-size:200%;margin-top:10px">This Week's Most Replied-To Posts</h2>
          {foreach from=$most_replied_to_1wk key=tid item=t name=foo}
            {include file="_post.tpl" t=$t headings="NONE"}
          {/foreach}
        {/if}

       {if $instance->network eq 'twitter' }
       <br /><br />
        <table width="100%" class="public_user_stats"><tr><td style="width:50%">
        <tr><td>
          <table><tr>
          <td>
            {$instance->percentage_replies|round}% posts are replies<br />
            {$instance->percentage_links|round}% posts contain links<br />
            <img src="http://chart.apis.google.com/chart?chxt=x,y&cht=bhg&chd=t:{$instance->percentage_replies|round},{$instance->percentage_links|round}&chco=76A4FB&chls=2.0&chs=425x150&chxl=1:|Broadcaster|Conversationalist|0:||20%||60%||100%|&chbh=50" />
        </td><td>
          Client Usage <span class="detail">(all posts)</span><br />
          <img src="http://chart.apis.google.com/chart?cht=p&chd=t:{foreach from=$all_time_clients_usage key=name item=num_posts name=foo}{if $num_posts>0}{math equation="round(x/y*100,2)" x=$num_posts y=$all_time_clients_usage|@array_sum}{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chs=425x200&chl={foreach from=$all_time_clients_usage key=name item=num_posts name=foo}{$name}+({$num_posts}){if !$smarty.foreach.foo.last}|{/if}{/foreach}&chco=76A4FB">
        </td></tr></table>
        </td></tr></table>
        {/if}

        {if $most_retweeted_1wk}
<hr />
<h2 style="font-size:200%;margin-top:10px">This Week's Most Retweeted</h2>
          {foreach from=$most_retweeted_1wk key=tid item=t name=foo}
            {include file="_post.tpl" t=$t headings="NONE"}
          {/foreach}
        {/if}

        {if $least_likely_followers}
            <hr />
            <h2 style="font-size:200%;margin-top:10px">Most Discerning Followers</h2>
            {foreach from=$least_likely_followers key=uid item=u name=foo}
            <div class="avatar-container" style="float:left;margin:7px;">  
               <a href="http://twitter.com/{$u.user_name}" title="{$u.user_name}"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.ico" class="service-icon2"/></a> 
            </div>
            {/foreach}
            <div style="clear:all;"><br /><br /><br /></div>
        {/if}

        {if $conversations}
<hr />
<h2 style="font-size:200%;margin-top:10px">Conversations</h2>
          {foreach from=$conversations key=tid item=r name=foo}
            {include file="_post.qa.tpl" t=$t headings="NONE"}
          {/foreach}
        {/if}
        {if $most_replied_to_alltime}
<hr />
<h2 style="font-size:200%;margin-top:10px">All-Time Most Replied-To</h2>
          {foreach from=$most_replied_to_alltime key=tid item=t name=foo}
            {include file="_post.tpl" t=$t headings="NONE"}
          {/foreach}
        {/if}

        {if $most_retweeted_alltime}
<hr />
<h2 style="font-size:200%;margin-top:10px">All-Time Most Retweeted</h2>
          {foreach from=$most_retweeted_alltime key=tid item=t name=foo}
            {include file="_post.tpl" t=$t headings="NONE"}
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