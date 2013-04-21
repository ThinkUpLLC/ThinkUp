{include file="_header.tpl" enable_bootstrap=1}
{include file="_statusbar.tpl" enable_bootstrap=1}


<div class="container">

<div class="row">
    <div class="span3">
          <div class="embossed-block">
            <ul>
              <li>Reset Your Password</li>
            </ul>
          </div>
    </div><!--/span3-->
    <div class="span6">

    {if isset($error_msg)}
        <div class="alert alert-error"><p>{$error_msg}</p></div>
    {/if}
    {if isset($success_msg)}
        <div class="alert alert-success"><p>{$success_msg}</p></div>
    {/if}

            {if !isset($error_msg) && !isset($success_msg)}
            <form name="form1" method="post" action="" class="login form-horizontal">

                <fieldset style="background-color : white; padding-top : 30px;">
                    <div class="control-group">
                    <label class="control-label" for="password">New Password</label>
                    <div class="controls">
                        <span class="input-prepend">
                            <span class="add-on"><i class="icon-key"></i></span>
                            <input type="password" name="password" id="password" 
                            {literal}pattern="^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*).{8,}$"{/literal} class="password" required 
                            data-validation-required-message="<i class='icon-exclamation-sign'></i> You'll need a enter a password of at least 8 characters." 
                            data-validation-pattern-message="<i class='icon-exclamation-sign'></i> Must be at least 8 characters, with both numbers & letters.">
                        </span>
                        <span class="help-inline"></span>

                        </div>
	                </div>
	                <div class="control-group">
	                    <label class="control-label" for="confirm_password">Confirm&nbsp;new Password</label>
	                    <div class="controls">
	                        <span class="input-prepend">
	                            <span class="add-on"><i class="icon-key"></i></span>            
	                            <input type="password" name="password_confirm" id="confirm_password" required 
	                             class="password" 
	                            data-validation-required-message="<i class='icon-exclamation-sign'></i> Password confirmation is required." 
	                            data-validation-match-match="password" 
	                            data-validation-match-message="<i class='icon-exclamation-sign'></i> Make sure this matches the password you entered above." >
	                        </span>
	                        <span class="help-block"></span>
	                        {include file="_usermessage.tpl" field="password" enable_bootstrap=1}
	                    </div>
	                </div>
                    
                    <div class="form-actions">
                            <input type="submit" id="login-save" name="Submit" class="btn btn-primary" value="Submit">
                            <span class="pull-right">
                                <div class="btn-group">
                                    <a href="login.php" class="btn btn-mini">Log In</a>
                                    {if $is_registration_open}<a href="register.php" class="btn btn-mini hidden-phone">Register</a>{else}{/if}
                                    {insert name="help_link" id='reset'}
                                </div>
                            </span>
                    </div>

                </fieldset>

            </form>
            {/if}

    </div><!-- end span9 -->

</div><!-- end row -->

{include file="_footer.tpl" enable_bootstrap=1}
