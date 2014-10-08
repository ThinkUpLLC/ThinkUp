{include file="_header.tpl" body_classes="settings account menu-off"}
{include file="_navigation.tpl"}

<div class="container">

            <header class="container-header">
                <h1>Create your ThinkUp account</h1>
            </header>



            <form class="input form" name="install_form" method="post" action="index.php?step=3" role="form" id="form-signin">

            <fieldset class="fieldset-no-header">

           <div class="form-group">
                <label for="full_name" class="control-label">Name</label>
                <input type="text" name="full_name" id="full_name" required class="form-control" {if isset($full_name)} value="{$full_name}"{/if}
                data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> Name can't be blank.">
                <span class="help-block"></span>
            </div>

            <div class="form-group">
                <label class="control-label" for="site_email">Email</label>
                <input type="email" name="site_email" id="site_email" required class="form-control" {if isset($site_email)} value="{$site_email}"{/if} placeholder="you@example.com"
                data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> A valid email address is required.">
                <span class="help-block"></span>
                {include file="_usermessage.tpl" field="email"}
            </div>

            <div class="form-group">
                <label class="control-label" for="password">Password</label>
                <input type="password" name="password" id="password" {if isset($password)} value="{$password}"{/if}
                {literal}pattern="^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*).{8,}$"{/literal} class="password form-control" required  placeholder="********"
                data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> You'll need a enter a password of at least 8 characters."
                data-validation-pattern-message="<i class='fa fa-exclamation-triangle'></i> Must be at least 8 characters, with both numbers & letters.">
                <span class="help-block"></span>
            </div>
            <div class="form-group">
                <label class="control-label" for="confirm_password">Confirm&nbsp;Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required
                {if isset($confirm_password)} value="{$confirm_password}"{/if} class="password form-control"
                data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> Password confirmation is required."  placeholder="********"
                data-validation-match-match="password"
                data-validation-match-message="<i class='fa fa-exclamation-triangle'></i> Make sure this matches the password you entered above." >
                <span class="help-block"></span>
                {include file="_usermessage.tpl" field="password"}
            </div>
                <div class="form-group ">
                    <label class="control-label" for="timezone">Time zone</label>
                          <select name="timezone" id="timezone" class="form-control picker">
                          <option value="" {if $current_tz eq ''} selected{/if}>Select a Time Zone:</option>
                            {foreach from=$tz_list key=group_name item=group}
                              <optgroup label='{$group_name}'>
                                {foreach from=$group item=tz}
                                  <option id="tz-{$tz.display}" value='{$tz.val}'{if $current_tz eq $tz.val} selected{/if}>{$tz.display}</option>
                                {/foreach}
                              </optgroup>
                            {/foreach}
                          </select>
                          <span class="input_information"></span>
                        {include file="_usermessage.tpl" field="timezone"}
                </div>

            </fieldset>

            <fieldset>
                <header>
                    <h2>Connect ThinkUp to Your Database</h2>
                </header>


                 <div class="form-group">
                    <label class="control-label"></label>
                    {include file="_usermessage.tpl" field="database}
                </div>

                <div class="form-group">
                    <label class="control-label" for="db_host">Database Host</label>
                        <input type="text" name="db_host" id="db_host" placeholder="localhost"{if isset($db_host) && $db_host != ''} value="{$db_host}" {else} value="localhost" {/if} required class="form-control"
                        data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> A database host is required - if you don't know yours, try 'localhost'.">
                        <span class="help-block">Usually <strong>localhost</strong> or specified by your hosting provider.</span>
                        {include file="_usermessage.tpl" field="database_host"}
                </div>
                <div class="form-group">
                    <label class="control-label" for="db_name">Database Name</label>
                        <input type="text" name="db_name" id="db_name"{if isset($db_name)} value="{$db_name}"{/if} required class="form-control"
                        data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> ThinkUp needs the name of the database where it will store its data.">
                        <span class="help-block">If the database does not exist, ThinkUp will attempt to create it.</span>
                        {include file="_usermessage.tpl" field="database_name"}
                </div>
                <div class="form-group">
                    <label class="control-label" for="db_user">User Name</label>
                        <input type="text" name="db_user" id="db_user"{if isset($db_user)} value="{$db_user}"{/if} required class="form-control"
                        data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> ThinkUp will need the MySQL user name for your database user.">
                        <span class="help-block">Your MySQL username.</span>
                </div>
                <div class="form-group">
                    <label class="control-label" for="db_passwd">Password</label>
                        <input type="password" name="db_passwd" class="ignore form-control" id="db_passwd"{if isset($db_passwd)} value="{$db_passwd}"{/if}>
                        <span class="help-block">Your MySQL password.</span>
                </div>

            </fieldset>


                <header>
                        <h1><a class="btn " data-toggle="collapse" data-target="#advanced-setup" style="margin-top: 12px;">Show Advanced Options <i class="fa fa-chevron-down icon-white"></i></a></h1>
                </header>

            <fieldset class="in collapse" id="advanced-setup" style="height: auto;">

                <header>
                    <p class="help-text">These options are only necessary for some sites. If you're not sure what you should enter here,
                        leave the default settings or check with your hosting provider.</p>
                </header>

                    <div class="form-group">
                        <label class="control-label" for="db_socket">Database Socket</label>
                            <input type="text" name="db_socket" id="db_socket" class="form-control" {if isset($db_socket)} value="{$db_socket}"{/if}>
                            <span class="help-block">If you're not sure about this, leave it blank.</span>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="db_port">Database Port</label>
                            <input type="text" name="db_port" id="db_port" class="form-control" {if isset($db_port)} value="{$db_port}"{/if}>
                            <span class="help-block">If you're not sure about this, leave it blank.</span>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="db_prefix">Table Prefix</label>
                            <input type="text" name="db_prefix" id="db_prefix" class="form-control" {if isset($db_prefix)} value="{$db_prefix}"{/if}>
                            <span class="help-block">Optional prefix for your ThinkUp tables.</span>
                    </div>
            </fieldset>

                        <input type="submit" name="Submit" class="next_step linkbutton btn btn-primary btn-circle btn-submit" id="nextstep" value="Set It Up">

            </form>

</div>


{include file="_footer.tpl" include_jstz="true"}
