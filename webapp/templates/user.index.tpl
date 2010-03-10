{include file="_header.tpl" load="no"}

<div id="bd" role="main">
	<div id="yui-main">
	<div class="yui-b">
        <div id="thinktank-tabs">
            <div role="application" class="yui-g" id="tabs">
        
            	<ul>
            		<li><a href="#updates"><div class="key-stat">
                        <h1>{$profile->tweet_count|number_format}</h1> 
                        <h3>Updates</h3></div></a></li>
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
                            <div class="grid_24 right small gray footnote">
                                {$profile->user_name} averages {$profile->avg_tweets_per_day} updates per day since joining {$profile->joined|relative_datetime} on {$profile->joined|date_format:"%D"} 
                                    <!--{if $sources} | Most-used Twitter client: {$sources[0].source}{/if}-->
                                    <br />
                                   	{if count($sources > 0)}
                                    	{foreach from=$sources key=tid item=s name=foo}
                                            {$s.total} statuses posted {if $s.source eq 'web'}on the {$s.source}{else}with {$s.source}{/if}
                                            {if $smarty.foreach.foo.last}<br />{else} | {/if} 
                                    	{/foreach}
                                	{/if}
                                	ThankTank last updated {$profile->user_name} {$profile->last_updated|relative_datetime}<br />
                            </div>

                            <div class="grid_22 push_1 clearfix">
                                <img src="{$profile->avatar}" class="avatar"> 
                                <h1><a href="http://twitter.com/{$profile->user_name}">@{$profile->user_name}</a></h1> 
                                <!--
                                [{$profile->follower_count|number_format} followers, following {$profile->friend_count|number_format}]
                                -->
                                {if $profile->description}<br />{$profile->description}{/if}
                                {if $profile->tweet_count > 0}
                                    <br />
                                    Last post {$profile->last_post|relative_datetime}
                                    {if $profile->location} from {$profile->location}{/if}
                                {/if}
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
                                    ThinkTank has not captured any conversations between {$instance->twitter_username} and {$profile->user_name}.
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
                                Follower data not yet available in ThinkTank.
                            </div>
                        </div>
                    </div>                
                </div>
                
                <div class="section" id="friends">
                    <img src="{$cfg->site_root_path}cssjs/images/dart_wht.png" alt="" class="dart" id="dart4"> 
                    <div id="top" class="clearfix">
                    
                        <div class="thinktank-canvas container_24">
                            <div class="grid_22 push_1 append_20">
                                Friend data not yet available in ThinkTank.
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