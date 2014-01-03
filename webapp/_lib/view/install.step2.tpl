{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div id="main" class="container">

    <div class="navbar">
        <span class="navbar-brand" style="margin-top: 12px;">Install ThinkUp:</span>
        <ul class="nav navbar-nav nav-pills pull-left">
            <li><a> <h4><i class="fa fa-check "></i> Check System Requirements</h4></a></li>
            <li class="active"><a class="disabled"> <h4><i class="fa fa-cogs"></i> Configure ThinkUp</h4></a></li>
            <li><a class="disabled"> <h4><i class="fa fa-lightbulb"></i> Finish</h4></a></li>
        </ul>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            
        </div>
        <div class="col-md-9">

            <form class="input form-horizontal" name="install_form" method="post" action="index.php?step=3" role="form">
            
            {include file="_usermessage.tpl"}
            
            <div class="panel panel-default">

            <fieldset>
            <legend class="panel-heading">Create your ThinkUp account</legend>
                
            <div class="panel-body">

                <div class="form-group">
                    <label for="full_name" class="col-sm-2 control-label">Name</label>
                    <div class="col-sm-10">
                        <input type="text" name="full_name" id="full_name" required class="form-control" {if isset($full_name)} value="{$full_name}"{/if} 
                        data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> Name can't be blank.">
                        <span class="help-block"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label" for="site_email">Email&nbsp;Address</label>
                    <div class="col-sm-10">
                        <span class="input-group">
                            <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                            <input type="email" name="site_email" id="site_email" required class="form-control" {if isset($site_email)} value="{$site_email}"{/if} 
                            data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> A valid email address is required.">
                        </span>
                        <span class="help-block"></span>
                        {include file="_usermessage.tpl" field="email"}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="password">Password</label>
                    <div class="col-sm-10">
                        <span class="input-group">
                            <span class="input-group-addon"><i class="fa fa-key"></i></span>
                            <input type="password" name="password" id="password" {if isset($password)} value="{$password}"{/if}
                            {literal}pattern="^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*).{8,}$"{/literal} class="password form-control" required 
                            data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> You'll need a enter a password of at least 8 characters." 
                            data-validation-pattern-message="<i class='fa fa-exclamation-triangle'></i> Must be at least 8 characters, with both numbers & letters.">
                        </span>
                        <span class="help-block"></span>

                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="confirm_password">Confirm&nbsp;Password</label>
                    <div class="col-sm-10">
                        <span class="input-group">
                            <span class="input-group-addon"><i class="fa fa-key"></i></span>            
                            <input type="password" name="confirm_password" id="confirm_password" required 
                            {if isset($confirm_password)} value="{$confirm_password}"{/if} class="password form-control" 
                            data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> Password confirmation is required." 
                            data-validation-match-match="password" 
                            data-validation-match-message="<i class='fa fa-exclamation-triangle'></i> Make sure this matches the password you entered above." >
                        </span>
                        <span class="help-block"></span>
                        {include file="_usermessage.tpl" field="password"}
                    </div>
                </div>
                <div class="form-group ">
                    <label class="col-sm-2 control-label" for="timezone">Time&nbsp;Zone</label>
                    <div class="col-sm-10">
                          <select name="timezone" id="timezone" class="form-control">
                          <option value="" {if $current_tz eq ''} selected{/if}>Select a Time Zone:</option>
                            {foreach from=$tz_list key=group_name item=group}
                              <optgroup label='{$group_name}'>
                                {foreach from=$group item=tz}
                                  <option id="tz-{$tz.display}" value='{$tz.val}'{if $current_tz eq $tz.val} selected{/if}>{$tz.display}</option>
                                {/foreach}
                              </optgroup>
                            {/foreach}
                          </select>
                          
                          <script type="text/javascript">
                          {literal}
                          var tz_info = jstz.determine();
                          var regionname = tz_info.name().split('/');
                          var tz_option_id = '#tz-' + regionname[1];
                          if( $('#timezone option[value="' + tz_info.name() + '"]').length > 0) {
                              if( $(tz_option_id) ) {
                                  $('#timezone').val( tz_info.name());
                              }
                          }
                          {/literal}
                          </script>
                          <span class="input_information"></span>
                        {include file="_usermessage.tpl" field="timezone"}
                    </div>
                </div>
                
            </div>
 
            </fieldset>
            
            </div>

            <div class="panel panel-default">

            <fieldset style="padding-bottom : 0px;">

                <legend class="panel-heading">Connect ThinkUp to Your Database</legend>

                <div class="panel-body">
 
                 <div class="form-group">
                    <label class="col-sm-2 control-label"></label>
                    <div class="col-sm-10">
                        {include file="_usermessage.tpl" field="database}
                    </div>
                </div>
                  
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="db_host">Database Host</label>
                    <div class="col-sm-10">
                        <input type="text" name="db_host" id="db_host" placeholder="localhost"{if isset($db_host)} value="{$db_host}"{/if} required class="form-control" 
                        data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> A database host is required - if you don't know yours, try 'localhost'.">
                        <span class="help-block">Usually <strong>localhost</strong> or specified by your hosting provider.</span>
                        {include file="_usermessage.tpl" field="database_host"}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="db_name">Database Name</label>
                    <div class="col-sm-10">
                        <input type="text" name="db_name" id=""{if isset($db_name)} value="{$db_name}"{/if} required class="form-control" 
                        data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> ThinkUp needs the name of the database where it will store its data.">
                        <span class="help-block">If the database does not exist, ThinkUp will attempt to create it.</span>
                        {include file="_usermessage.tpl" field="database_name"}
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="db_user">User Name</label>
                    <div class="col-sm-10">
                        <input type="text" name="db_user" id="db_user"{if isset($db_user)} value="{$db_user}"{/if} required class="form-control" 
                        data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> ThinkUp will need the MySQL user name for your database user.">
                        <span class="help-block">Your MySQL username.</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="db_passwd">Password</label>
                    <div class="col-sm-10">
                        <input type="password" name="db_passwd" class="ignore form-control" id="db_passwd"{if isset($db_passwd)} value="{$db_passwd}"{/if}>
                        <span class="help-block">Your MySQL password.</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label"></label>
                    <div class="col-sm-10">
                        <p><a class="btn " data-toggle="collapse" data-target="#advanced-setup" style="margin-top: 12px;">Show Advanced Options <i class="fa fa-chevron-down icon-white"></i></a></p>
                    </div>
                </div>

                <div class="in collapse" id="advanced-setup" style="height: auto;">
               <div class="form-group">
                    <label class="col-sm-2 control-label"></label>
                    <div class="col-sm-10">
                        <div class="help">
                        These options are only necessary for some sites. If you're not sure what you should enter here,
                        leave the default settings or check with your hosting provider.</div>
                    </div>
                </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="db_socket">Database Socket</label>
                        <div class="col-sm-10">
                            <input type="text" name="db_socket" id="db_socket" class="form-control" {if isset($db_socket)} value="{$db_socket}"{/if}>
                            <span class="help-block">If you're not sure about this, leave it blank.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="db_port">Database Port</label>
                        <div class="col-sm-10">
                            <input type="text" name="db_port" id="db_port" class="form-control" {if isset($db_port)} value="{$db_port}"{/if}>
                            <span class="help-block">If you're not sure about this, leave it blank.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="db_prefix">Table Prefix</label>
                        <div class="col-sm-10">
                            <input type="text" name="db_prefix" id="db_prefix" class="form-control" {if isset($db_prefix)} value="{$db_prefix}"{/if}>
                            <span class="help-block">Optional prefix for your ThinkUp tables.</span>
                        </div>
                    </div>
                
                </div>
            
                </div>
            </fieldset>
            </div>


                <div class="form-group">
                    <label class="col-sm-2 control-label"></label>
                    <div class="col-sm-10">
                        <input type="submit" name="Submit" class="next_step linkbutton btn btn-primary btn-lg" id="nextstep" value="Set It Up &raquo">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label"></label>
                    <div class="col-sm-10">
                    </div>
                </div>

            </form>

        </div>
    </div>

        
</div>
  

{include file="_footer.tpl"}