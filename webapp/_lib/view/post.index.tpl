{include file="_header.tpl"}
{include file="_statusbar.tpl"}

  <div class="thinkup-canvas round-all container_24">
    <div class="clearfix prepend_20 append_20">
    
      <div class="clearfix prefix_1 suffix_1">
        {include file="_usermessage.tpl"}
        
        {if $post}
        
<div class="clearfix"> <!-- POST DETAILS -->
          
            <div class="grid_2 alpha">
              <div class="avatar-container">
                <img src="{$post->author_avatar}" class="avatar2"/><img src="{$site_root_path}plugins/{$post->network|get_plugin_path}/assets/img/favicon.ico" class="service-icon2"/>
              </div>
            </div> <!-- end .grid_2 -->
            
            <div class="grid_11">
            
              <div class="tweet clearfix">

                {if $post->link->is_image}
                  <div class="pic float-r ml_10"><a href="{$post->link->url}"><img src="{$post->link->expanded_url}" /></a></div>
                {/if}
              
                <!-- POST -->
                {if $post->post_text}
                  {$post->post_text|link_usernames_to_twitter}
                {else}
                  <span class="no-post-text">No post text</span>
                {/if}
              
                <!-- LINK -->
                {if $post->link->expanded_url and !$post->link->is_image and $post->link->expanded_url != $post->link->url}
                  <br>
                  <a href="{$post->link->expanded_url}" title="{$post->link->expanded_url}" class="small">
                    {$post->link->expanded_url}
                  </a>
                {/if}
          

              </div>
              
              {*
              <div class="small gray prepend">
              
                <!-- POST NETWORK ICON -->
                <img src="{$site_root_path}assets/img/social_icons/{$post->network|get_plugin_path}.png" class="float-l">
                
                <!-- POST DATESTAMP -->
                Posted at {$post->adj_pub_date}{if $post->source} via {$post->source}{/if}<br>
                {if $post->location}From: {$post->location}{/if}
              
                <!-- POST GEO-LOCATION -->
                {if $post->is_geo_encoded eq 1}
                  <div>
                    <a href="{$site_root_path}post/map.php?t=post&pid={$post->post_id}&n={$post->network}" title="Locate on Map">
                      <img src="{$site_root_path}assets/img/map_icon.png" class="map-icon map-icon-public">
                    </a>
                  </div>
                {/if}
                
              </div>
              *}
              
            </div> <!-- end .grid_12 -->
            
            <div class="grid_9 omega">

              <!-- TOOL BUTTONS -->
              <div class="clearfix small">
              
                {if $post->is_geo_encoded eq 1}
                <a href="{$site_root_path}post/map.php?t=post&pid={$post->post_id}&n={$post->network}" class="tt-button ui-state-default tt-button-icon-left ui-corner-all">
                  <span class="ui-icon ui-icon-pin-s"></span>
                  Map
                </a>
                {/if}
                
    
              </div>
              
              <div class="clearfix small">                
                
                  <div class="bb clearfix">
                    <div class="grid_4 alpha">Network</div>
                    <div class="grid_5 omega">{$post->network}</div>
                  </div>
                
                  <div class="bb clearfix">
                    <div class="grid_4 alpha">Life span</div>
                    <div class="grid_5 omega">{$post->adj_pub_date|relative_datetime}</div>
                  </div>
                  
                  <div class="bb clearfix">
                    <div class="grid_4 alpha">Post date/time</div>
                    <div class="grid_5 omega">{$post->adj_pub_date}</div>
                  </div>
                    
                  <div class="bb clearfix">
                    <div class="grid_4 alpha">Source</div>
                    <div class="grid_5 omega">{if $post->source}{$post->source}{/if}</div>
                  </div>
                    
                    <div class="grid_4 alpha">Location</div>
                    <div class="grid_5 omega">{if $post->location}{$post->location}{/if}</div>
              </div>

            <div class="post-stats clearfix" style="padding-top:10px;">
              {if $replies}
                    <div class="grid_3 alpha center">
                      <div class="round-all border-all">
                      <h1>{$post->reply_count_cache|number_format}</h1>
                      Repl{if $post->reply_count_cache == 1}y{else}ies{/if}
                      </div>
                    </div>
              {/if}
              {if $retweets}          
                    <div class="grid_3 center">
                      <div class="round-all border-all">
                      <h1>{$retweets|@count|number_format}</h1>
                      Forwards
                      </div>
                    </div>  
                    <div class="grid_3 omega center">
                      <div class="round-all border-all">
                      <h1>{$retweet_reach|number_format}</h1>
                      Reach
                      </div>
                    </div>  
              {/if}
            </div>
            
                    {*
                    {if $logged_in_user}
                      <!-- SEARCH -->
                      <a href="#" class="grid_search" title="Search" onclick="return false;"><img src="{$site_root_path}assets/img/search-icon.gif" id="grid_search_icon"></a>
                    {/if}
                    *}
                                        
                
            </div> <!-- end grid_6 -->
            
</div> <!-- end .clearfix -->

{*
<div class="clearfix prepend">
<div class="grid_12 prefix_2 alpha">
              <div class="post-stats clearfix">
                <div class="grid_4 alpha center">
                  <div class="round-all border-all">
                  <h1>{$post->reply_count_cache|number_format}</h1>
                  Repl{if $post->reply_count_cache == 1}y{else}ies{/if}
                  </div>
                </div>
                <div class="grid_4 center">
                  <div class="round-all border-all">
                  <h1>{$retweets|@count|number_format}</h1>
                  Forwards
                  </div>
                </div>  
                <div class="grid_4 omega center">
                  <div class="round-all border-all">
                  <h1>{$retweet_reach|number_format}</h1>
                  Reach
                  </div>
                </div>  
              </div>
</div>

<div class="grid_8 omega">
</div>

</div>
*}

{literal}
<script type="text/javascript" src="/thinkup/assets/js/grid_search.js"></script>

<script type="text/javascript">

$(document).ready(function() {

	//Default Action
	$(".tab_content").hide(); //Hide all content
	$("ul.tabs li:first").addClass("active").show(); //Activate first tab
	$(".tab_content:first").show(); //Show first tab content
	
	//On Click Event
	$("ul.tabs li").click(function() {
		$("ul.tabs li").removeClass("active"); //Remove any "active" class
		$(this).addClass("active"); //Add "active" class to selected tab
		$(".tab_content").hide(); //Hide all tab content
		var activeTab = $(this).find("a").attr("href"); //Find the rel attribute value to identify the active tab + content
		$(activeTab).fadeIn(); //Fade in the active content
		return false;
	});

  $("#testme").click(function() {
    $.ajax({
      url: '/thinkup/assets/html/grid.html?v=tweets-all&u=dash30&n=twitter&cb=1286370329896',
      success: function(data) {
        $('.result').html(data);
        //alert('Load was performed.');
      }
    });
  });

});


</script>
{/literal}

  <div class="clearfix">
    <ul class="tabs">
        <li><a href="#tab1" class="tt-button ui-state-default tt-button-icon-left ui-corner-all"><span class="ui-icon ui-icon-pin-s"></span>View All</a></li>
        <li><a href="#tab2" id="grid_search_icon" class="tt-button ui-state-default tt-button-icon-left ui-corner-all"><span class="ui-icon ui-icon-pin-s"></span>Filter</a></li>
    </ul>
  </div>
  
  
  
    <div class="tab_container">
        <div id="tab1" class="tab_content">
        
          <!-- REPLIES -->

          {if $replies}
            <div class="post-stats clearfix">
                <div class="grid_4 alpha center">
                  <div class="round-all border-all">
                  <h1>{$post->reply_count_cache|number_format}</h1>
                  Repl{if $post->reply_count_cache == 1}y{else}ies{/if}
                  </div>
                </div>
            </div>
            <!-- 
            <br>
            <h2 class="subhead">Replies</h2>
            -->
            <div class="append_20 clearfix">
              {foreach from=$replies key=tid item=t name=foo}
                {include file="_post.tpl" t=$t sort='no' scrub_reply_username=true}
              {/foreach}
              {if !$logged_in_user && $private_reply_count > 0}
                <span style="font-size:12px">Not showing {$private_reply_count} private repl{if $private_reply_count == 1}y{else}ies{/if}.</span>
              {/if}
            </div>
          {/if}
          
          {*
          <!-- SHOW ALL REPLIES BUTTON -->
          <div class="append prepend clearfix">
            <a href="#" class="show_replies tt-button ui-state-default tt-button-icon-left ui-corner-all "
               style="display:none;">
              <span class="ui-icon ui-icon-circle-arrow-w"></span>
              Show All Replies
            </a>
          </div>
          
          <!-- RETWEETS/FORWARDS -->
          <div class="clearfix">
          
              <div class="grid_6 center big-number omega">
                <div class="bl">
                  <div class="key-stat">
                    <h1>
                    <a href="#fwds" name="fwds">{$retweets|@count|number_format}</a>
                    fwds to<br /> <a href="#fwds">{$retweet_reach|number_format}</a></h1>
                    <h3>total reach</h3>
                  </div>
                </div>
              </div>
          </div> <!-- end .clearfix -->
          *}
          
          {if $retweets}
            <!--
            <br>
            <h2 class="subhead">Forwards</h2>
            -->
            <div class="append_20 clearfix">
              {foreach from=$retweets key=tid item=t name=foo}
                {include file="_post.tpl" t=$t sort='no' scrub_reply_username=false}
              {/foreach}
            </div>
          {/if}
        
        </div>
        <div id="tab2" class="tab_content">
        
          <div class="clearfix">
            {if $replies && $logged_in_user}
            <a href="#" class="tt-button ui-state-default tt-button-icon-left ui-corner-all" onclick="return false;" id="grid_search_icon">
              <span class="ui-icon ui-icon-search"></span>
              Search
            </a>
            {/if}
            
            <a href="{$site_root_path}post/export.php?u={$post->author_username}&n={$post->network}&post_id={$post->post_id}&type=replies" class="tt-button ui-state-default tt-button-icon-left ui-corner-all">
              <span class="ui-icon ui-icon-disk"></span>
              Export
            </a>
          </div>        
          <iframe id="grid_iframe" src="/thinkup/assets/img/ui-bg_glass_65_ffffff_1x400.png" frameborder="0" scrolling="no"></iframe>
        </div>
    </div>





          
          <!-- SHOW ALL FORWARDS BUTTON -->
          <div class="append prepend clearfix">
            <a href="#" class="show_forwards tt-button ui-state-default tt-button-icon-left ui-corner-all "
               style="display:none;">
              <span class="ui-icon ui-icon-circle-arrow-w"></span>
              Show All Forwards
            </a>
          </div>
          
          <!-- BACK HOME BUTTON -->
          <div class="append prepend clearfix">
            <a href="{$site_root_path}index.php" class="tt-button ui-state-default tt-button-icon-left ui-corner-all">
              <span class="ui-icon ui-icon-circle-arrow-w"></span>
              Back home
            </a>
          </div>
          
        {else}
          &nbsp;
        {/if} <!-- end if post -->
        
      
    </div>
  </div> 
  
</div> <!-- end .thinkup-canvas -->

  <script type="text/javascript" src="{$site_root_path}assets/js/linkify.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
  
  {if $replies && $logged_in_user}
    {include file="_grid.search.tpl"}
    <script type="text/javascript">post_username = '{$post->author_username}';</script>
    <script type="text/javascript" src="{$site_root_path}assets/js/grid_search.js"></script>
  {/if}
  
{include file="_footer.tpl"}