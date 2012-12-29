{include file="_header.tpl" enable_bootstrap="true" register_form="true"}
{include file="_statusbar.tpl" enable_bootstrap="true"}


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


        {include file="_usermessage.tpl" enable_bootstrap="true"}
        
        {if !$closed and !$has_been_registered}

            <form name="form1" method="post" id="registerform" action="register.php{if $invite_code}?code={$invite_code|filter_xss}{/if}" class="login form-horizontal">

                <fieldset style="background-color : white; padding-top : 30px;">
                    
                    <div class="control-group">
                        <label for="full_name" class="control-label">Name:</label>
                        <div class="controls">
                            <input name="full_name" type="text" id="full_name"{if  isset($name)} value="{$name|filter_xss}"{/if}>
                        </div>
                    </div>
                    
                    <div class="control-group"> 
                        
                        <label for="email" class="control-label">Email:</label>
                        <div class="controls">
                            <input name="email" type="text" id="email"{if  isset($mail)} value="{$mail|filter_xss}"{/if}> {include file="_usermessage.tpl" field="email" enable_bootstrap="true" inline="true"}
                        </div>
                    </div>
                    
                    <div class="control-group"> 
                        
                        <label for="pass1" class="control-label">Password:</label>
                        <div class="controls">

                            <div class="password-meter">
                                <input type="password" name="pass1" id="pass1" class="password"><span for="pass1" class="password-meter-message"> </span> {include file="_usermessage.tpl" field="password" enable_bootstrap="true" inline="true"}
                                
                                <div class="password-meter-bg">
                                    <div class="password-meter-bar"></div>
                                </div>
                            </div>                   
                            
                        </div>
                        
                    </div>

                    <div class="control-group"> 
                        <label for="pass2" class="control-label">Retype password:</label>
                        <div class="controls">
                            <input name="pass2" type="password" id="pass2" class="password">
                        </div>
                    </div>
                    
                    <div class="control-group">
                        
                        <label for="user_code" class="control-label">Prove you&rsquo;re human:</label>
                        <div class="controls">
                            {$captcha} {include file="_usermessage.tpl" field="captcha" enable_bootstrap="true" inline="true"}
                            
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

{include file="_footer.tpl" enable_bootstrap="true"}
