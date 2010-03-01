
{if $instance}
<div id="service-bar" class="clearfix"> 
    {if $smarty.session.user}Logged in as {$smarty.session.user} | {/if}
    Last update: {$instance->crawler_last_run|relative_datetime}
    <ul> 
        <li><strong>Services: </strong></li> 
        <li>Twitter </li> 
        <!--
        <li>Facebook | </li> 
        <li>Flickr | </li> 
        <li>LinkedIn</li> 
        -->
    </ul> 
</div> 
{/if}
    
<div id="content"> 

    <div class="clearfix">
    <div id="menu-bar"> 
        <ul> 
        {if $smarty.session.user}
            {if $instance}
                <li class="round-tl round-bl"><a href="{$cfg->site_root_path}?u={$instance->twitter_username}">{$instance->twitter_username}</a></li>
            {else}
                <li class="round-tl round-bl"><a href="{$cfg->site_root_path}">Home</a></li>
            {/if}
            
            {if $mode eq "public"}
                <li><a href="{$cfg->site_root_path}">Private Dashboard</a></li>
            {else}
                <li><a href="{$cfg->site_root_path}public.php">Public Timeline</a></li>
            {/if}

            <li><a href="{$cfg->site_root_path}account/">Configuration</a></li> 
            <li class="round-tr round-br"><a href="{$cfg->site_root_path}session/logout.php">Logout</a></li> 
        {else}
            <li><a href="{$cfg->site_root_path}session/login.php">Sign in</a></li>
        {/if}        
        </ul> 
    </div> 
    </div>
    
</div>