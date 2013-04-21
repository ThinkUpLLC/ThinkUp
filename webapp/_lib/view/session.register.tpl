{include file="_header.tpl" enable_bootstrap=1}
{include file="_statusbar.tpl" enable_bootstrap=1}


<div class="container">

<div class="row">
    <div class="span3">
          <div class="embossed-block">
            <ul>
              <li>Register</li>
            </ul>
          </div>
    </div><!--/span3-->
    <div class="span6">


        {include file="_usermessage.tpl" enable_bootstrap=1}
        
        {if !$closed and !$has_been_registered}

            <form name="form1" method="post" id="registerform" action="register.php{if $invite_code}?code={$invite_code|filter_xss}{/if}" class="login form-horizontal">

                <fieldset style="background-color : white; padding-top : 30px;">
                    
                    <div class="control-group">
                        <label for="full_name" class="control-label">Name:</label>
                        <div class="controls">
                            <input type="text" name="full_name" id="full_name" required {if  isset($name)} value="{$name|filter_xss}"{/if} 
                            data-validation-required-message="<i class='icon-exclamation-sign'></i> Name can't be blank.">
                        	<span class="help-inline"></span>
                        </div>
                    </div>
                    
                <div class="control-group">
                    <label class="control-label" for="email">Email&nbsp;Address</label>
                    <div class="controls">
                        <span class="input-prepend">
                            <span class="add-on"><i class="icon-envelope"></i></span>
                            <input type="email" name="email" id="email"{if  isset($mail)} value="{$mail|filter_xss}"{/if} required 
                            data-validation-required-message="<i class='icon-exclamation-sign'></i> A valid email address is required.">
                        </span>
                        <span class="help-inline"></span>
                        {include file="_usermessage.tpl" field="email" enable_bootstrap=1}
                    </div>
                </div>
                    <div class="control-group">
                    <label class="control-label" for="password">Password</label>
                    <div class="controls">
                        <span class="input-prepend">
                            <span class="add-on"><i class="icon-key"></i></span>
                            <input type="password" name="pass1" id="pass1"
                            {literal}pattern="^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*).{8,}$"{/literal} class="password" required 
                            data-validation-required-message="<i class='icon-exclamation-sign'></i> You'll need a enter a password of at least 8 characters." 
                            data-validation-pattern-message="<i class='icon-exclamation-sign'></i> Must be at least 8 characters, with both numbers & letters.">
                        </span>
                        <span class="help-inline"></span>
                    </div>
                </div>
                    <div class="control-group">
                    <label class="control-label" for="pass2">Confirm&nbsp;Password</label>
                    <div class="controls">
                        <span class="input-prepend">
                            <span class="add-on"><i class="icon-key"></i></span>
                            <input type="password" name="pass2" id="pass2" required 
                            class="password" 
                            data-validation-required-message="<i class='icon-exclamation-sign'></i> Password confirmation is required." 
                            data-validation-match-match="pass1" 
                            data-validation-match-message="<i class='icon-exclamation-sign'></i> Make sure this matches the password you entered above." >
                        </span>
                        <span class="help-block"></span>
                        {include file="_usermessage.tpl" field="password" enable_bootstrap=1}
                    </div>
                </div>
                    
                    <div class="control-group">
                        <label for="user_code" class="control-label">Prove you&rsquo;re human:</label>
                        <div class="controls">
                            {$captcha} {include file="_usermessage.tpl" field="captcha" enable_bootstrap=1 inline="true"}
                        </div>
                    </div>

                    <div class="form-actions">
                            <input type="submit" name="Submit" id="login-save" class="btn btn-primary" value="Register">
                            <span class="pull-right">
                                <div class="btn-group">
                                    <a href="login.php" class="btn btn-mini">Log In</a>
                                    <a href="forgot.php" class="btn btn-mini">Forgot password</a>
                                    {insert name="help_link" id='register'}
                                </div>
                            </span>
                        
                    </div>

                </fieldset>


{if !$success_msg}
                    <div class="control-group">
                        <div class="controls">
                            
                        </div>
                    </div>
{/if}                                           

            </form>
            
        {/if}

    </div>
</div>

{include file="_footer.tpl" enable_bootstrap=1}
