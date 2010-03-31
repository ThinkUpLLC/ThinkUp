{include file="_header.tpl" load="no"}

<div class="container_24">
<div role="application" id="tabs">
    <ul>
        <li><a href="#plugins">Plug-ins</a></li>
        <li><a href="#instances">Your ThinkTank Password</a></li>
        {if $owner->is_admin}
            <li><a href="#ttusers">All ThinkTank Accounts</a></li>
        {/if} 
    </ul>
                
    <div class="section thinktank-canvas clearfix" id="plugins">
        <div class="alpha omega grid_22 prefix_1 clearfix prepend_20 append_20">

            <div class="append_20 clearfix">        
                {foreach from=$config_menu key=cmindex item=cmitem}
                    <div class="grid_7 border-all">
                        <div class="padding clearfix">
                            <img src="{$cfg->site_root_path}/cssjs/images/social_icons/{$cmitem[0]}.png" class="float-l">
                            <a href="?p={$cmitem[0]}">{$cmitem[1]}</a>
                        </div>
                	</div>
                {/foreach}
            </div>

            {if $body}
            {include file=$body}
            {/if}
        
        </div>
    </div> <!-- #plugins -->
            
    <div class="section" id="instances">
        <div class="thinktank-canvas clearfix">
        
            <div class="alpha omega grid_22 prefix_1 clearfix prepend_20 append_20">
                {if isset($errormsg)}
                	<div class="ui-state-error ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
                		<p><span class="ui-icon ui-icon-alert" style="float: left; margin:.3em 0.3em 0 0;"></span>
                		{$errormsg}</p>
                	</div>
                {/if}
                {if isset($successmsg)}
                	<div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
                		<p><span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
                		{$successmsg}</p>
                	</div>
                {/if}
                
                <form name="changepass" method="post" action="index.php" class="login prepend_20">
    
                    <div class="clearfix">
                        <div class="grid_9 prefix_1 right"><label>Current password:</label></div>
                        <div class="grid_9 left"><input name="oldpass" type="password" id="oldpass"></div>
                    </div>
        
                    <div class="clearfix">
                        <div class="grid_9 prefix_1 right"><label>New password:</label></div>
                        <div class="grid_9 left">
                            <input name="pass1" type="password" id="pass1">
                            <br />
                            	<div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: .5em 0.7em;"> 
                            		<p><span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
                            		Must be at least 5 characters.</p>
                                </div>
                        </div>
                        <div class="clearfix append_bottom">
                            <div class="grid_9 prefix_1 right">
                                <label>
                                    Re-type new password:
                                </label>
                            </div>
                            <div class="grid_9 left">
                                <input name="pass2" type="password" id="pass2">
                            </div>
                        </div>
                        <div class="prefix_10 grid_9 left">
                            <input type="submit" id="login-save" name="changepass" value="Change password" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" />
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </div> <!-- #instances -->
        
    {if $owner->is_admin}
    <div class="section" id="ttusers">
        <div class="thinktank-canvas clearfix">
            <div class="alpha omega grid_20 prefix_1 clearfix prepend_20 append_20">
                <h2 class="subhead">User accounts in this ThinkTank installation</h2>

            	<div class="ui-state-highlight ui-corner-all" style="margin: 20px 0px; padding: .5em 0.7em;"> 
            		<p><span class="ui-icon ui-icon-info" style="float: left; margin:.3em 0.3em 0 0;"></span>
            		As an administrator you can see all accounts in the system.</p>
            	</div>

                <ul class="user-accounts">
                    {foreach from=$owners key=oid item=o}
                    <li>
                        <b>{$o->full_name} ({$o->user_email})</b>{if $o->last_login neq '0000-00-00'}, last logged in {$o->last_login}{/if}
                        {if $o->instances neq null}
                        <ul>
                            {foreach from=$o->instances key=iid item=i}
                            <li>
                                {$i->network_username} ({$i->network}){if !$i->is_active} (paused){/if}
                            </li>
                            {/foreach}
                        </ul>
                        {/if}
                    </li>
                    {/foreach}
                </ul>
                
            </div>
        </div>
        {/if} <!-- is_admin-->
        
        
    </div>
</div>

{include file="_footer.tpl" stats="no"} 

<script type="text/javascript">

{literal}

$(function() {
	$(".btnPub").click(function() {  
	// validate and process form here  
		var element = $(this);
		var u = element.attr("id");
		
		var dataString = 'u='+ u+ "&p=1";  
		//alert (dataString);return false;  
		    $.ajax({  
		      type: "GET",  
		      url: "{/literal}{$cfg->site_root_path}{literal}account/toggle-public.php",  
		      data: dataString,  
		      success: function() {  
			$('#div'+u).html("<span class='success' id='message"+u+"'></span>");  
			$('#message'+u).html("Added to public timeline!") 
		       .hide()  
		       .fadeIn(1500, function() {  
			 $('#message'+u);  
		       });  
		    }  
		   });  
		   return false;  
	  });
	
	$(".btnPriv").click(function() {  
	// validate and process form here  
		var element = $(this);
		var u = element.attr("id");

		var dataString = 'u='+ u+ "&p=0";  
		//alert (dataString);return false;  
		    $.ajax({  
		      type: "GET",  
		      url: "{/literal}{$cfg->site_root_path}{literal}account/toggle-public.php",  
		      data: dataString,  
		      success: function() {  
			$('#div'+u).html("<span class='success' id='message"+u+"'></span>");  
			$('#message'+u).html("Removed from public timeline!") 
		       .hide()  
		       .fadeIn(1500, function() {  
			 $('#message'+u);  
		       });  
		    }  
		   });  
		   return false;  
	      });  

});	

$(function() {
	$(".btnPlay").click(function() {  
	// validate and process form here  
		var element = $(this);
		var u = element.attr("id");
		
		var dataString = 'u='+ u+ "&p=1";  
		//alert (dataString);return false;  
		    $.ajax({  
		      type: "GET",  
		      url: "{/literal}{$cfg->site_root_path}{literal}account/toggle-active.php",  
		      data: dataString,  
		      success: function() {  
			$('#divactivate'+u).html("<span class='success mt_10' id='message"+u+"'></span>");  
			$('#message'+u).html("Crawling has started!") 
		       .hide()  
		       .fadeIn(1500, function() {  
			 $('#message'+u);  
		       });  
		    }  
		   });  
		   return false;  
	  });
	
	$(".btnPause").click(function() {  
	// validate and process form here  
		var element = $(this);
		var u = element.attr("id");

		var dataString = 'u='+ u+ "&p=0";  
		//alert (dataString);return false;  
		    $.ajax({  
		      type: "GET",  
		      url: "{/literal}{$cfg->site_root_path}{literal}account/toggle-active.php",  
		      data: dataString,  
		      success: function() {  
			$('#divactivate'+u).html("<span class='success mt_10' id='message"+u+"'></span>");  
			$('#message'+u).html("Crawling has paused!") 
		       .hide()  
		       .fadeIn(1500, function() {  
			 $('#message'+u);  
		       });  
		    }  
		   });  
		   return false;  
	      });  

});	

{/literal}
                    	
</script>
