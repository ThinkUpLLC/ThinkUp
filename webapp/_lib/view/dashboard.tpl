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
          {if $instance->network eq 'foursquare'}
          
         <!--  If the user has checkins from this day last year show them -->
        {if $checkins_one_year_ago|@count > 0 }
		<div class="section">
            <h2>Remember These?</h2>
              
            {foreach from=$checkins_one_year_ago item=current}
            <div class="clearfix article"> 
            <div class="individual-tweet post clearfix">
            <div class="grid_5 alpha">
            <a href="http://maps.google.co.uk/maps?q={$current->geo}"><img src="{$current->place_obj->map_image}"></a>
            </div>	
            <div class="grid_7"> 
            <img src="{$current->place_obj->icon}"> {$current->place} <br> {$current->location} <br>
            
             {foreach from=$current->links item=current_link}
             <a href="{$current_link->url}"><img src="{$current_link->url}" width=100px height=100px}></a>
             
             {/foreach}
            
            </div>
            <div class="grid_5 omega"/> {$current->post_text} <br> <br> {$current->pub_date} <br>
            
            {if $current->reply_count_cache > 0}
                <span class="reply-count">
                <a href="{$site_root_path}post/?t={$current->post_id}&n={$current->network|urlencode}">{$current->reply_count_cache|number_format}</a></span>
              {else}
                &#160;
              {/if}
            
            </div>
            </div>
            	<br>
            </div>

            {/foreach}
        </div>
       {/if} 
        
        {if $checkins_per_hour_last_week|@count > 3 }
        <div class="section">
              <h2>Check-ins Per Hour - This Week</h2>
              
              <img width="680" height="280" src="https://chart.googleapis.com/chart?cht=bvs&amp;chco=7CC0D7&amp;chd=t:{foreach from=$checkins_per_hour_last_week name=foo item=lastweek}{$lastweek.counter|urlencode}{if !$smarty.foreach.foo.last}%2C{/if}{/foreach}&amp;chbh=a&amp;chxt=x,y&amp;chxl=0:|{foreach from=$checkins_per_hour_last_week name=foo item=lastweek2}{$lastweek2.hour|urlencode}{if !$smarty.foreach.foo.last}%7C{/if}{/foreach}&amp;chs=680x280&amp;chtt=Check-ins+Per+Hour+-+This+Week&amp;chds=a" >
        </div>
        {/if}
        {if $checkins_per_hour_all_time|@count > 3 }
        <div class="section">
              <h2>Check-ins Per Hour - All Time</h2>
              
        	  <img width="680" height="280" src="https://chart.googleapis.com/chart?cht=bvs&amp;chco=7CC0D7&amp;chd=t:{foreach from=$checkins_per_hour_all_time name=foo item=alltime}{$alltime.counter|urlencode}{if !$smarty.foreach.foo.last}%2C{/if}{/foreach}&amp;chbh=a&amp;chxt=x,y&amp;chxl=0:|{foreach from=$checkins_per_hour_all_time name=foo item=alltime2}{$alltime2.hour|urlencode}{if !$smarty.foreach.foo.last}%7C{/if}{/foreach}&amp;chs=680x280&amp;chtt=Check-ins+Per+Hour+-+All+Time&amp;chds=a" >
              

        </div>
        {/if}
        {if $checkins_by_type|@count > 0 }
        <div class="section">
              <h2>The Types Of Places You Visit</h2>
              
              <img width="680" height="280" src="https://chart.googleapis.com/chart?chds=a&amp;chd=t:{foreach from=$checkins_by_type name=foo item=placecount}{$placecount.place_count|urlencode}{if !$smarty.foreach.foo.last}%2C{/if}{/foreach}&amp;cht=p&amp;chl={foreach from=$checkins_by_type name=foo item=placecount}{$placecount.place_type|urlencode}{if !$smarty.foreach.foo.last}%7C{/if}{/foreach}&amp;chtt=Types+of+Places+You+Visit&amp;chs=700x280&chco=7CC0D7,D5F0FC"> 

        </div>
		 {/if}
		 {else}
        
            {if $hot_posts|@count > 3}
        <div class="section">
                <h2>Response Rates</h2>
                <div class="clearfix article">
                    {assign var="ra_max" value=0}
                    {foreach from=$hot_posts key=post_id item=post name=foo}
                        {assign var="ra_count" value="`$post->favlike_count_cache+$post->reply_count_cache+$post->all_retweets`"}
                        {if $ra_max < $ra_count}
                            {assign var="ra_max" value=$ra_count}
                        {/if}
                    {/foreach}
                    {if $instance->network neq "twitter"}
                        <img width="680" height="280" src="http://chart.googleapis.com/chart?chxs=0,,11&chxt=y&chxl=0:|{foreach from=$hot_posts|@array_reverse key=post_id item=post name=foo}{if $post->post_text}{$post->post_text|replace:'|':''|strip_tags|truncate:50|urlencode}{elseif $post->link->title}{$post->link->title|replace:'|':''|truncate:50|urlencode}{elseif $post->link->url}{$post->link->url|replace:'|':''|truncate:50|urlencode}{else}{$post->pub_date|date_format:"%b %e"}{/if}|{/foreach}&chd=t:{foreach from=$hot_posts key=post_id item=post name=foo}{if $post->favlike_count_cache > 0}{$post->favlike_count_cache}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}|{foreach from=$hot_posts key=post_id item=post name=foo}{if $post->reply_count_cache > 0}{$post->reply_count_cache}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}|{foreach from=$hot_posts key=post_id item=post name=foo}{if $post->all_retweets > 0}{$post->all_retweets}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chds=0,{$ra_max+5}&chbh=a&chco=3E5D9A,3C8ECC,BBCCDD&&chdl={if $instance->network eq 'google+'}%2B1s{else}Likes{/if}|Replies|Shares&chs=700x280&cht=bhs&chm=N*s*,666666,-1,-1,11,,e:2:0">                        
                    {else}
                        <img width="680" height="280" src="http://chart.googleapis.com/chart?chxs=0,,11&chxt=y&chxl=0:|{foreach from=$hot_posts|@array_reverse key=post_id item=post name=foo}{$post->post_text|replace:'|':''|truncate:50|urlencode}|{/foreach}&chd=t:{foreach from=$hot_posts key=post_id item=post name=foo}{if $post->all_retweets > 0}{$post->all_retweets}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}|{foreach from=$hot_posts key=post_id item=post name=foo}{if $post->reply_count_cache > 0}{$post->reply_count_cache}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chds=0,{$ra_max+5}&chbh=a&chco=3E5D9A,3C8ECC&chdl=Retweets|Replies&chs=700x280&cht=bhs&chm=N*s*,666666,-1,-1,11,,e:2:0">
                    {/if}
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
                    <img width="320" height="200" src="http://chart.apis.google.com/chart?chs=320x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{$tid|date_format:"%b %d"}|{/foreach}1:|{foreach from=$follower_count_history_by_day.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=bvs&chco=7DD3F0&chd=t:{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{if $t > 0}{$t}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chbh=a&chds={$follower_count_history_by_day.min_count},{$follower_count_history_by_day.max_count}&chxr={$follower_count_history_by_day.min_count},{$follower_count_history_by_day.max_count}&chxs=1N*s*&chm=N*s*,666666,0,-1,10,,e::5" />
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
                        <img width="320" height="200" src="http://chart.apis.google.com/chart?chs=320x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_week.history key=tid item=t name=foo}{$tid|date_format:"%b %d"}|{/foreach}1:|{foreach from=$follower_count_history_by_week.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=bvs&chco=7DD3F0&chd=t:{foreach from=$follower_count_history_by_week.history key=tid item=t name=foo}{if $t > 0}{$t}{else}_{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chbh=a&chds={$follower_count_history_by_week.min_count},{$follower_count_history_by_week.max_count}&chxr={$follower_count_history_by_week.min_count},{$follower_count_history_by_week.max_count}&chxs=1N*s*&chm=N*s*,666666,0,-1,10,,e::5" />
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
                          <img width="250" height="175" src="http://chart.apis.google.com/chart?chxt=x,y&cht=bhg&chd=t:{$instance->percentage_replies|round},{$instance->percentage_links|round}&&chco=7CC0D7&chls=2.0&chs=250x175&chxl=0:|20%|60%|100%|1:|Broadcaster|Conversationalist&chxp=0,20,60,100&chbh=50" />
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
                        <img width="350" height="200" src="http://chart.apis.google.com/chart?cht=p&chd=t:{foreach from=$all_time_clients_usage key=name item=num_posts name=foo}{if $num_posts>0}{math equation="round(x/y*100,2)" x=$num_posts y=$all_time_clients_usage|@array_sum}{else}0{/if}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chs=350x200&chl={foreach from=$all_time_clients_usage key=name item=num_posts name=foo}{$name}+({$num_posts}){if !$smarty.foreach.foo.last}|{/if}{/foreach}&chco=7CC0D7,D5F0FC">
                        </div>
                        <div class="stream-pagination">
                        <small style="color:#666;padding:5px;">Recently posting about {$instance->posts_per_day|round} times a day{if $latest_clients_usage}, mostly using {foreach from=$latest_clients_usage key=name item=num_posts name=foo}{$name}{if !$smarty.foreach.foo.last} and {/if}{/foreach}{/if}</small>
                        </div>
                   </div>
              </div>

            {/if}
            
          {/if} <!-- end if $data_template -->
          {/if}
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

{include file="_footer.tpl"}
