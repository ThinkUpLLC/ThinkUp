{include file="_header.tpl" load="no"}
<div id="bd" role="main">
    <div id="yui-main">
        <div class="yui-b">
            <div role="application" class="yui-g" id="tabs">
                <ul>
                    <li>
                        <a href="#instances">Account</a>
                    </li>
                    <li>
                        <a href="#templates">Templates</a>
                    </li>
                </ul>
                <div class="section" id="instances">
                    <b>Your Twitter accounts</b>
                    <br/>
                    <br/>
                    {if isset($errormsg)}
                    <div class="error">
                        {$errormsg} 
                    </div>{/if}
                    {if isset($successmsg)}
                    <div class="success">
                        {$successmsg} 
                    </div>{/if}
                    {if $owner->is_admin}
                    <p class="info">
                        You are an administrator so you can see all accounts in the system.
                    </p>
                    <br/>
                    <br/>
                    {/if}
                    {if count($owner_instances) > 0 }
                    <ul>
                        {foreach from=$owner_instances key=iid item=i}
                        <li>
                            <a href="{$cfg->site_root_path}?u={$i->twitter_username}">{$i->twitter_username}</a>
                            <span id="div{$i->twitter_username}"><input type="submit" name="submit" class="{if $i->is_public}btnPriv{else}btnPub{/if}" id="{$i->twitter_username}" value="{if $i->is_public}remove from public timeline{else}include on public timeline{/if}" /></span>
                        </li>{/foreach}
                    </ul>{else}
                    You have no Twitter accounts configured.
                    {/if}
                    <br/>
                    <br/>
                    <b>Add a Twitter account</b>: <a href="{$oauthorize_link}">Authorize ThinkTank on Twitter&rarr;</a>
                    <br/>
                    <br/>
                    <br/>
                    <br/>
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
                <div class="section" id="templates">
                    Template list goes here
                </div>
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
        
        		{/literal}
        	
</script>
{include file="_footer.tpl" stats="no"} 