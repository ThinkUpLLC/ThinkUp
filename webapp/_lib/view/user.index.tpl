{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div class="container_24">
  <div class="clearfix">
    
    <div class="grid_4 alpha omega" style="background-color:#e6e6e6"> <!-- begin left nav -->
      <div id="nav-sidebar">
        <ul id="top-level-sidenav"><br />
          
          <ul class="side-subnav">
            <li><br><a href="{insert name=dashboard_link}">Dashboard</a></li>
            <li>User</li>
              <li class="currentview"><a href="">Detail</a></li>
          </ul>
          
        </ul>
      </div> <!-- /#nav-sidebar -->
    </div>
    <!-- end sidebar column -->
    
    <!-- content canvas -->
    <div class="thinkup-canvas round-all grid_20 alpha omega prepend_20 append_20" style="min-height:340px">
      <div class="prefix_1">
        {include file="_usermessage.tpl"}

        <!-- begin user detail -->
        <div class="clearfix">
          <div class="grid_2 alpha">
            <div class="avatar-container">
              <img src="{$profile->avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$profile->network|get_plugin_path}/assets/img/favicon.png" class="service-icon2"/>
            </div>
          </div>
          <div class="grid_12">
            <div class="br" style="min-height:110px">
              <div class="pr">
                <span class="user">
                  <h1>{$profile->username}</h1>
                  {if $profile->description}
                    <p>{$profile->description}</p>
                  {/if}
                  {if $profile->location}<br>Location: {$profile->location}{/if}
                  {if $profile->url}<br>URL: <a href="{$profile->url}">{$profile->url}</a>{/if}
                </span>
              </div>
            </div>
          </div>

          <div class="grid_5 omega keystats">
            <div class="padding">
              <span class="gray">Posts:</span> {$profile->post_count|number_format}<br>
              <span class="gray">Conversations:</span> {$total_exchanges}<br>
              <span class="gray">Followers:</span> {$profile->follower_count|number_format}<br>
              <span class="gray">Friends:</span> {$profile->friend_count|number_format}<br>
              <span class="gray">Mutual friends:</span> {$total_mutual_friends}<br>
              <span class="gray">Joined {$profile->name}:</span> <!--{$profile->joined|relative_datetime} on -->{$profile->joined|date_format:"%D"}<br>
            </div>
          </div>
        </div> <!-- end .clearfix -->
        <!-- end user detail -->

        <!-- begin user content -->
        {if $mutual_friends}
        <div class="clearfix">
            <h2>Mutual friends</h2>
            <div class="grid_18 alpha omega">
            {foreach from=$mutual_friends key=uid item=u name=foo}
            <div class="avatar-container float-l mr_10 mb_10">  
               <a href="https://twitter.com/intent/user?user_id={$u.user_id}" title="{$u.user_name}"><img src="{$u.avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$u.network}/assets/img/favicon.png" class="service-icon2"/></a> 
            </div>
            {/foreach}
            </div>
        </div>
        {/if}

        {if $user_statuses}
        <div class="clearfix">
            <h2>Posts</h2>
            {foreach from=$user_statuses key=tid item=t name=foo}
              {include file="_post.counts_no_author.tpl" post=$t}
            {/foreach}

           <div class="float-l">
           {if $next_page}
               <a href="{$site_root_path}user/?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n}&{/if}page={$next_page}" id="next_page">&#60; Older Posts</a>
           {/if}
           {if $last_page}
               | <a href="{$site_root_path}user/?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n}&{/if}page={$last_page}" id="last_page">Newer Posts  &#62;</a>
           {/if}
           </div>
        </div>
        {/if}

        {if $sources}
          <h2>Client usage</h2>
          {if count($sources > 0)}
            {foreach from=$sources key=tid item=s name=foo}
              <div class="clearfix">
                <div class="grid_12 bold">{$s.total} post{if $s.total > 1}s{/if} via</div>
                <div class="grid_6 right">{if $s.source eq 'web'} the {$s.source}{else}{$s.source}{/if}</div>
              </div>
            {/foreach}
          {/if}
        {/if}
        
        {if $profile->avg_tweets_per_day}
          <h2>Average posts per day</h2>
          <div class="clearfix bt">
            <div class="grid_9 bold alpha">Averages</div>
            <div class="grid_10 right omega">{$profile->avg_tweets_per_day} updates per day</div>
          </div>
        {/if}
                    
        {if $exchanges}
          <h2>Exchanges</h2>
            {foreach from=$exchanges key=tahrt item=r name=foo}
              {include file="_post.qa.tpl" t=$t}
            {/foreach}
        {/if}
        
      </div>
    </div>
  </div>
</div> <!-- /.thinkup-canvas -->

{include file="_footer.tpl"}