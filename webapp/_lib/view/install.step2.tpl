{include file="_header.tpl" enable_bootstrap=1}
{include file="_statusbar.tpl" enable_bootstrap=1}

<div id="main" class="container">

    <div class="navbar">
        <div class="navbar-inner">
        <span class="brand" style="margin-top: 12px;">Install ThinkUp:</span>
        <ul class="nav pull-left">
            <li><a> <h4><i class="icon-ok-circle "></i> Check System Requirements</h4></a></li>
            <li class="active"><a class="disabled"> <h4><i class="icon-cogs"></i> Configure ThinkUp</h4></a></li>
            <li><a class="disabled"> <h4><i class="icon-lightbulb"></i> Finish</h4></a></li>
        </ul>
        </div>
    </div>
    
    <div class="row">
        <div class="span3">
            
        </div>
        <div class="span9">

            <form class="input form-horizontal" name="install_form" method="post" action="index.php?step=3">
            
            {include file="_usermessage.tpl" enable_bootstrap=1}

            <fieldset>
                <legend>Create your ThinkUp account</legend>
                <div class="control-group">
                    <label class="control-label" for="full_name">Name</label>
                    <div class="controls">
                        <input type="text" name="full_name" id="full_name" required {if isset($full_name)} value="{$full_name}"{/if}>
                    </div>
                </div>
                <div class="control-group input-prepend">
                    <label class="control-label" for="site_email">Email&nbsp;Address</label>
                    <div class="controls">
                        <span class="add-on"><i class="icon-envelope"></i></span>
                        <input type="email" name="site_email" id="site_email" required {if isset($site_email)} value="{$site_email}"{/if}>
                        {include file="_usermessage.tpl" field="email" enable_bootstrap=1}
                    </div>
                </div>
                <div class="control-group input-prepend">
                    <label class="control-label" for="password">Choose&nbsp;Password</label>
                    <div class="controls">
                        <span class="add-on"><i class="icon-key"></i></span>
                        <input type="password" name="password" id="password"{if isset($password)} value="{$password}"{/if}
                            class="password" required >
                        

                    </div>
                </div>
                <div class="control-group input-prepend">
                    <label class="control-label" for="password">Confirm&nbsp;Password</label>
                    <div class="controls">
                        <span class="add-on"><i class="icon-key"></i></span>            
                        <input type="password" name="confirm_password" id="confirm_password" required
                            {if isset($confirm_password)} value="{$confirm_password}"{/if} class="password">
                        <br /><br />
                        {include file="_usermessage.tpl" field="password" enable_bootstrap=1}
                    </div>
                </div>
                <div class="control-group ">
                    <label class="control-label" for="site_email">Time&nbsp;Zone</label>
                    <div class="controls">
                          <select name="timezone" id="timezone">
                          <option value=""{if $current_tz eq ''} selected{/if}>Select a Time Zone:</option>
                            {foreach from=$tz_list key=group_name item=group}
                              <optgroup label='{$group_name}'>
                                {foreach from=$group item=tz}
                                  <option value='{$tz.val}'{if $current_tz eq $tz.val} selected{/if}>{$tz.display}</option>
                                {/foreach}
                              </optgroup>
                            {/foreach}
                          </select>
                          <script type="text/javascript" src="{$site_root_path}assets/js/extlib/detect_timezone.js"></script>
                          <script type="text/javascript">
                          {literal}
                          var tz_info = jstz.determine_timezone().timezone;
                          var tz_option_id = '#tz-' + tz_info.olson_tz;
                          if( $('#timezone option[value=' + tz_info.olson_tz + ']').length > 0) {
                              if( $(tz_option_id) ) {
                                  $('#timezone').val(tz_info.olson_tz);
                              }
                          }
                          {/literal}
                          </script>
                          <span class="input_information"></span>
                        {include file="_usermessage.tpl" field="timezone" enable_bootstrap=1}
                    </div>
                </div>
 
            </fieldset>


            <fieldset style="padding-bottom : 0px;">

                <legend>Connect ThinkUp to Your Database</legend>
                  {include file="_usermessage.tpl" field="database" enable_bootstrap=1}
                  
                <div class="control-group">
                    <label class="control-label" for="db_host">Database Host</label>
                    <div class="controls">
                        <input type="text" name="db_host" id="db_host" placeholder="localhost"{if isset($db_host)} value="{$db_host}"{/if}>
                        <span class="help-inline">Usually <strong>localhost</strong> or specified by your hosting provider.</span>
                        {include file="_usermessage.tpl" field="database_host" enable_bootstrap=1}
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="db_name">Database Name</label>
                    <div class="controls">
                        <input type="text" name="db_name" id=""{if isset($db_name)} value="{$db_name}"{/if}>
                        <span class="help-inline">If the database does not exist, ThinkUp will attempt to create it.</span>
                        {include file="_usermessage.tpl" field="database_name" enable_bootstrap=1}
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="db_user">User Name</label>
                    <div class="controls">
                        <input type="text" name="db_user" id="db_user"{if isset($db_user)} value="{$db_user}"{/if}>
                        <span class="help-inline">Your MySQL username.</span>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="db_passwd">Password</label>
                    <div class="controls">
                        <input type="password" name="db_passwd" class="ignore" id="db_passwd"{if isset($db_passwd)} value="{$db_passwd}"{/if}>
                        <span class="help-inline">Your MySQL password.</span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label"></label>
                    <div class="controls">
                        <span class="help-inline">
                        These options are only necessary for some sites. If you're not sure what you should enter here,
                        leave the default settings or check with your hosting provider.</span>
                          
                        <a class="btn " data-toggle="collapse" data-target="#advanced-setup" style="margin-top: 12px;">Show Advanced Options <i class="icon-chevron-down icon-white"></i></a>
                
                    </div>
                </div>

                <div class="in collapse" id="advanced-setup" style="height: auto;">

                    <div class="control-group">
                        <label class="control-label" for="db_socket">Database Socket</label>
                        <div class="controls">
                            <input type="text" name="db_socket" id="db_socket"{if isset($db_socket)} value="{$db_socket}"{/if}>
                            <span class="help-inline">If you're not sure about this, leave it blank.</span>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="db_port">Database Port</label>
                        <div class="controls">
                            <input type="text" name="db_port" id="db_port"{if isset($db_port)} value="{$db_port}"{/if}>
                            <span class="help-inline">If you're not sure about this, leave it blank.</span>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="db_prefix">Table Prefix</label>
                        <div class="controls">
                            <input type="text" name="db_prefix" id="db_prefix"{if isset($db_prefix)} value="{$db_prefix}"{/if}>
                            <span class="help-inline">Optional prefix for your ThinkUp tables.</span>
                        </div>
                    </div>
                
                </div>

                <div class="form-actions">
                    <input type="submit" name="Submit" class="next_step linkbutton btn btn-primary" value="Set It Up &raquo">
                </div>
                
            </fieldset>

            </form>

        </div>
    </div>

        
</div>
  
{include file="_footer.tpl" enable_bootstrap=1}