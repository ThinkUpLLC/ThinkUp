{include file="_header.tpl" load="no" title=$profile->username}

{literal}
<script>
$(document).ready(function() {

	$(".toggle_container").hide(); 

    $("h4.trigger").toggle(function(){
		$(this).addClass("active");
		}, function () {
		$(this).removeClass("active");
	});

    //Slide up and down on click
	$("h4.trigger").click(function(){
		$(this).next(".toggle_container").slideToggle("slow");
	});
	
});
</script>
{/literal}

<div id="bd" role="main">
	<div id="yui-main">
	<div class="yui-b">

        <div id="thinktank-tabs">
            <div role="application" id="tabs">
        
            	<ul>
            		<li><a href="#updates"><div class="key-stat">
                        <h1>{$profile->post_count|number_format}</h1> 
                        <h3>Posts</h3></div></a></li>
            		<li><a href="#conversations"><div class="key-stat">        
                        <h1>{$total_exchanges}</h1> 
                        <h3>Conversations</h3></div></a></li>
            		<li><a href="#followers"><div class="key-stat">        
                        <h1>{$profile->follower_count|number_format}</h1> 
                        <h3>Followers</h3></div></a></li>
            		<li><a href="#friends"><div class="key-stat">
                        <h1>{$profile->friend_count|number_format}</h1> 
                        <h3>Friends</h3></div></a></li>
            		<li class="no-border"><a href="#mutual"><div class="key-stat">
                        <h1>{$total_mutual_friends}</h1> 
                        <h3>Mutual</h3></div></a></li>
            	</ul>		


                <div class="section" id="updates">
                
                    <img src="{$cfg->site_root_path}cssjs/images/dart_wht.png" alt="" class="dart" id="dart1"> 
                    <div id="top" class="clearfix">
                    
                        <div class="thinktank-canvas container_24">

                            <h4 class="trigger clearfix"><a href="#">Statistics</a></h4>                        

                            <div class="footnote toggle_container clearfix">
                            
                            <div class="grid_11 push_1 alpha">
                            
                                <div class="clearfix">
                                    <div class="grid_11 alpha omega">Clients:</div>
                                </div> 
                               	{if count($sources > 0)}
                                	{foreach from=$sources key=tid item=s name=foo}
                                        <div class="clearfix bt">
                                            <div class="grid_7 bold alpha">{$s.total} statuses posted via</div>
                                            <div class="grid_4 right omega">{if $s.source eq 'web'} the {$s.source}{else}{$s.source}{/if}</div>
                                        </div>
                                	{/foreach}
                            	{/if}
                                <div class="clearfix bt">
                                    <div class="grid_11 alpha omega">&nbsp;</div>
                                </div> 
                             
                            </div>
                            
                            <div class="grid_11 push_1 omega">
                                <div class="clearfix">
                                    <div class="grid_11 alpha omega">&nbsp;</div>
                                </div> 
                                <div class="clearfix bt">
                                    <div class="grid_5 bold alpha">Joined Twitter</div>
                                    <div class="grid_6 right omega">{$profile->joined|relative_datetime} on {$profile->joined|date_format:"%D"}</div>
                                </div>
                                {if $profile->avg_tweets_per_day}
                                <div class="clearfix bt">
                                    <div class="grid_5 bold alpha">Averages</div>
                                    <div class="grid_6 right omega">{$profile->avg_tweets_per_day} updates per day</div>
                                </div>
                                {/if}
                                <div class="clearfix bt">
                                    <div class="grid_9 bold alpha">ThankTank last updated {$profile->user_name}</div>
                                    <div class="grid_2 right omega">{$profile->last_updated|relative_datetime}</div>
                                </div>
                                <!--
                                {if $sources}
                                <div class="clearfix bt">
                                    <div class="grid_8 bold alpha">Most-used Twitter client</div>
                                    <div class="grid_3 right omega">{$sources[0].source}</div>
                                </div>
                                {/if}
                                -->
                                <div class="clearfix bt">
                                    <div class="grid_11 alpha omega">&nbsp;</div>
                                </div> 
                            </div>
                                 
                            </div>

                            <div class="clearfix append_20">
                                <div class="grid_2 prefix_1 alpha">
                                    <img src="{$profile->avatar}" class="avatar2"> 
                                </div>
                                <div class="grid_19 omega">
                                    <h1 class="user">{$profile->user_name}</h1> 
                                    <!--
                                    [{$profile->follower_count|number_format} followers, following {$profile->friend_count|number_format}]
                                    -->
                                    {if $profile->description}{$profile->description}{/if}
                                    {if $profile->tweet_count > 0}
                                        <br />
                                        Last post {$profile->last_post|relative_datetime}
                                        {if $profile->location} from {$profile->location}{/if}
                                    {/if}
                                </div>
                            </div>
                            
                            <div class="grid_22 push_1">
                            {foreach from=$user_statuses key=tid item=t name=foo}
                                <div>
                                    {include file="_post.mine.tpl" t=$t}
                                </div>
                            {/foreach}
                            </div>
                            
                
                        </div>
                    </div>                
                
                
                </div>
                <div class="section" id="conversations">
                    <img src="{$cfg->site_root_path}cssjs/images/dart_wht.png" alt="" class="dart" id="dart2"> 
                    <div id="top" class="clearfix append">
                    
                        <div class="thinktank-canvas container_24">
                            <div class="grid_22 push_1 append_20">
                               	{if $exchanges}
                            		{foreach from=$exchanges key=tahrt item=r name=foo}
                                        {include file="_post.qa.tpl" t=$t}
                            		{/foreach}
                                {else}
                                    ThinkTank has not captured any conversations between {$instance->network_username} and {$profile->user_name}.
                                {/if}
                        	</div>
                        </div>
                    </div>                
                </div>
                
                <div class="section" id="followers">
                    <img src="{$cfg->site_root_path}cssjs/images/dart_wht.png" alt="" class="dart" id="dart3"> 
                    <div id="top" class="clearfix">
                    
                        <div class="thinktank-canvas container_24">
                            <div class="grid_22 push_1 append_20">
                                Detailed data about this user's {$profile->follower_count|number_format} followers not yet available in ThinkTank.
                            </div>
                        </div>
                    </div>                
                </div>
                
                <div class="section" id="friends">
                    <img src="{$cfg->site_root_path}cssjs/images/dart_wht.png" alt="" class="dart" id="dart4"> 
                    <div id="top" class="clearfix">
                    
                        <div class="thinktank-canvas container_24">
                            <div class="grid_22 push_1 append_20">
                                Detailed data about this user's {$profile->friend_count|number_format} friends not yet available in ThinkTank.
                            </div>
                        </div>
                    </div>                
                </div>
                
                <div class="section" id="mutual">
                    <img src="{$cfg->site_root_path}cssjs/images/dart_wht.png" alt="" class="dart" id="dart5"> 
                    <div id="top" class="clearfix">
                    
                        <div class="thinktank-canvas container_24">
                            <div class="grid_22 push_1 append_20">
                            	{if count($mutual_friends > 0)}
                            	{foreach from=$mutual_friends key=tid item=f name=foo}
                                    {include file="_user.tpl" t=$f}
                            	{/foreach}
                            	{else}
                            	   No mutual friends identified.
                            	{/if}
                            </div>
                        </div>
                    </div>
                </div>



        
        	</div> <!-- #tabs -->
        </div> <!-- #thinktank-tabs -->	

        <!--	
    	<div role="application" class="yui-g" id="ttabs">
    
    				<ul>
    					<li><a href="#tweets">User</a></li>
    					{if $exchanges}<li><a href="#replies">Conversations ({$total_exchanges})</a></li>{/if}
    					{if count($mutual_friends) > 0}<li><a href="#mutualfriends">Mutual Friends ({$total_mutual_friends})</a></li>{/if}
    					{if count($sources) > 0 }<li><a href="#sources">Clients</a></li>{/if}
    				</ul>		
    
    
    		<div class="section" id="tweets"></div>
    	
        </div>
        -->
    
    </div>
    </div>

        <!--
        <h2>User Stats</h2>
        <ul>
        	<li>Last updated {$profile->last_updated|relative_datetime}</li>
        	<li><a {if $instance}href="{$cfg->site_root_path}?u={$instance->twitter_username}">{else}href="#" onClick="history.go(-1)">{/if}&larr; back</a></li>
        </ul>
        -->

</div>


{include file="_footer.tpl" stats="no"}