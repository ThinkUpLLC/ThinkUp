{include file="_public.header.tpl"}

{include file="_public.header.statusbar.tpl" mode="public"}
  <div class="thinkup-canvas round-all container_24">
    <div class="clearfix prepend_20 append_20">
      <div class="grid_22 push_1 clearfix">
        {include file="_usermessage.tpl"}
        {if $post and ($replies OR $retweets)}
          <div class="clearfix">
            <div class="grid_2 alpha">
              <img src="{$post->author_avatar}" class="avatar2">
            </div>
            <div class="{if $replies or $retweets}grid_13{else}grid_19{/if}">
              <span class="tweet">{$post->post_text|link_usernames_to_twitter}</span>
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
              <img src="{$user_details->avatar}" class="avatar2">
            </div>
            <div class="grid_19">
              <img src="{$site_root_path}assets/img/social_icons/{$user_details->network}.png">
              <span class="tweet">{$user_details->username}</span>
            </div>
         </div>
         {/if}

        {if $follower_count_history_by_day.history and $follower_count_history_by_week.history}
<br /><br />
        <table width="100%"><tr><td>
        Follower Count By Day<br />{if $follower_count_history_by_day.history|@count < 2}<i>Not enough data yet</i>{else}<br />
        <img src="http://chart.apis.google.com/chart?chs=425x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_day.history key=tid item=t name=foo}{$t.date}|{/foreach}1:|{foreach from=$follower_count_history_by_day.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=ls&chco=0077CC&chd=t:{foreach from=$follower_count_history_by_day.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,76A4FB,0,0,0&chg=33">
        {/if}
        </td><td>
        Follower Count By Week<br />{if $follower_count_history_by_week.history|@count < 2}<i>Not enough data yet</i>{else}<br />
        <img src="http://chart.apis.google.com/chart?chs=425x200&chxt=x,y&chxl=0:|{foreach from=$follower_count_history_by_week.history key=tid item=t name=foo}{$t.date}|{/foreach}1:|{foreach from=$follower_count_history_by_week.y_axis key=tid item=t name=foo}{$t|number_format}{if !$smarty.foreach.foo.last}|{/if}{/foreach}&cht=ls&chco=0077CC&chd=t:{foreach from=$follower_count_history_by_week.percentages key=tid item=t name=foo}{$t}{if !$smarty.foreach.foo.last},{/if}{/foreach}&chm=B,76A4FB,0,0,0&chg=33">
        {/if}
        </td></tr>
        </table>
        {/if}

        {if $most_replied_to_1wk}
<hr />
<h2 style="font-size:200%;margin-top:10px">This Week's Most Replied-To Posts</h2>
          {foreach from=$most_replied_to_1wk key=tid item=t name=foo}
            {include file="_post.public.tpl" t=$t headings="NONE"}
          {/foreach}
        {/if}

        {if $least_likely_followers}
<hr />
<h2 style="font-size:200%;margin-top:10px">Least Likely Followers</h2>
            {foreach from=$least_likely_followers key=uid item=u name=foo}
                <a href="http://twitter.com/{$u.user_name}" title="{$u.user_name}">
                  <img src="{$u.avatar}"  height="48" width="48" />
                </a> 
            {/foreach}
        {/if}

        {if $most_replied_to_1wk}
<hr />
<h2 style="font-size:200%;margin-top:10px">This Week's Most Retweeted</h2>
          {foreach from=$most_retweeted_1wk key=tid item=t name=foo}
            {include file="_post.public.tpl" t=$t headings="NONE"}
          {/foreach}
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