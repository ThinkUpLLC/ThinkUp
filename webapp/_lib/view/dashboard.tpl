{include file="_header.tpl" enable_bootstrap=$enable_bootstrap}
{include file="_statusbar.tpl" enable_bootstrap=$enable_bootstrap}

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
                <a href="{$site_root_path}dashboard.php?u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">Dashboard</a>
              </li>
        {/if}
        {if $sidebar_menu}
          {foreach from=$sidebar_menu key=smkey item=sidebar_menu_item name=smenuloop}
          {if !$sidebar_menu_item->parent}
                <li{if $smarty.get.v eq $smkey OR $parent eq $smkey} class="selected"{/if}>
                {* TODO: Remove this logic from the view *}
                {if $parent eq $smkey}{assign var="parent_name" value=$sidebar_menu_item->name}{/if}
                <a href="{$site_root_path}dashboard.php?v={$smkey}&u={$instance->network_username|urlencode}&n={$instance->network|urlencode}">{$sidebar_menu_item->name}</a></li>
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
        {if $show_update_now_button eq true}
        <br>
        <a href="{$site_root_path}crawler/updatenow.php{if $developer_log}?log=full{/if}" class="linkbutton emphasized">Capture Data Now</a>
        {/if}

        {if $instance}
          {* begin public user dashboard *}
          {if $user_details}
            <div class="grid_18 alpha omega">
              <div class="clearfix alert stats round-all" id="">
                <div class="grid_2 alpha">
                  <div class="avatar-container">
                    <img src="{$user_details->avatar}" class="avatar2" width="48" height="48"/>
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

          {if $data_template}
            {include file=$data_template}
          {else} {* else if no $data_template *}

              {if $instance->network eq 'foursquare'}
               {if $checkins_map|count_characters neq 0}
                   <div class="section">
                       <h2>This Week's Checkins Map</h2>
                       <div class="clearfix article">
                       <center><img src="{$checkins_map}"></center>
                       </div>
                   </div>
               {/if}

               {if $checkins_per_hour|count_characters neq 0}
                    {include file="_dashboard.checkinsperhour.tpl"}
               {/if}

               {if $checkins_by_type_last_week|count_characters neq 0 && $checkins_by_type|count_characters neq 0}
                   <div class="section" style="float : left; clear : none; width : 345px;">
                       {include file="_dashboard.checkinplacetypeslastweek.tpl"}
                   </div>
                   <div class="section" style="float : left; clear : none;margin-left : 16px; width : 345px;">
                       {include file="_dashboard.checkinplacetypesalltime.tpl"}
                   </div>
               {/if}
             {/if}

            {if $hot_posts_data && $instance->network neq 'foursquare'}
                <div class="section">
                {include file="_dashboard.responserates.tpl"}
                </div>
            {/if}

            {if $yearly_popular && $instance->network eq 'twitter'}
                <div class="section">
                <h2>Your Most Popular Tweets of {$yearly_popular_year}</h2>
                {foreach from=$yearly_popular key=tid item=t name=foo}
                    {include file="_post.counts_no_author.tpl" post=$t headings="NONE"}
                {/foreach}
                <div class="clearfix view-all">
                    <a href="{$site_root_path}dashboard.php?v=years_most_popular&u={$instance->network_username}&n={$instance->network}&y={$yearly_popular_year}">More...</a>
                </div>
                </div>
            {/if}

            {if $least_likely_followers}
              <div class="clearfix section">
                <h2>This Week's Most Discerning Followers</h2>
                <div class="clearfix article" style="padding-top : 0px;">
                {foreach from=$least_likely_followers key=uid item=u name=foo}
                  {if !$smarty.foreach.foo.last}
                  <div class="avatar-container" style="float:left;margin:7px;">
                    <a href="https://twitter.com/intent/user?user_id={$u.user_id}" title="{$u.user_name} has {$u.follower_count|number_format} followers and {$u.friend_count|number_format} friends"><img src="{$u.avatar}" class="avatar2" width="48" height="48"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.png" class="service-icon2"/></a>
                  </div>
                  {/if}
                {/foreach}
                <br /><br /><br />
                </div>
                <div class="clearfix view-all">
                    <a href="{$site_root_path}dashboard.php?v=followers-leastlikely&u={$instance->network_username}&n={$instance->network}">More...</a>
                </div>
                </div>
            {/if}

            {if $click_stats_data}
                <div class="section">
                {include file="_dashboard.clickthroughrates.tpl"}
                </div>
            {/if}

            {if $instance->network eq "foursquare"}
               <style type="text/css">
                {literal}
                .map-image-container { width: 130px; height: 130px; padding-bottom : 30px; }
                img.map-image2 {float:left;margin:6px 0 0 0;width:150px;height:150px;}
                img.place-icon2 {position: relative;width: 32px;height: 32px;top: -146px;left: 5px;}
                {/literal}
                </style>
            {/if}

            {if $most_replied_to_1wk && $instance->network neq 'foursquare'}
              <div class="section">
                <h2>This Week's Most {if $instance->network eq 'google+'}Discussed{else}Replied-To{/if} Posts</h2>
                {foreach from=$most_replied_to_1wk key=tid item=t name=foo}
                    {if $instance->network eq "twitter"}
                        {include file="_post.counts_no_author.tpl" post=$t headings="NONE"}
                    {elseif $instance->network eq 'foursquare'}
                        {include file="_post.checkin.tpl" post=$t}
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
                    {include file="_dashboard.followercountbyday.tpl"}
                </div>
                <div class="section" style="float : left; clear : none;margin-left : 16px; width : 345px;">
                    {include file="_dashboard.followercountbyweek.tpl"}
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

            {if $instance->network eq 'twitter' }
                <div class="section" style="float : left; clear : none; width : 345px;">
                  {include file="_dashboard.posttypes.tpl"}
                </div>
                <div class="section" style="float : left; clear : none;margin-left : 10px; width : 345px;">
                    {include file="_dashboard.clientusage.tpl"}
                </div>
            {/if}

            {if $posts_flashback|@count > 0 }
            <div class="section">
                <h2>Time Machine: On This Day In Years Past</h2>
                {if $instance->network eq 'foursquare'}
                    {foreach from=$posts_flashback item=post name=foo}
                        {include file="_post.checkin.tpl" }
                    {/foreach}
                {else}
                    {foreach from=$posts_flashback key=tid item=post name=foo}
                      {include file="_post.counts_no_author.tpl" post=$post show_favorites_instead_of_retweets=false}
                    {/foreach}
                {/if}
            </div>
           {/if} 

          {/if} {* end if $data_template *}
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


{if $smarty.get.v eq "insights"}
    {include file="_footer.tpl" enable_bootstrap=1}
{else}
    {include file="_footer.tpl"}
{/if}
