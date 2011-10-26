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
                  <div class="clearfix">
                      <!-- show retweets and replies for twitter; show favorites and replies for others -->
                      <div id="hot_posts">
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
                    <div id="follower_count_history_by_day"></div>
                    {if $follower_count_history_by_day.trend}({if $follower_count_history_by_day.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_day.trend|number_format}</span>/day){/if}
                  </div>
                  <div class="grid_9 omega">
                    <div id="follower_count_history_by_week"></div>
                    {if $follower_count_history_by_week.trend != 0}({if $follower_count_history_by_week.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_week.trend|number_format}</span>/week){/if}
                  </div>
                </div>
                <script type="text/javascript">
                // Load the Visualization API and the standard charts
                google.load('visualization', '1');
                // Set a callback to run when the Google Visualization API is loaded.
                google.setOnLoadCallback(drawCharts);

                {literal}
                function drawCharts() {
                {/literal}
                  var follower_count_history_by_day_data = new google.visualization.DataTable(
                  {$follower_count_history_by_day.vis_data});
                  var follower_count_history_by_week_data = new google.visualization.DataTable(
                  {$follower_count_history_by_week.vis_data});
                  var follower_description = '{if $instance->network eq 'twitter'}Followers{elseif $instance->network eq 'facebook page'}Fans{elseif $instance->network eq 'facebook'}Friends{/if}';

                  var hot_posts_data = new google.visualization.DataTable({$hot_posts_data});

                  var client_usage_data = new google.visualization.DataTable({$all_time_clients_usage});

                  {literal}
                  var follower_count_history_by_day_chart = new google.visualization.ChartWrapper({
                      containerId: 'follower_count_history_by_day',
                      chartType: 'LineChart',
                      dataTable: follower_count_history_by_day_data,
                      options: {
                          title: follower_description + ' Count By Day',
                          titleTextStyle: {color: '#848884', fontSize: 16},
                          width: 350,
                          height: 250,
                          legend: "top",
                          interpolateNulls: true,
                          vAxis: {
                            minValue: 0
                          }
                      },
                  });
                  follower_count_history_by_day_chart.draw();

                  var follower_count_history_by_week_chart = new google.visualization.ChartWrapper({
                      containerId: 'follower_count_history_by_week',
                      chartType: 'LineChart',
                      dataTable: follower_count_history_by_week_data,
                      options: {
                          title: follower_description + ' Count By Week',
                          titleTextStyle: {color: '#848884', fontSize: 16},
                          width: 350,
                          height: 250,
                          legend: "top",
                          interpolateNulls: true,
                          vAxis: {
                            minValue: 0
                          }
                      },
                  });
                  follower_count_history_by_week_chart.draw();

                  if (typeof(replies) != 'undefined') {
                    var post_types = new google.visualization.DataTable();
                    post_types.addColumn('number', 'Conversationalist');
                    post_types.addColumn('number', 'Broadcaster');
                    post_types.addRow([{v: replies/100, f: replies + '%'}, {v: links/100, f: links + '%'}]);

                    var post_type_chart = new google.visualization.ChartWrapper({
                        containerId: 'post_types',
                        chartType: 'BarChart',
                        dataTable: post_types,
                        options: {
                            title: 'Post Types',
                            titleTextStyle: {color: '#848884', fontSize: 19},
                            width: 300,
                            height: 150,
                            legend: 'top',
                            hAxis: {
                              minValue: 0,
                              maxValue: 1,
                              format:'#,###%'
                            }
                        }
                    });
                    post_type_chart.draw();
                  }

                  var hot_posts_chart = new google.visualization.ChartWrapper({
                      containerId: 'hot_posts',
                      chartType: 'BarChart',
                      dataTable: hot_posts_data,
                      options: {
                          title: 'Recent Activity',
                          titleTextStyle: {color: '#848884', fontSize: 19},
                          isStacked: true,
                          width: 700,
                          height: 300,
                          legend: 'right',
                          hAxis: {
                            minValue: 0,
                          },
                          vAxis: {
                            textStyle:  {fontSize: 9},
                          },
                          chartArea:{width:"40%"}
                      }
                  });
                  hot_posts_chart.draw();

                  var client_usage_chart = new google.visualization.ChartWrapper({
                      containerId: 'client_usage',
                      // chartType: 'ColumnChart',
                      chartType: 'PieChart',
                      dataTable: client_usage_data,
                      options: {
                          title: 'Client Usage (All Posts)',
                          titleTextStyle: {color: '#848884', fontSize: 19},
                          width: 400,
                          height: 300,
                          sliceVisibilityThreshold: 1/100,
                          pieSliceText: 'label',
                      }
                  });
                  client_usage_chart.draw();
                }

                  {/literal}
                </script>
              {/if}

              {if $follower_count_history_by_week.milestone}
                <div class="small gray">
                  Next milestone: <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.will_take} week{if $follower_count_history_by_week.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.next_milestone|number_format} followers</span> at this rate. <a href="{$site_root_path}index.php?v=followers-history&u={$instance->network_username}&n={$instance->network}">More...</a>
                </div>
              {/if}

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
                          <h2></h2>
                          <div id="post_types"></div>
                          <div class="clearfix small prepend">
                             {$instance->percentage_replies|round}% posts are replies<br>
                             {$instance->percentage_links|round}% posts contain links<br>
                          </div>
                          <script>
                          var replies = {$instance->percentage_replies|round};
                          var links = {$instance->percentage_links|round};
                          </script>
                       </div>
                       <div class="grid_8 omega">
                            <div id="client_usage">
                       </div>
                     </div>
                  </div>
                  <small>Recently posting about {$instance->posts_per_day|round} times a day{if $latest_clients_usage}, mostly using {foreach from=$latest_clients_usage key=name item=num_posts name=foo}{$name}{if !$smarty.foreach.foo.last} and {/if}{/foreach}{/if}</small>
                {/if}
            {/if} <!-- end if $data_template -->
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

<script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>

{include file="_footer.tpl"}
