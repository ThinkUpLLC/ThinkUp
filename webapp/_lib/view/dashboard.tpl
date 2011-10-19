{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div class="container_24">
  <div class="clearfix">

    <!-- begin left nav -->
    <div class="grid_4 alpha omega">
        {if $instance}
      <div id="nav">
        <ul id="top-level-sidenav">
        {/if}
        {if $instance}
              <li{if $smarty.get.v eq ''} class="selected"{/if}>
                <a href="{$site_root_path}?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">Dashboard</a>
              </li>
        {/if}
        {if $sidebar_menu}
          {foreach from=$sidebar_menu key=smkey item=sidebar_menu_item name=smenuloop}
          {if !$sidebar_menu_item->parent}
                <li{if $smarty.get.v eq $smkey OR $parent eq $smkey} class="selected"{/if}>
                {* TODO: Remove this logic from the view *}
                {if $parent eq $smkey}{assign var="parent_name" value=$sidebar_menu_item->name}{/if}
                <a href="{$site_root_path}?v={$smkey}&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">{$sidebar_menu_item->name}</a></li>
             {/if}
            {/foreach}

        {/if}
        {if $instance}
        </ul>
      </div>
        {/if}
    </div>

    <div class="thinkup-canvas round-all grid_20 alpha omega prepend_20 append_20" style="min-height:340px">
      <div class="prefix_1 suffix_1">

        {include file="_usermessage.tpl"}

        {if $instance}
          <!--begin public user dashboard-->
          {if $user_details}
            <div class="grid_18 alpha omega">
              <div class="clearfix alert stats round-all" id="">
                <div class="grid_2 alpha">
                  <div class="avatar-container">
                    <img src="{$user_details->avatar}" class="avatar2"/>
                    <img src="{$site_root_path}plugins/{$user_details->network|get_plugin_path}/assets/img/favicon.png" class="service-icon2"/>
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
          {else} <!-- else if $data_template -->
          
            {if $hot_posts|@count > 3}
        <div class="section">
                <h2>Response Rates</h2>
                <div class="clearfix article">

                    <div id="hot_posts"></div>

                </div>
        </div>
            {/if}


            {if $least_likely_followers}
              <div class="clearfix section">
                <h2>This Week's Most Discerning Followers</h2>
                <div class="clearfix article" style="padding-top : 0px;">
                {foreach from=$least_likely_followers key=uid item=u name=foo}
                  <div class="avatar-container" style="float:left;margin:7px;">
                    <a href="https://twitter.com/intent/user?user_id={$u.user_id}" title="{$u.user_name} has {$u.follower_count|number_format} followers and {$u.friend_count|number_format} friends"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.png" class="service-icon2"/></a>
                  </div>
                {/foreach}
                <br /><br /><br />    
                </div>
                <div class="clearfix view-all">
                    <a href="{$site_root_path}?v=followers-leastlikely&u={$instance->network_username}&n={$instance->network}">More..</a>
                </div>
                </div>
            {/if}


            {if $click_stats|@count > 3}
        <div class="section">
                <h2>Clickthrough Rates</h2>
                <div class="clearfix article">
                {assign var="ra_max" value=0}
                {foreach from=$click_stats key=post_id item=post name=foo}
                    {assign var="ra_count" value="`$post.click_count`"}
                    {if $ra_max < $ra_count}
                        {assign var="ra_max" value=$ra_count}
                    {/if}
                {/foreach}
                <img width="680" height="280" src="http://chart.googleapis.com/chart?chxs=0,,11&chxt=y&chxl=0:|{foreach from=$click_stats|@array_reverse key=post_id item=post name=foo}{$post.post_text|replace:'|':''|strip_tags|truncate:50|urlencode}|{/foreach}&chd=t:{foreach from=$click_stats key=post_id item=post name=foo}{$post.click_count}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chds=0,{$ra_max+5}&chbh=a&chco=3C8ECC&&chdl=Clicks&chs=700x280&cht=bhs&chm=N*s*,666666,-1,-1,11,,e:2:0">
                </div>
        </div>
            {/if}

            {if $most_replied_to_1wk}
              <div class="section">
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
              <div class="section">
                <h2>This Week's Most {if $instance->network eq 'google+'}+1ed{else}Liked{/if} Posts</h2>
                {foreach from=$most_faved_1wk key=tid item=t name=foo}
                  {include file="_post.counts_no_author.tpl" post=$t headings="NONE" show_favorites_instead_of_retweets=true}
                {/foreach}
              </div>
            {/if}

            {if $follower_count_history_by_day.history && $follower_count_history_by_week.history}
              
                <div class="section" style="float : left; clear : none; width : 345px;">
                  <h2>
                    {if $instance->network eq 'twitter'}Followers {elseif $instance->network eq 'facebook page'}Fans {elseif $instance->network eq 'facebook'}Friends {/if}By Day
                    {if $follower_count_history_by_day.trend}
                        ({if $follower_count_history_by_day.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_day.trend|number_format}</span>/day)
                    {/if}
                  </h2>
                  {if !$follower_count_history_by_day.history OR $follower_count_history_by_day.history|@count < 2}
                    <div class="alert helpful">Not enough data to display chart</div>
                  {else}
                      <div class="article">
                        <div id="follower_count_history_by_day"></div>
                    </div>
                    <div class="view-all">
                    <a href="{$site_root_path}?v={if $instance->network neq 'twitter'}friends{else}followers{/if}&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">More...</a>
                  </div>
                    
                  {/if}
                </div>
                <div class="section" style="float : left; clear : none;margin-left : 16px; width : 345px;">
                  <h2>
                    {if $instance->network eq 'twitter'}Followers {elseif $instance->network eq 'facebook page'}Fans {elseif $instance->network eq 'facebook'}Friends {/if} By Week
                    {if $follower_count_history_by_week.trend != 0}
                        ({if $follower_count_history_by_week.trend > 0}<span style="color:green">+{else}<span style="color:red">{/if}{$follower_count_history_by_week.trend|number_format}</span>/week)
                    {/if}
                  </h2>
                  {if !$follower_count_history_by_week.history OR $follower_count_history_by_week.history|@count < 2}
                      <div class="alert helpful">Not enough data to display chart</div>
                  {else}
                    <div class="article">
                        <div id="follower_count_history_by_week"></div>
                    </div>
                    {if $follower_count_history_by_week.milestone and $follower_count_history_by_week.milestone.will_take > 0}
                    <div class="stream-pagination"><small style="color:gray">
                        <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.will_take} week{if $follower_count_history_by_week.milestone.will_take > 1}s{/if}</span> till you reach <span style="background-color:#FFFF80;color:black">{$follower_count_history_by_week.milestone.next_milestone|number_format} followers</span> at this rate.
                    </small></div>
                    {/if}
                  <div class="view-all">
                    <a href="{$site_root_path}?v={if $instance->network neq 'twitter'}friends{else}followers{/if}&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">More...</a>
                  </div>
                  {/if}
                </div>

            {/if}

            {if $most_retweeted_1wk}
              <div class="clearfix section">
                <h2>This Week's Most {if $instance->network eq 'google+'}Reshared{else}Retweeted{/if} Posts</h2>
                {foreach from=$most_retweeted_1wk key=tid item=t name=foo}
                  {include file="_post.counts_no_author.tpl" post=$t show_favorites_instead_of_retweets=false}
                {/foreach}
              </div>
            {/if}
            {if $instance->network eq 'twitter' && $recent_posts|@count > 0 }
              <div class="section" style="float : left; clear : none; width : 314px;">
                  <div class="alpha">
                      <h2>Post Types</span></h2>
                      <div class="small prepend article">
                        <div id="post_types"></div>
                       </div>
                       <div class="stream-pagination"><small style="color:#666;padding:5px;">
                       {$instance->percentage_replies|round}% posts are replies<br>
                          {$instance->percentage_links|round}% posts contain links
                          </small>
                       </div>
                </div>
            </div>
                
            <div class="section" style="float : left; clear : none;margin-left : 10px; width : 380px;">
                   <div class="omega">
                        <h2>Client Usage <span class="detail">(all posts)</span></h2>
                        <div class="article">
                        <div id="client_usage"></div>
                        </div>
                        <div class="stream-pagination">
                        <small style="color:#666;padding:5px;">Recently posting about {$instance->posts_per_day|round} times a day{if $latest_clients_usage}, mostly using {foreach from=$latest_clients_usage key=name item=num_posts name=foo}{$name}{if !$smarty.foreach.foo.last} and {/if}{/foreach}{/if}</small>
                        </div>
                   </div>
              </div>

            {/if}
          {/if} <!-- end if $data_template -->
        {/if}

        {if !$instance}
          <div style="width:60%;text-align:center;">
          {if $add_user_buttons}
          <br ><br>
            {foreach from=$add_user_buttons key=smkey item=button name=smenuloop}
                <div style="float:right;padding:5px;"><a href="{$site_root_path}account/?p={$button}" class="linkbutton emphasized">Add a {if $button eq 'googleplus'}Google+{else}{$button|ucwords}{/if} Account &rarr;</a></div>
                <div style="clear:both;">&nbsp;</div>
             {/foreach}
          {/if}
          {if $logged_in_user}
          <div style="float:right;padding:5px;"><a href="{$site_root_path}account/" class="linkbutton emphasized">Adjust Your Settings</a></div>
          {else}
          <div style="float:right;padding:5px;"><a href="{$site_root_path}session/login.php" class="linkbutton emphasized">Log In</a></div>
          {/if}
          </div>
        {/if}

      </div> <!-- /.prefix_1 -->
    </div> <!-- /.thinkup-canvas -->

  </div> <!-- /.clearfix -->
</div> <!-- /.container_24 -->

<script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>

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
                          titleTextStyle: {color: '#999', fontSize: 16},
                          width: 325,
                          height: 250,
                          legend: "none",
                          interpolateNulls: true,
                          pointSize: 2,
                          hAxis: {
                              baselineColor: '#eee',
                              format: 'MMM d',
                              textStyle: { color: '#999' },
                              gridlines: { color: '#eee' }
                          },
                          vAxis: {
                              baselineColor: '#eee',
                              textStyle: { color: '#999' },
                              gridlines: { color: '#eee' }
                          },
                      },
                  });
                  follower_count_history_by_day_chart.draw();

                  var follower_count_history_by_week_chart = new google.visualization.ChartWrapper({
                      containerId: 'follower_count_history_by_week',
                      chartType: 'LineChart',
                      dataTable: follower_count_history_by_week_data,
                      options: {
                          titleTextStyle: {color: '#999', fontSize: 16},
                          width: 325,
                          height: 250,
                          legend: "none",
                          interpolateNulls: true,
                          pointSize: 2,
                          hAxis: {
                              baselineColor: '#eee',
                              format: 'MMM d',
                              textStyle: { color: '#999' },
                              gridlines: { color: '#eee' }
                          },
                          vAxis: {
                              baselineColor: '#eee',
                              textStyle: { color: '#999' },
                              gridlines: { color: '#eee' }
                          },
                      },
                  });
                  follower_count_history_by_week_chart.draw();

                  if (typeof(replies) != 'undefined') {
                    var post_types = new google.visualization.DataTable();
                    post_types.addColumn('string', 'Type');
                    post_types.addColumn('number', 'Percentage');
                    post_types.addRows([
                        ['Conversationalist', {v: replies/100, f: replies + '%'}], 
                        ['Broadcaster', {v: links/100, f: links + '%'}]
                    ]);

                    var post_type_chart = new google.visualization.ChartWrapper({
                        containerId: 'post_types',
                        chartType: 'BarChart',
                        dataTable: post_types,
                        options: {
                            title: 'Post Types',
                            titleTextStyle: {color: '#848884', fontSize: 19},
                            colors: ['#3c8ecc'],
                            width: 350,
                            height: 200,
                            legend: 'none',
                            hAxis: {
                                minValue: 0,
                                maxValue: 1,
                                format:'#,###%',
                                textStyle: { color: '#666' },
                            },
                            vAxis: {
                                textStyle: { color: '#666' },
                                gridlines: { color: '#ccc' },
                                baselineColor: '#ccc',
                            },
                        }
                    });
                    post_type_chart.draw();
                  }

                  var hot_posts_chart = new google.visualization.ChartWrapper({
                      containerId: 'hot_posts',
                      chartType: 'BarChart',
                      dataTable: hot_posts_data,
                      options: {
                          colors: ['#3e5d9a', '#3c8ecc'],
                          isStacked: true,
                          width: 650,
                          height: 250,
                          chartArea:{left:300,height:"80%"},
                          legend: 'bottom',
                          hAxis: {
                            textStyle: { color: '#fff', fontSize: 1 }
                          },
                          vAxis: {
                            minValue: 0,
                            baselineColor: '#ccc',
                            textStyle: { color: '#999' },
                            gridlines: { color: '#eee' }
                          },
                      }
                  });
                  hot_posts_chart.draw();

                  var client_usage_chart = new google.visualization.ChartWrapper({
                      containerId: 'client_usage',
                      // chartType: 'ColumnChart',
                      chartType: 'PieChart',
                      dataTable: client_usage_data,
                      options: {
                          titleTextStyle: {color: '#848884', fontSize: 19},
                          width: 350,
                          height: 300,
                          sliceVisibilityThreshold: 1/100,
                          pieSliceText: 'label',
                      }
                  });
                  client_usage_chart.draw();
                }

                  {/literal}
                </script>


{include file="_footer.tpl"}
