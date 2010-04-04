{include file="_header.tpl"}

{literal}
<script type="text/javascript">
$(document).ready(function() {
  $(".toggle_container").hide();
  $("h4.trigger").toggle(
    function () {
      $(this).addClass("active");
    },
    function () {
      $(this).removeClass("active");
    }
  );

  // Slide up and down on click
  $("h4.trigger").click(
    function () {
      $(this).next(".toggle_container").slideToggle("slow");
    }
  );
});
</script>
{/literal}

<div class="container">

    <!-- session instance info: {$smarty.session.instance->network_username} -->


    <div id="thinktank-tabs">
        <div role="application" id="tabs">
    
          <ul>
            <li><a href="#updates"><div class="key-stat">
                    <h1>{$owner_stats->post_count|number_format}</h1> 
                    <h3>Posts</h3></div></a></li>
            <li><a href="#mentions"><div class="key-stat">        
                    <h1>{$instance->total_replies_in_system|number_format}</h1> 
                    <h3>Replies</h3></div></a></li>
            <li><a href="#followers"><div class="key-stat">        
                    <h1>{$owner_stats->follower_count|number_format}</h1> 
                    <h3>Followers</h3></div></a></li>
            <li><a href="#friends"><div class="key-stat">
                    <h1>{$owner_stats->friend_count|number_format}</h1> 
                    <h3>Friends</h3></div></a></li>
            <li class="no-border"><a href="#links"><div class="key-stat">
                    <h1>&nbsp;</h1> 
                    <h3>Links</h3></div></a></li>
          </ul>
              
            <div class="section" id="updates">
                <img src="{$cfg->site_root_path}cssjs/images/dart_wht.png" alt="" class="dart" id="dart1"> 
                <div class="thinktank-canvas clearfix">
                
                    <div class="container_24">
                    
                        <h4 class="trigger clearfix"><a href="#">Statistics</a></h4>                        
                        
                        <div class="footnote toggle_container clearfix">
                        
                            <div class="grid_13 push_10 append_20">
                                <div class="clearfix bt">
                                    <div class="grid_9 bold alpha">Average updates per day</div>
                                    <div class="grid_4 right omega">{$owner_stats->avg_tweets_per_day}</div>
                                </div>
                                <div class="clearfix bt">
                                    <div class="grid_9 bold alpha">Joined</div>
                                    <div class="grid_4 right omega">{$owner_stats->joined|date_format:"%D"}</div>
                                </div>
                                <div class="clearfix bt">
                                    <div class="grid_13 bold alpha omega">&nbsp;</div>
                                </div> 
                            </div>
 
                        </div>
                       
                        <div class="clearfix append_20">
                            <div class="grid_1 prefix_1">
                                <div id="loading_updates"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>&nbsp;
                            </div>
                            <div class="grid_22 alpha append_20 clearfix">
                                <ul id="updates-menu" class="menu">
                                    <li class="menu-item selected" id="tweets-all">All</li>
                                    <li class="menu-item" id="tweets-mostreplies">Most&nbsp;replied-to</li>
                                    <li class="menu-item" id="tweets-mostretweeted">Most&nbsp;shared</li>
                                    <li class="menu-item" id="tweets-convo">Conversations</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="grid_22 prefix_1">
                            <div id="tweets_content"></div>
                        </div>
                        
                    </div>

                </div> <!-- #top -->
            </div> <!-- #updates -->
    
            <div class="section" id="mentions">
                <img src="{$cfg->site_root_path}cssjs/images/dart_wht.png" alt="" class="dart" id="dart2"> 
                <div class="thinktank-canvas clearfix">
                
                    <div class="container_24">

                        <h4 class="trigger clearfix"><a href="#">Statistics</a></h4>                        
                        
                        <div class="grid_24 footnote toggle_container clearfix">
                        
                            <div class="grid_13 push_10 append_20">
                            
                                <div class="clearfix bt">
                                    <div class="grid_9 bold alpha">Mentions loaded into ThinkTank</div>
                                    <div class="grid_4 right omega">{$instance->total_replies_in_system|number_format}</div>
                                </div>
                                
                                {if $instance->total_replies_in_system > 0}
                                    <div class="clearfix bt">
                                        <div class="grid_9 bold alpha">Average replies per day</div>
                                        <div class="grid_4 right omega">{$instance->avg_replies_per_day}</div>
                                    </div> 
                                    
                                    <div class="clearfix bt">
                                        <div class="grid_9 bold alpha">Earliest reply loaded into ThinkTank</div>
                                        <div class="grid_4 right omega">{$instance->earliest_reply_in_system|date_format:"%D"}</div>
                                    </div> 
                                {/if}                           

                                <div class="clearfix bt">
                                    <div class="grid_13 bold alpha omega">&nbsp;</div>
                                </div> 

                            </div>
                        
                        </div>
                        
                        <div class="grid_1 prefix_1">
                            <div id="loading_mentions"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>&nbsp;
                        </div>
                        <div class="grid_22 alpha append_20 clearfix">
                             <ul id="mentions-menu" class="menu">
                                <li class="menu-item selected" id="mentions-all">All&nbsp;mentions</li>
                                <li class="menu-item" id="mentions-allreplies">Replies</li>
                                <li class="menu-item" id="mentions-orphan">Not&nbsp;replies&nbsp;or&nbsp;shared</li>
                                <li class="menu-item" id="mentions-standalone">Standalone</li>
                            </ul>
                        </div>
                        <div class="grid_22 prefix_1">
                            <div id="mentions_content"></div>
                        </div>
                    </div>

                </div> <!-- #top -->
            </div> <!-- #mentions -->
    
            <div class="section" id="followers">
                <img src="{$cfg->site_root_path}cssjs/images/dart_wht.png" alt="" class="dart" id="dart3"> 
                <div class="thinktank-canvas clearfix">
                
                    <div class="container_24">

                        <h4 class="trigger clearfix"><a href="#">Statistics</a></h4>                        
                        
                        <div class="grid_24 footnote toggle_container">
                        
                            <div class="grid_13 push_10 append_20">
                            
                                <div class="clearfix bt">
                                    <div class="grid_9 bold alpha">Followers loaded into ThinkTank</div>
                                    <div class="grid_4 right omega">{$total_follows_with_full_details}</div>
                                </div>
                                {if $total_follows_protected>0}
                                <div class="clearfix bt">
                                    <div class="grid_9 bold alpha">Followers who are private</div>
                                    <div class="grid_4 right omega">{$total_follows_protected|number_format} ({$percent_followers_protected}%)</div>
                                </div>
                                {/if}
                                {if $total_follows_with_errors>0}
                                <div class="clearfix bt">
                                    <div class="grid_9 bold alpha">Followers who are suspended</div>
                                    <div class="grid_4 right omega">{$total_follows_with_errors|number_format} ({$percent_followers_suspended}%)</div>
                                </div>
                                {/if}                           
                                <div class="clearfix bt">
                                    <div class="grid_13 bold alpha omega">&nbsp;</div>
                                </div> 

                            </div>

                        </div>
                        
                        <div class="grid_1 prefix_1">
                            <div id="loading_followers"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>&nbsp;
                        </div>
                        <div class="grid_22 alpha append_20 clearfix">
                            <ul id="followers-menu" class="menu">
                                <li class="menu-item selected" id="followers-mostfollowed">Most-followed</li>
                                <li class="menu-item" id="followers-leastlikely">Least&nbsp;likely</li>
                                <li class="menu-item" id="followers-earliest">Earliest</li>
                                <li class="menu-item" id="followers-former">Former</li>
                            </ul>
                        </div>
                        <div class="grid_22 prefix_1">
                            <div id="followers_content"></div>
                        </div>
                    </div>
                    
                </div> <!-- #top -->
            </div> <!-- #followers -->
    
            <div class="section" id="friends">
                <img src="{$cfg->site_root_path}cssjs/images/dart_wht.png" alt="" class="dart" id="dart4"> 
                <div class="thinktank-canvas clearfix">
                
                    <div class="container_24">
                    
                        <h4 class="trigger clearfix"><a href="#">Statistics</a></h4>                        
                        
                        <div class="grid_24 footnote toggle_container">
                        
                            <div class="grid_13 push_10 append_20">
                                <div class="clearfix bt">
                                    <div class="grid_9 bold alpha">Friends</div>
                                    <div class="grid_4 right omega">{$owner_stats->friend_count|number_format}</div>
                                </div>
                                <div class="clearfix bt">
                                    <div class="grid_9 bold alpha">Profiles loaded</div>
                                    <div class="grid_4 right omega">{$total_friends|number_format}</div>
                                </div>
                                {if $total_friends_protected>0}
                                <div class="clearfix bt">
                                    <div class="grid_9 bold alpha">Friends who are private</div>
                                    <div class="grid_4 right omega">{$total_friends_protected|number_format}</div>
                                </div>
                                {/if}
                                {if $total_friends_with_errors>0}
                                <div class="clearfix bt">
                                    <div class="grid_9 bold alpha">Friends suspended</div>
                                    <div class="grid_4 right omega">{$total_friends_with_errors|number_format}</div>
                                </div>
                                {/if}
                                <div class="clearfix bt">
                                    <div class="grid_13 bold alpha omega">&nbsp;</div>
                                </div> 
                            </div>

                        </div>
                        
                        <div class="grid_1 prefix_1">
                            <div id="loading_friends"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>&nbsp;
                        </div>
                        <div class="grid_22 alpha append_20 clearfix">
                            <ul id="friends-menu" class="menu">
                                <li class="menu-item selected" id="friends-mostactive">Chatterboxes</li>
                                <li class="menu-item" id="friends-leastactive">Deadbeats</li>
                                <li class="menu-item" id="friends-mostfollowed">Popular</li>
                                <li class="menu-item" id="friends-former">Former</li>
                                <li class="menu-item" id="friends-notmutual">Not&nbsp;mutual</li>      
                            </ul>
                        </div>
                        <div class="grid_22 prefix_1">
                            <div id="friends_content"></div>
                        </div>
                    </div>
                    
                </div> <!-- #top -->
            </div> <!-- #friends -->
    
            <div class="section" id="links">
                <img src="{$cfg->site_root_path}cssjs/images/dart_wht.png" alt="" class="dart" id="dart5"> 
                <div class="thinktank-canvas clearfix">
                
                    <div class="container_24">

                        <!--<h4 class="trigger clearfix"><a href="#">Statistics</a></h4>-->                      
                        
                        <div class="grid_24 footnote toggle_container">
                            &nbsp;
                        </div>
                        
                        <div class="grid_1 prefix_1">
                            <div id="loading_links"><img src="{$cfg->site_root_path}cssjs/images/ui_throbber.gif" alt="Loading..." /></div>&nbsp;
                         </div>
                        <div class="grid_22 alpha append_20 clearfix">
                            <ul id="links-menu" class="menu">
                                <li class="menu-item selected" id="links-friends">From friends</li>
                                <li class="menu-item" id="links-favorites">From your favorites</li>
                                <li class="menu-item" id="links-photos">Photos</li>
                            </ul>
                        </div>
                        <div class="grid_22 prefix_1">
                           <div id="links_content"></div>
                        </div>
                    </div>
                    
                </div> <!-- #top -->
            </div> <!-- #links -->
    
      </div> <!-- #tabs -->
    </div> <!-- #thinktank-tabs -->
    
      
  <div role="contentinfo" id="keystats">

        <h2>Key Stats</h2>

        <ul>
            <li>Followers: <cite title="Total followers according to Twitter.com (not necessarily loaded into ThinkTank)">{$owner_stats->follower_count|number_format}</cite><br /> <small>{if $total_follows_protected>0} (<cite title="{$total_follows_protected|number_format} of {$total_follows_with_full_details|number_format} total follower profiles loaded into ThinkTank">{$percent_followers_protected}% protected</cite>)<br />{/if}{if $total_follows_with_errors>0} (<cite title="{$total_follows_with_errors|number_format} of {$total_follows_with_full_details|number_format} follower profiles loaded into ThinkTank">{$percent_followers_suspended}% suspended</cite>){/if}</small></li>
            <li>Friends: {$owner_stats->friend_count|number_format} <br /> <small>{if $total_friends_protected}({$total_friends_protected|number_format} protected)<br />{/if}{if $total_friends_with_errors>0} ({$total_friends_with_errors|number_format} suspended){/if}</small></li>
            <li>{$owner_stats->tweet_count|number_format} Tweets <small></small><br /><small>{$owner_stats->avg_tweets_per_day} per day since {$owner_stats->joined|date_format:"%D"}</small></li>
            <li>{$instance->total_replies_in_system|number_format} Mentions in System<br />{if $instance->total_replies_in_system > 0}<small>{$instance->avg_replies_per_day} per day since {$instance->earliest_reply_in_system|date_format:"%D"}</small>{/if}</li>
            <li>
        </ul>

        <ul id="sidemenu">
          <li>Conversations
            <ul class="submenu">
              <li>Your Tweets</li>
              <li>Mentions</li>
              <li>Messages</li>
              <li>Recent Links</li>
              <li>Favorited</li>
              <li>Retweets</li>
            </ul>
          </li>
          <li>Stats
            <ul class="submenu">
              <li>Followers Over Time</li>
              <li>Tweets per Day</li>
              <li>Replies per Day</li>
              <li>Retweets per Day</li>
              <li>Mentions per Day</li>
              <li>Noise Level by Day</li>
            </ul>    
          </li>
          <li>People
            <ul class="submenu">
              <li>Most Popular Followers</li>
              <li>Least Likely</li>
              <li>Chatterboxes</li>
              <li id="friends-leastactive">Deadbeats</li>
              <li>Repliers</li>
              <li>Messagers</li>
              <li>Messagees</li>
              <li>Favoritees</li>
            </ul>    
          </li>
          <li>Relationships
            <ul class="submenu">
              <li>Former Followers</li>
              <li>Not-Mutual</li>
            </ul>    
          </li>
        </ul>
        
        <br /><br />

        {if sizeof($instances) > 1 }
        <br /><br />
        <h2>Twitter Accounts</h2>
        <ul>
          {foreach from=$instances key=tid item=i}
          {if $i->network_user_id != $instance->network_user_id}
          <li><a href="?u={$i->network_username|urlencode}">{$i->network_username}</a><br /><small>updated {$i->crawler_last_run|relative_datetime}{if !$i->is_active} (paused){/if}</small></li>
          {/if}
          {/foreach}  
          <li><a href="{$cfg->site_root_path}account/">Add an account&rarr;</a></li>
        </ul>
        {/if}

    </div> <!-- #keystats -->

</div> <!-- #content -->

{include file="_footer.tpl"}
