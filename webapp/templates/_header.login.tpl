{if $statusbar neq 'no'}	
<div id="status-bar" class="clearfix"> 

    <div class="status-bar-left">

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
        	<span id="choose-instance">{$instance->network_username}</span>
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

    </div>
    
    <div class="status-bar-right">
    
        <ul> 
            {if $smarty.session.user}
                <li>Logged in as: {$smarty.session.full_name} | 
                <a href="{$cfg->site_root_path}session/logout.php">Log Out</a> | </li>
                <li>Plug-ins:</li> 
                <li>Twitter</li> 
            {else}
                <li><a href="{$cfg->site_root_path}session/login.php">Log In</a></li>
            {/if}
        </ul> 
        
    </div>

</div> <!-- #status-bar -->
{/if}
    
<div class="container clearfix"> <!-- menu-bar -->

    <div id="app-title"><a href="{$cfg->site_root_path}?u={$instance->network_username}">
        <h1><span class="bold">Think</span><span class="gray">Tank</span></h1>
        <h2>Ask your friends</h2></a>
        

    </div>
        
    <div id="menu-bar"> 
        <ul> 
            {if $smarty.session.user}
                {if $mode eq "public"} <!-- this is the public timeline -->
                    <li class="round-tl round-bl"><a href="{$cfg->site_root_path}">Private Dashboard</a></li>
                {else}
                    <li class="round-tl round-bl"><a href="{$cfg->site_root_path}?u={$instance->network_username}">{if $instance}{$instance->network_username}{else}Home{/if}</a></li>
                    <li><a href="{$cfg->site_root_path}public.php">Public Timeline</a></li>
                {/if}
                <li class="round-tr round-br"><a href="{$cfg->site_root_path}account/">Configuration</a></li>
            {else}
                <li class="round-tr round-br round-tl round-bl"><a href="http://thinktankapp.com/">Get ThinkTank</a></li>
            {/if}
            <!--<li class="round-tr round-br"><a href="{$cfg->site_root_path}session/logout.php">Logout</a></li>--> 


        </ul> 
    </div> <!-- #menu-bar -->
        
</div> <!-- .container -->    
