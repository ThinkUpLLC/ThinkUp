<div id="service-bar" class="clearfix"> 

    {if $instance} <!-- the user has selected a particular one of their instances -->

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

        Last update: {$instance->crawler_last_run|relative_datetime} | 
        
    	<span id="choose-instance">Switch instance</span>
    	<span id="instance-selector" style="display: none;">
    		<select id="instance-select" onchange="changeMe();">
    				<option value="">-- Select an instance --</option>
                    {foreach from=$instances key=tid item=i}
                        {if $i->network_user_id != $instance->network_user_id}
                        <option value="{$cfg->site_root_path}?u={$i->network_username}">{$i->network_username} (updated {$i->crawler_last_run|relative_datetime}{if !$i->is_active} (paused){/if})</option>
                        {/if}
                    {/foreach}  				
    		</select>
    		<span id="cancel-instance">Cancel</span>
    	</span>
                
    {else} <!-- the user has not selected an instance -->
    
        Last update: {$crawler_last_run|relative_datetime}

        
    {/if} <!-- end if instance -->

    <ul> 
        {if $smarty.session.user}<li>Logged in as: {$smarty.session.user} | </li>{/if}
        <li>Services:</li> 
        <li>Twitter</li> 
    </ul> 

</div> <!-- #service-bar -->
    
<div class="content"> <!-- menu-bar -->

    <div class="clearfix">
    
        <div id="app-title"><a href="{$cfg->site_root_path}?u={$instance->network_username}">
            <h1><span class="bold">Think</span><span class="gray">Tank</span></h1>
            <h2>Ask your friends</h2></a>
        </div>
            
        <div id="menu-bar"> 
            <ul> 
            {if $smarty.session.user}
                {if $instance}
                    <li class="round-tl round-bl"><a href="{$cfg->site_root_path}?u={$instance->network_username}">{$instance->network_username}</a></li>
                {else}
                    <li class="round-tl round-bl"><a href="{$cfg->site_root_path}">Home</a></li>
                {/if}
                
                <li><a href="{$cfg->site_root_path}public.php">Public Timeline</a></li>
    
                <li><a href="{$cfg->site_root_path}account/">Configuration</a></li> 
                <li class="round-tr round-br"><a href="{$cfg->site_root_path}session/logout.php">Logout</a></li> 
            {else}
                {if $mode eq "public"}
                    <li class="round-tl round-bl"><a href="{$cfg->site_root_path}">Private Dashboard</a></li>
                {else}
                    <li class="round-tl round-bl"><a href="{$cfg->site_root_path}public.php">Public Timeline</a></li>
                {/if}
                <li class="round-tr round-br"><a href="{$cfg->site_root_path}session/login.php">Sign in</a></li>
            {/if}        
            </ul> 
        </div> <!-- #menu-bar -->
        
    </div> <!-- .clearfix -->
    
