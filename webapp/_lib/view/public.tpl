{include file="_public.header.tpl"}
{include file="_public.header.statusbar.tpl" mode="public"}
  <div class="thinkup-canvas round-all container_24">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        {include file="_usermessage.tpl"}
        {if $post and ($replies OR $retweets)}
          <div class="clearfix">
            <div class="grid_2 alpha">
            <div class="avatar-container">
              <img src="{$post->author_avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$post->network}/assets/img/favicon.ico" class="service-icon2"/>
             </div>
            </div>
            <div class="{if $replies or $retweets}grid_13{else}grid_19{/if}">
              <span class="tweet">
                {if $post->post_text}
                  {$post->post_text|link_usernames_to_twitter}
                {else}
                  <span class="no-post-text">No post text</span>
                {/if}
              </span>
              {if $post->link->expanded_url and !$post->link->is_image and $post->link->expanded_url != $post->link->url}
                <br><a href="{$post->link->expanded_url}" title="{$post->link->expanded_url}">
                  {$post->link->expanded_url}
                </a>
              {/if}
              <div class="grid_10 omega small gray {if $replies or $retweets}prefix_3 prepend{else}prefix_10{/if}">
                <img src="{$site_root_path}assets/img/social_icons/{$post->network}.png" class="float-l">
                Posted at {$post->adj_pub_date} via {$post->source}<br>
                From: {$post->location}
                {if $post->is_geo_encoded eq 1}
                <div>
                <a href="{$site_root_path}map.php?t=post&pid={$post->post_id}&n={$post->network}" title="Locate on Map">
                  <img src="{$site_root_path}assets/img/map_icon.png" class="map-icon map-icon-public">
                </a>
                </div>
                {/if}
              </div>
            </div>
            <div class="grid_7 center big-number omega">
              <div class="bl">
                <div class="key-stat">
                  {if $replies}
                    <h1>{$post->reply_count_cache|number_format}</h1>
                    <h3>replies in {$post->adj_pub_date|relative_datetime}</h3>
                  {else}
                    <h1><a href="#fwds" name="fwds">{$retweets|@count|number_format}</a>
                    fwds to<br><a href="#fwds">{$rtreach|number_format}</a></h1>
                    <h3>total reach</h3>
                  {/if}
                </div>
              </div>
            </div>
          </div> <!-- end .clearfix -->
          {if $replies}
            <div class="append_20 clearfix">
              {foreach from=$replies key=tid item=t name=foo}
                {include file="_post.public.tpl" t=$t sort='no'}
              {/foreach}
            </div>
          {/if}
          <div class="append prepend clearfix">
            <a href="#" class="show_replies tt-button ui-state-default tt-button-icon-left ui-corner-all "
               style="display:none;">
              <span class="ui-icon ui-icon-circle-arrow-w"></span>
              Show All Replies
            </a>
          </div>
          <div class="clearfix">
            <div class="{if $retweets}grid_13{else}grid_19{/if}">
              <span class="tweet"></span>
              <div class="grid_10 omega small gray {if $retweets}prefix_3 prepend{else}prefix_10{/if}"></div>
            </div>
            
            {if $retweets and $replies|@count > 0}
              <div class="grid_7 center big-number omega">
                <div class="bl">
                  <div class="key-stat">
                    <h1><a href="#fwds" name="fwds">{$retweets|@count|number_format}</a>
                    fwds to<br /> <a href="#fwds">{$rtreach|number_format}</a></h1>
                    <h3>total reach</h3>
                  </div>
                </div>
              </div>
            {/if}
          </div> <!-- end .clearfix -->
          {if $retweets}
            <div class="append_20 clearfix">
              {foreach from=$retweets key=tid item=t name=foo}
                {include file="_post.public.tpl" t=$t sort='no'}
              {/foreach}
            </div>
          {/if}
          <div class="append prepend clearfix">
            <a href="#" class="show_forwards tt-button ui-state-default tt-button-icon-left ui-corner-all "
               style="display:none;">
              <span class="ui-icon ui-icon-circle-arrow-w"></span>
              Show All Forwards
            </a>
          </div>
        <div class="append prepend clearfix">
          <a href="{$site_root_path}public.php" class="tt-button ui-state-default tt-button-icon-left ui-corner-all">
            <span class="ui-icon ui-icon-circle-arrow-w"></span>
            Back to the public timeline
          </a>
        </div>
        {else}
          &nbsp;
        {/if}
        {if $posts}{include file="_pagination.tpl"}{/if}
        {if $header}<h1>{$header}</h1>{/if}
        {if $description}<h4>{$description}</h4>{/if}
        {if $posts}
          {foreach from=$posts key=tid item=t name=foo}
            {include file="_post.public.tpl" t=$t username_link='internal'}
          {/foreach}
          {include file="_pagination.tpl"}
        {/if}

        {if $instance}
        <!--begin public user dashboard-->
          {if $user_details}
          <div class="clearfix">
            <div class="grid_2 alpha">
              <div class="avatar-container">
              <img src="{$user_details->avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$user_details->network}/assets/img/favicon.ico" class="service-icon2"/>
              </div>
            </div>
            <div class="grid_19">
              <span class="tweet">{$user_details->username} on {$user_details->network|capitalize}</span><br />
              <small>Recently posting about {$instance->posts_per_day|round} times a day, mostly using {foreach from=$latest_clients_usage key=name item=num_posts name=foo}{$name}{if !$smarty.foreach.foo.last} and {/if}{/foreach}</small>
            </div>
         </div>
         {/if}

        {if $recent_posts}
<br /><br />
          {foreach from=$recent_posts key=tid item=t name=foo}
            {include file="_post.public.tpl" t=$t headings="NONE"}
          {/foreach}
        {/if}

        {if $follower_count_history_by_day.history and $follower_count_history_by_week.history}
<br /><br />
        <table width="100%"><tr><td>
        Follower Count By Day{if $follower_count_history_by_day.history|@count < 2}<br /><i>Not enough data yet</i>{else} {if $follower_count_by_day_trend != 0}({if $follower_count_by_day_trend > 0}<span style="color:green">+{else}<span style="color:green">-{/if}{$follower_count_by_day_trend|round|number_format}</span> per day){/if}<br />
        <img src="http://chart.apis.google.com/chart?chs=425x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{$t.date}|{/foreach}1:|{foreach from=$follower_count_history_by_day.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=ls&chco=0077CC&chd=t:{foreach from=$follower_count_history_by_day.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,76A4FB,0,0,0&chg=33">
        {/if}
        </td><td>
        Follower Count By Week{if $follower_count_history_by_week.history|@count < 2}<br /><i>Not enough data yet</i>{else} {if $follower_count_by_week_trend != 0}({if $follower_count_by_week_trend > 0}<span style="color:green">+{else}<span style="color:green">-{/if}{$follower_count_by_week_trend|round|number_format}</span> per week){/if}<br />
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
            {include file="_post.public.tpl" t=$t headings="NONE"}
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
          <img src="http://chart.apis.google.com/chart?cht=p&chd=t:{foreach from=$all_time_clients_usage key=name item=num_posts name=foo}{math equation="round(x/y*100,2)" x=$num_posts y=$all_time_clients_usage|@array_sum}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chs=425x200&chl={foreach from=$all_time_clients_usage key=name item=num_posts name=foo}{$name}+({$num_posts}){if !$smarty.foreach.foo.last}|{/if}{/foreach}&chco=76A4FB">
        </td></tr></table>
        </td></tr></table>
        {/if}

        {if $most_retweeted_1wk}
<hr />
<h2 style="font-size:200%;margin-top:10px">This Week's Most Retweeted</h2>
          {foreach from=$most_retweeted_1wk key=tid item=t name=foo}
            {include file="_post.public.tpl" t=$t headings="NONE"}
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
            {include file="_post.public.tpl" t=$t headings="NONE"}
          {/foreach}
        {/if}

        {if $most_retweeted_alltime}
<hr />
<h2 style="font-size:200%;margin-top:10px">All-Time Most Retweeted</h2>
          {foreach from=$most_retweeted_alltime key=tid item=t name=foo}
            {include file="_post.public.tpl" t=$t headings="NONE"}
          {/foreach}
        {/if}
        
        <div class="append prepend clearfix">
          <a href="{$site_root_path}public.php" class="tt-button ui-state-default tt-button-icon-left ui-corner-all">
            <span class="ui-icon ui-icon-circle-arrow-w"></span>
            Back to the public timeline
          </a>
        </div>

        {/if}
      </div>
    </div>
  </div> <!-- end .thinkup-canvas -->

  <script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
  <script type="text/javascript" src="{$site_root_path}assets/js/easytooltip.js"></script>
  <script type="text/javascript" src="{$site_root_path}plugins/geoencoder/assets/js/locationfilter.js"></script>
  
{include file="_footer.tpl" stats="no"}