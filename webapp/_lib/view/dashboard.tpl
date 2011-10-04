{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div class="container_24">
  <div class="clearfix">
    
    <!-- begin left nav -->
    <div class="grid_4 alpha omega" style="background-color:#e6e6e6">
      <div id="nav-sidebar">
        <ul id="top-level-sidenav">
          <li style="list-style: none">
        {if $instance}
        <ul>
          <li>
            <ul class="side-subnav">
              <li{if $smarty.get.v eq ''} class="currentview"{/if}><br />
                <a href="{$site_root_path}index.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">Dashboard</a>
              </li>
        {/if}
        {if $sidebar_menu}
          {foreach from=$sidebar_menu key=smkey item=sidebar_menu_item name=smenuloop}
            {if $sidebar_menu_item->header}
            </ul>
          </li>
        <li>{$sidebar_menu_item->header}
        <ul class="side-subnav">
        {/if}
        <li{if $smarty.get.v eq $smkey} class="currentview"{/if}>
        <a href="{$site_root_path}index.php?v={$smkey}&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">{$sidebar_menu_item->name}</a></li>
        {/foreach}
        </ul>
        </li>
        </ul>
        {/if}
        </li>
        </ul>
      </div>
    </div>

    <div class="thinkup-canvas round-all grid_20 alpha omega prepend_20 append_20" style="min-height:340px">
      <div class="prefix_1 suffix_1">

        {include file="_usermessage.tpl"}

        {if $instance}
            <!--begin public user dashboard-->
            {if $user_details}
            <div class="grid_18 alpha omega">
              <div class="clearfix dashboard-header round-all">
                <div class="grid_2 alpha">
                  <div class="avatar-container">
                    <img src="{$user_details->avatar}" class="avatar2"/>
                    <img src="{$site_root_path}plugins/{$user_details->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon2"/>
                  </div>
                </div>
                <div class="grid_15 omega">
                  <span class="tweet">{$user_details->username} <span style="color:#ccc">{$user_details->network|capitalize}</span></span><br />
                  <div class="small">
                    {if $instance->crawler_last_run eq 'realtime'}<span style="color:green;">&#9679;</span> Updated in realtime{else}Updated {$instance->crawler_last_run|relative_datetime} ago{/if}{if !$instance->is_active} (paused){/if}
                  </div>
                </div>
              </div>
            </div>
            {/if}

            {if $data_template}
              {include file=$data_template}
              <div class="float-l" id="older-posts-div">
                {if $next_page}
                  <a href="{$site_root_path}index.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$next_page}" id="next_page">&#60; Older Posts</a>
                {/if}
                {if $last_page}
                  | <a href="{$site_root_path}index.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n|urlencode}&{/if}page={$last_page}" id="last_page">Newer Posts  &#62;</a>
                {/if}
              </div>

            {else} <!-- else if $data_template -->

            {if $hot_posts|@count > 3}
              <h2>Hot Posts</h2>
                {foreach from=$hot_posts key=tid item=t name=foo}
                    {if $smarty.foreach.foo.index < 3}
                        {if $instance->network eq "twitter"}
                            {include file="_post.counts_no_author.tpl" post=$t}
                        {else}
                            {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets=true}
                        {/if}
                    {/if}
                {/foreach}
            {else}
              {if $recent_posts}
              <h2>Recent posts</h2>
                {foreach from=$recent_posts key=tid item=t name=foo}
                    {if $smarty.foreach.foo.index < 3}
                        {if $instance->network eq "twitter"}
                            {include file="_post.counts_no_author.tpl" post=$t}
                        {else}
                            {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets=true}
                        {/if}
                    {/if}
                {/foreach}
              {/if}
            {/if}

            {if $hot_posts|@count > 3}
                <h2>Recent Activity</h2>
                <div class="clearfix">
                    {foreach from=$hot_posts key=post_id item=post name=foo}
                        {assign var="ra_count" value="`$post->favlike_count_cache+$post->reply_count_cache+$post->all_retweets`"}
                        {if $ra_max < $ra_count}
                            {assign var="ra_max" value=$ra_count}
                        {/if}
                    {/foreach}
                    {if $instance->network neq "twitter"} 
                        <img width="700" height="280" src="http://chart.googleapis.com/chart?chxs=0,,11&chxt=y&chxl=0:|{foreach from=$hot_posts|@array_reverse key=post_id item=post name=foo}{if $post->post_text}{$post->post_text|replace:'|':''|truncate:50|urlencode}{elseif $post->link->title}{$post->link->title|replace:'|':''|truncate:50|urlencode}{elseif $post->link->url}{$post->link->url|replace:'|':''|truncate:50|urlencode}{else}{$post->pub_date|date_format:"%b %e"}{/if}|{/foreach}&chd=t:{foreach from=$hot_posts key=post_id item=post name=foo}{if $post->favlike_count_cache > 0}{$post->favlike_count_cache}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}|{foreach from=$hot_posts key=post_id item=post name=foo}{if $post->reply_count_cache > 0}{$post->reply_count_cache}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chds=0,{$ra_max+5}&chbh=a&chco=FF9900,cccccc&&chdl={if $instance->network eq 'google+'}%2B1's{else}Likes{/if}|Replies&chs=700x280&cht=bhs&chm=N*s*,666666,-1,-1,11,,e:2:0">
                    {else}
                        <img width="700" height="280" src="http://chart.googleapis.com/chart?chxs=0,,11&chxt=y&chxl=0:|{foreach from=$hot_posts|@array_reverse key=post_id item=post name=foo}{$post->post_text|replace:'|':''|truncate:50|urlencode}|{/foreach}&chd=t:{foreach from=$hot_posts key=post_id item=post name=foo}{if $post->all_retweets > 0}{$post->all_retweets}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}|{foreach from=$hot_posts key=post_id item=post name=foo}{if $post->reply_count_cache > 0}{$post->reply_count_cache}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chds=0,{$ra_max+5}&chbh=a&chco=FF9900,cccccc&chdl=Retweets|Replies&chs=700x280&cht=bhs&chm=N*s*,666666,-1,-1,11,,e:2:0">
                    {/if}
                </div>
            {/if}

            {if $most_replied_to_1wk}
              <div class="clearfix">
                <h2>This Week's Most {if $instance->network eq 'google+'}Discussed{else}Replied-To{/if} Posts</h2>
                {foreach from=$most_replied_to_1wk key=tid item=t name=foo}
                    {if $instance->network eq "twitter"}
                        {include file="_post.counts_no_author.tpl" post=$t headings="NONE"}
                    {else}
                        {include file="_post.counts_no_author.tpl" post=$t headings="NONE" show_favorites_instead_of_retweets=true}
                    {/if}
                {/foreach}
              </div>
            {/if}

            {if $most_faved_1wk}
              <div class="clearfix">
                <h2>This Week's Most {if $instance->network eq 'google+'}+1'ed{else}Liked{/if} Posts</h2>
                {foreach from=$most_faved_1wk key=tid item=t name=foo}
                  {include file="_post.counts_no_author.tpl" post=$t headings="NONE" show_favorites_instead_of_retweets=true}
                {/foreach}
              </div>
            {/if}

              {if $follower_count_history_by_day.history && $follower_count_history_by_week.history}
              <div class="clearfix">
                <div class="grid_9 alpha">
                  <h2>{if $instance->network eq 'twitter'}Followers {elseif $instance->network eq 'facebook page'}Fans {elseif $instance->network eq 'facebook'}Friends {/if}By Day{if !$follower_count_history_by_day.history OR $follower_count_history_by_day.history|@count < 2}<br /><i>Not enough data to display chart</i>{else} {if $follower_count_history_by_day.trend}({if $follower_count_history_by_day.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_day.trend|number_format}</span>/day){/if}</h2>
                  <img width="350" height="200" src="http://chart.apis.google.com/chart?chs=350x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{$tid|date_format:"%b %d"}|{/foreach}1:|{foreach from=$follower_count_history_by_day.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=bvs&chco=FF9900&chd=t:{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{if $t > 0}{$t}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chbh=a&chds={$follower_count_history_by_day.min_count},{$follower_count_history_by_day.max_count}&chxr={$follower_count_history_by_day.min_count},{$follower_count_history_by_day.max_count}&chxs=1N*s*&chm=N*s*,666666,0,-1,10,,e::5" />{/if}
                </div>
                <div class="grid_9 omega">
                  <h2>{if $instance->network eq 'twitter'}Followers {elseif $instance->network eq 'facebook page'}Fans {elseif $instance->network eq 'facebook'}Friends {/if} By Week{if !$follower_count_history_by_week.history OR $follower_count_history_by_week.history|@count < 2}<br /><i>Not enough data to display chart</i><br clear="all"/>{else} {if $follower_count_history_by_week.trend != 0}({if $follower_count_history_by_week.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_week.trend|number_format}</span>/week){/if}</h2>
                  <img width="350" height="200" src="http://chart.apis.google.com/chart?chs=350x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_week.history key=tid item=t name=foo}{$tid|date_format:"%b %d"}|{/foreach}1:|{foreach from=$follower_count_history_by_week.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=bvs&chco=FF9900&chd=t:{foreach from=$follower_count_history_by_week.history key=tid item=t name=foo}{if $t > 0}{$t}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chbh=a&chds={$follower_count_history_by_week.min_count},{$follower_count_history_by_week.max_count}&chxr={$follower_count_history_by_week.min_count},{$follower_count_history_by_week.max_count}&chxs=1N*s*&chm=N*s*,666666,0,-1,10,,e::5" />
                </div>
              </div>
              {/if}

              {if $follower_count_history_by_week.milestone}
                <div class="small gray">
                  Next milestone: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.will_take} week{if $follower_count_history_by_week.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.next_milestone|number_format} followers</span> at this rate. <a href="{$site_root_path}index.php?v=followers-history&u={$instance->network_username}&n={$instance->network}">More...</a>
                </div>
              {/if}

            {/if} <!-- end if $data_template -->

            {if $least_likely_followers}
              <div class="clearfix">
                <h2>Most Discerning Followers</h2>
                <div class="clearfix">
                {foreach from=$least_likely_followers key=uid item=u name=foo}
                  <div class="avatar-container" style="float:left;margin:7px;">  
                    <a href="http://twitter.com/{$u.user_name}" title="{$u.user_name}"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.ico" class="service-icon2"/></a> 
                  </div>
                {/foreach}
                <div class="clearfix small prepend">
                <br ><br >&nbsp;<a href="{$site_root_path}index.php?v=followers-leastlikely&u={$instance->network_username}&n={$instance->network}">More...</a></div>
                </div>
                </div>
            {/if}
            

            {if $most_retweeted_1wk}
              <div class="clearfix">
                <h2>This Week's Most {if $instance->network eq 'google+'}Reshared{else}Retweeted{/if}</h2>
                {foreach from=$most_retweeted_1wk key=tid item=t name=foo}
                  {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets=false}
                {/foreach}
              </div>
            {/if}

        {if $instance->network eq 'twitter' }
        <div class="clearfix">
           <div class="public_user_stats">
            <div class="grid_8 alpha">
            <h2>Post Types</span></h2>
            <div class="clearfix small prepend">
                {$instance->percentage_replies|round}% posts are replies<br>
                {$instance->percentage_links|round}% posts contain links<br>
             </div>
                <img width="250" height="175" src="http://chart.apis.google.com/chart?chxt=x,y&cht=bhg&chd=t:{$instance->percentage_replies|round},{$instance->percentage_links|round}&chco=6184B5&chls=2.0&chs=250x175&chxl=0:|20%|60%|100%|1:|Broadcaster|Conversationalist&chxp=0,20,60,100&chbh=50" />
             </div>
             <div class="grid_8 omega">
                  <h2>Client Usage <span class="detail">(all posts)</span></h2>
                  <img width="400" height="200" src="http://chart.apis.google.com/chart?cht=p&chd=t:{foreach from=$all_time_clients_usage key=name item=num_posts name=foo}{if $num_posts>0}{math equation="round(x/y*100,2)" x=$num_posts y=$all_time_clients_usage|@array_sum}{else}0{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chs=400x200&chl={foreach from=$all_time_clients_usage key=name item=num_posts name=foo}{$name}+({$num_posts}){if !$smarty.foreach.foo.last}|{/if}{/foreach}&chco=6184B5,E6E6E6"><br /><br />
             </div>
           </div>
        </div>
        <small>Recently posting about {$instance->posts_per_day|round} times a day{if $latest_clients_usage}, mostly using {foreach from=$latest_clients_usage key=name item=num_posts name=foo}{$name}{if !$smarty.foreach.foo.last} and {/if}{/foreach}{/if}</small>
        {/if}
          {/if} 
          
        {/if}

{if !$instance}
        <div style="width:60%;text-align:center;">
        {if $add_user_buttons}
        {foreach from=$add_user_buttons key=smkey item=button name=smenuloop}
            <br><br>
            <div style="float:right;"><a href="{$site_root_path}account/?p={$button}" class="tt-button ui-state-default tt-button-icon-right ui-corner-all"><span class="ui-icon ui-icon-circle-arrow-e"></span>Add a {if $button eq 'googleplus'}Google+{else}{$button|ucwords}{/if} Account</a></div>
         {/foreach}
        {/if}
        <br><br>
        <div style="float:right;"><a href="{$site_root_path}account/" class="tt-button ui-state-default tt-button-icon-right ui-corner-all"><span class="ui-icon ui-icon-circle-arrow-e"></span>Adjust Your Settings</a></div>
        </div>
{/if}

      </div> <!-- /.prefix_1 -->
    </div> <!-- /.thinkup-canvas -->
    
  </div> <!-- /.clearfix -->
</div> <!-- /.container_24 -->

  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
  
{include file="_footer.tpl"}
