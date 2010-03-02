{if $instance}

    {literal}
    <script type="text/javascript">
    $(document).ready(function(){
        $('#choose-instance').click(function() {
            $('#instance-selector').show();
            $('#choose-instance').hide();
        });
        $('#cancel-instance').click(function() {
            $('#instance-selector').hide();
            $('#choose-instance').show();
        });
    });
    function changeMe() {
        var _mu = $("select#instance-select").val();
        if (_mu != "null") { document.location.href = _mu; }
    }    
    </script>
    {/literal}

    <div id="service-bar" class="clearfix"> 
        Last update: {$instance->crawler_last_run|relative_datetime} | 
        
    	<span id="choose-instance">Switch account</span>
    	<span id="instance-selector" style="display: none;">
    		<select id="instance-select" onchange="changeMe();">
    				<option value="">-- Select an account --</option>
                    {foreach from=$instances key=tid item=i}
                        {if $i->twitter_user_id != $instance->twitter_user_id}
                        <option value="{$cfg->site_root_path}?u={$i->twitter_username}">{$i->twitter_username} (updated {$i->crawler_last_run|relative_datetime}{if !$i->is_active} (paused){/if})</option>
                        {/if}
                    {/foreach}  				
    		</select>
    		<span id="cancel-instance">Cancel</span>
    	</span>
        
        
        <ul> 
            {if $smarty.session.user}<li>Logged in as: {$smarty.session.user} | </li>{/if}
            <li>Services:</li> 
            <li>Twitter</li> 
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