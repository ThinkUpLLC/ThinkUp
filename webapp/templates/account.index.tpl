{include file="_header.tpl" load="no"}
<div id="bd" role="main">
    <div id="yui-main">
        <div class="yui-b">
            <div role="application" class="yui-g" id="tabs">
                <ul>
                    <li>
                        <a href="#plugins">Plug-ins</a>
                    </li>
                    <li>
                        <a href="#instances">Your ThinkTank Account</a>
                    </li>
                    {if $owner->is_admin}
                    <li>
                        <a href="#ttusers">All ThinkTank Accounts</a>
                    </li>
                    {/if} 
                </ul>
                
                <div class="section" id="plugins">
                    <b>Configure ThinkTank Plug-ins</b>
                    <ul>
                    {foreach from=$config_menu key=cmindex item=cmitem}
                    	<li><a href="?p={$cmitem[0]}">{$cmitem[1]}</a></li>
                    {/foreach}
                    </ul>
                    <br/>
                    <br/>
                    {if $body}
                    	{include file=$body}
                    {/if}
                </div>
                
                <div class="section" id="instances">
                    {if isset($errormsg)}
                    <div class="error">
                        {$errormsg} 
                    </div>{/if}
                    {if isset($successmsg)}
                    <div class="success">
                        {$successmsg} 
                    </div>{/if}
                    <b>Your ThinkTank Password</b>
                    <form name="changepass" method="post" action="index.php" style="padding:5px;">
                        <table cellpadding="5" cellspacing="5" width="100%">
                            <tr>
                                <td align="right">
                                    Current Password:
                                </td>
                                <td>
                                    <input name="oldpass" type="password" id="oldpass">
                                </td>
                            </tr>
                            <tr>
                                <td align="right" valign="top">
                                    New Password:
                                </td>
                                <td>
                                    <input name="pass1" type="password" id="pass1">
                                    <br/>
                                    <small>
                                        At least 5 chars
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <td align="right">
                                    Retype New Password:
                                </td>
                                <td>
                                    <input name="pass2" type="password" id="pass2">
                                </td>
                            </tr>
                            <tr>
                                <td>
                                </td>
                                <td>
                                    <input type="submit" name="changepass" value="Change Password" />
                                </td>
                            </tr>
                        </table>
                    </form>
                    
                </div>
                {if $owner->is_admin}
                <div class="section" id="ttusers">
                <b>User accounts in this ThinkTank instance</b>
                <br /><br />
                    <p class="info">
                        You are an administrator so you can see all accounts in the system.
                    </p>
                <br /><br />
                    <ul>
                        {foreach from=$owners key=oid item=o}
                        <li>
                        	<b>{$o->full_name} ({$o->user_email})</b>{if $o->last_login neq '0000-00-00'}, last logged in {$o->last_login}{/if}
                        	{if $o->instances neq null}
                        	<ul>
                        		{foreach from=$o->instances key=iid item=i}
                        		<li>&rarr; {$i->twitter_username}{if !$i->is_active} (paused){/if}</li>
                        		{/foreach}
                        	</ul>
                        	{/if}
                        </li>
                        {/foreach}
                     </ul>
                </div>
                {/if}
            </div>
        </div>
    </div>
    <div role="contentinfo" id="keystats" class="yui-b">
        <h2></h2>
        <ul>
        </ul>
    </div>
</div>
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
        					$('#divactivate'+u).html("<span class='success' id='message"+u+"'></span>");  
        					$('#message'+u).html("Crawling has been started!") 
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
        					$('#divactivate'+u).html("<span class='success' id='message"+u+"'></span>");  
        					$('#message'+u).html("Crawling has been paused!") 
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
{include file="_footer.tpl" stats="no"} 