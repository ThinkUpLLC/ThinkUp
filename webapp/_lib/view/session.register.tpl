{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div class="container">

<div class="row">
    <div class="col-md-3">
          &nbsp;
    </div><!--/col-md-3-->
    <div class="col-md-9">

            {include file="_usermessage.tpl"}

            <div class="panel panel-default">

        {if !$closed and !$has_been_registered}
            <form name="form1" method="post" id="registerform" action="register.php{if $invite_code}?code={$invite_code|filter_xss}{/if}" class="login form-horizontal">

            <fieldset>
            <legend class="panel-heading">Register for a ThinkUp Account</legend>
            
                
            <div class="panel-body">

                    <div class="form-group">
                        <label for="full_name" class="col-sm-2">Name:</label>
                        <div class="col-sm-8">
                            <input type="text" name="full_name" id="full_name" class="form-control" required {if  isset($name)} value="{$name|filter_xss}"{/if} 
                            data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> Name can't be blank.">
                        	<span class="help-block"></span>
                        </div>
                    </div>
                    
                <div class="form-group">
                    <label class="col-sm-2" for="email">Email&nbsp;Address</label>
                    <div class="col-sm-8">
                        <span class="input-group">
                            <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                            <input type="email" name="email" id="email" class="form-control"{if  isset($mail)} value="{$mail|filter_xss}"{/if} required 
                            data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> A valid email address is required.">
                        </span>
                        <span class="help-block"></span>
                        {include file="_usermessage.tpl" field="email"}
                    </div>
                </div>
                    <div class="form-group">
                    <label class="col-sm-2" for="password">Password</label>
                    <div class="col-sm-8">
                        <span class="input-group">
                            <span class="input-group-addon"><i class="fa fa-key"></i></span>
                            <input type="password" name="pass1" id="pass1"
                            {literal}pattern="^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*).{8,}$"{/literal} class="form-control password" required 
                            data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> You'll need a enter a password of at least 8 characters." 
                            data-validation-pattern-message="<i class='fa fa-exclamation-triangle'></i> Must be at least 8 characters, with both numbers & letters.">
                        </span>
                        <span class="help-block"></span>
                    </div>
                </div>
                    <div class="form-group">
                    <label class="col-sm-2" for="pass2">Confirm&nbsp;Password</label>
                    <div class="col-sm-8">
                        <span class="input-group">
                            <span class="input-group-addon"><i class="fa fa-key"></i></span>
                            <input type="password" name="pass2" id="pass2" required 
                            class="form-control password" 
                            data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> Password confirmation is required." 
                            data-validation-match-match="pass1" 
                            data-validation-match-message="<i class='fa fa-exclamation-triangle'></i> Make sure this matches the password you entered above." >
                        </span>
                        <span class="help-block"></span>
                        {include file="_usermessage.tpl" field="password"}
                    </div>
                </div>
                    
                    <div class="form-group">
                            {$captcha} 
                            {include file="_usermessage.tpl" field="captcha" inline="true"}
                    </div>

                    <div class="form-group form-actions">
                        <label class="col-sm-2"></label>
                        <div class="col-sm-8">
                            <input type="submit" name="Submit" id="login-save" class="btn btn-primary" value="Register">
                            <span class="pull-right">
                                <div class="btn-group">
                                    <a href="login.php" class="btn btn-xs">Log In</a>
                                    <a href="forgot.php" class="btn btn-xs">Forgot password</a>
                                    {insert name="help_link" id='register'}
                                </div>
                            </span>
                        </div>
                        
                    </div>

                </div><!-- /panel-body -->

                </fieldset>

            </form>
        </div><!-- /panel -->
            
        {/if}

    </div>
</div>

{include file="_footer.tpl"}
