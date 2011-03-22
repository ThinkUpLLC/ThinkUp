{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div class="thinkup-canvas round-all container_24">
  <div class="clearfix">
    
    <!-- begin left nav -->
    <div class="grid_4 alpha">
      <div id="nav-sidebar">
        <ul id="top-level-sidenav"><br />
        {if $post}
          <ul class="side-subnav">
          <li><a href="{insert name=dashboard_link}">Dashboard</a></li>
          </ul>
          <li>
          Replies
          <ul class="side-subnav">
          <li{if $smarty.get.v eq ''} class="currentview"{/if}><a href="index.php?t={$post->post_id}&n={$post->network}">Post Replies&nbsp;&nbsp;&nbsp;</a></li>
          {if $logged_in_user && $post->reply_count_cache && $post->reply_count_cache > 1}
            <li id="grid_search_icon"><a href="#" class="grid_search" title="Search" onclick="return false;"><span>Search & Filter Replies</span></a></li>
          {/if}
          <li><a href="{$site_root_path}post/export.php?u={$post->author_username}&n={$post->network}&post_id={$post->post_id}&type=replies">Export Replies (CSV)</a></li>
          {if $post->reply_count_cache > $top_20_post_min}
            <li class="word_frequency"><a href="#" title="Top 20 Words" onclick="return false;"><span>Top 20 Words</span></a></li>
          {/if}
          </ul></li>
        {/if}
        
        
        {if $sidebar_menu}
          {foreach from=$sidebar_menu key=smkey item=sidebar_menu_item name=smenuloop}
            {if $sidebar_menu_item->header}</li></ul> <li>{$sidebar_menu_item->header}<ul class="side-subnav">{/if}
              <li{if $smarty.get.v eq $smkey} class="currentview"{/if}><a href="index.php?v={$smkey}&t={$post->post_id}&n={$post->network}">{$sidebar_menu_item->name}&nbsp;&nbsp;&nbsp;</a></li>
            {/foreach}
              </li>
            </ul>
        {/if}
        </ul>
      </div>
    </div>
    <!-- end left nav -->
    
    <div class="grid_20 omega prepend_20 append_20">
      {include file="_usermessage.tpl"}
      
      {if $data_template}
        {include file=$data_template}
        <div class="float-l">
          <!-- {if $next_page}
          <a href="{$site_root_path}index.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n}&{/if}page={$next_page}" id="next_page">&#60; Older Posts</a>
          {/if}
          {if $last_page}
          | <a href="{$site_root_path}index.php?{if $smarty.get.v}v={$smarty.get.v}&{/if}{if $smarty.get.u}u={$smarty.get.u}&{/if}{if $smarty.get.n}n={$smarty.get.n}&{/if}page={$last_page}" id="last_page">Newer Posts  &#62;</a>
          {/if}-->
        </div>
        
      {else}
    
        {if $post}
                
          <div class="clearfix append_20">
            <div class="grid_2 alpha">
              <div class="avatar-container">
                <img src="{$post->author_avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon2"/>
              </div>
            </div>
            
            <div class="grid_12">
              <div class="br">
                <div class="clearfix pr">
                  <div id="post_detail" class="float-r"><span class="ui-icon ui-icon-search"></span></div>
                </div>
                <div class="tweet pr">
                  {if $post->post_text}
                    {$post->post_text|link_usernames_to_twitter}
                  {else}
                    <span class="no-post-text">No post text</span>
                  {/if}
                </div>
              
                {if $post->link->expanded_url and !$post->link->is_image and $post->link->expanded_url != $post->link->url}
                  <br><a href="{$post->link->expanded_url}" title="{$post->link->expanded_url}">{$post->link->expanded_url}</a><span class="ui-icon ui-icon-newwin">
                {/if}
              
                {if $post->link->expanded_url and $post->link->is_image}<br ><br >
                  <div class="pic"><a href="{$post->link->url}"><img src="{$post->link->expanded_url}" alt=""></a></div>
                {/if}
                            
                <!-- tooltip element -->
                <div class="tooltip">
                	<img src="{$site_root_path}assets/img/social_icons/{$post->network|get_plugin_path}.png" style="float:left;margin:0 15px 20px 0" />
                	<table style="margin:0">
                		<tr>
                			<td class="label">Posted:</td>
                			<td>{$post->adj_pub_date|date_format:"%D"} @ {$post->adj_pub_date|date_format:"%I:%M %p"}</td>
                		</tr>
                		{if $post->location}
                		<tr>
                			<td class="label">From:</td>
                			<td>{$post->location}</td>
                		</tr>
                		{/if}
                		{if $post->source}
                		<tr>
                			<td class="label">Via:</td>
                			<td>
                			  {if $post->source eq 'web'}
                			    the web
                			  {else}
              			      {$post->source}<span class="ui-icon ui-icon-newwin"></span>
                			  {/if}
                			</td>
                		</tr>
                		{/if}
            			  {if $post->network eq 'twitter'}
                		<tr>
                			<td class="label">Link:</td>
                			<td>
                        <a href="http://twitter.com/{$post->author_username}/statuses/{$post->post_id}">View on Twitter</a><span class="ui-icon ui-icon-newwin"></span>
                			</td>
                		</tr>		
                    {/if}
                	</table>
                </div> <!-- /.tooltip -->
              	
                {literal}
                <script>
                  $("#post_detail").tooltip({ 
                    tip: '.tooltip',
                    effect: 'fade',
                    offset: [0, 0],
                    position: 'bottom center',
                    relative: true,
                    opacity: 1,
                    delay: 30,
                  });
                </script>
                {/literal}
                <!--{if $post->is_geo_encoded eq 1}
                <div>
                <a href="{$site_root_path}post/map.php?t=post&pid={$post->post_id}&n={$post->network}" title="Locate on Map">
                  <img src="{$site_root_path}assets/img/map_icon.png" class="map-icon map-icon-public">
                </a>
                </div>
                {/if}-->
              </div>
            </div>

            <div class="grid_5 omega center keystats">
              <div class="big-number">
                  <h1>{$post->reply_count_cache|number_format}</h1>
                  <h3>replies in {$post->adj_pub_date|relative_datetime}</h3>
              </div>
            </div>
          </div> <!-- /.clearfix -->
          {/if} <!-- end if post -->
          
          {if $replies}
            <div class="append_20 clearfix bt">
              {if $post->reply_count_cache > $top_20_post_min}
                 {include file="_post.word-frequency.tpl"}
              {/if}
              {if $replies && $logged_in_user}
                  {include file="_grid.search.tpl" version2=true}
              {/if}
              <div id="post-replies-div"><br>
                <div id="post_replies clearfix">
                {foreach from=$replies key=tid item=t name=foo}
                  {include file="_post.clean.tpl" t=$t sort='no' scrub_reply_username=true reply_count=$post->reply_count_cache}
                {/foreach}
                </div>
              </div>
              {if $post->reply_count_cache > $top_20_post_min}
                {include file="_post.word-frequency.tpl"}
                <script src="{$site_root_path}assets/js/extlib/Snowball.stemmer.min.js" type="text/javascript"></script>
                <script src="{$site_root_path}assets/js/word_frequency.js" type="text/javascript"></script>
              {/if}
              {if !$logged_in_user && $private_reply_count > 0}
                <span style="font-size:12px">Not showing {$private_reply_count} private repl{if $private_reply_count == 1}y{else}ies{/if}.</span>
              {/if}
            </div>
          {/if}
    {/if}

        <!--
        <div class="append prepend clearfix">
          <a href="{$site_root_path}index.php" class="tt-button ui-state-default tt-button-icon-left ui-corner-all">
            <span class="ui-icon ui-icon-circle-arrow-w"></span>
            Back home
          </a>
        </div>
          &nbsp;
        -->  
          
      </div>

    </div> <!-- /.clearfix -->
</div> <!-- /.thinkup-canvas -->

<script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
{if $replies && $logged_in_user}
  <script type="text/javascript">post_username = '{$post->author_username}';</script>
  <script type="text/javascript" src="{$site_root_path}assets/js/grid_search.js"></script>
{/if}
  
{include file="_footer.tpl"}