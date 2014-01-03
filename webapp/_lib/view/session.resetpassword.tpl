{include file="_header.tpl"}
{include file="_statusbar.tpl"}


<div class="container">

<div class="row">
    <div class="col-md-3">
    </div><!--/col-md-3-->
    <div class="col-md-9">

    {if isset($error_msg)}
        <div class="alert alert-error"><p>{$error_msg}</p></div>
    {/if}
    {if isset($success_msg)}
        <div class="alert alert-success"><p>{$success_msg}</p></div>
    {/if}

            {if !isset($error_msg) && !isset($success_msg)}

            <div class="panel panel-default">

            <form name="form1" method="post" action="" class="login form-horizontal">

                <fieldset >
                <legend class="panel-heading">Reset your password</legend>
                
                <div class="panel-body">

                    <div class="form-group">
                    <label class="col-sm-2" for="password">New Password</label>
                    <div class="col-sm-8">
                        <span class="input-group">
                            <span class="input-group-addon"><i class="fa fa-key"></i></span>
                            <input type="password" name="password" id="password" 
                            {literal}pattern="^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*).{8,}$"{/literal} class="password form-control" required 
                            data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> You'll need a enter a password of at least 8 characters." 
                            data-validation-pattern-message="<i class='fa fa-exclamation-triangle'></i> Must be at least 8 characters, with both numbers & letters.">
                        </span>
                        <span class="help-block"></span>

                        </div>
	                </div>
	                <div class="form-group">
	                    <label class="col-sm-2" for="confirm_password">Confirm&nbsp;new Password</label>
	                    <div class="col-sm-8">
	                        <span class="input-group">
	                            <span class="input-group-addon"><i class="fa fa-key"></i></span>            
	                            <input type="password" name="password_confirm" id="confirm_password" required 
	                             class="password form-control" 
	                            data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> Password confirmation is required." 
	                            data-validation-match-match="password" 
	                            data-validation-match-message="<i class='fa fa-exclamation-triangle'></i> Make sure this matches the password you entered above." >
	                        </span>
	                        <span class="help-block"></span>
	                        {include file="_usermessage.tpl" field="password"}
	                    </div>
	                </div>
                    
                    <div class="form-group form-actions">
                            <input type="submit" id="login-save" name="Submit" class="btn btn-primary" value="Submit">
                            <span class="pull-right">
                                <div class="btn-group">
                                    <a href="login.php" class="btn btn-xs">Log In</a>
                                    {if $is_registration_open}<a href="register.php" class="btn btn-xs hidden-phone">Register</a>{else}{/if}
                                    {insert name="help_link" id='reset'}
                                </div>
                            </span>
                    </div>
                    
                </div>

                </fieldset>

            </form>
            
            </div>
            {/if}

    </div><!-- end col-md-9 -->

</div><!-- end row -->

{include file="_footer.tpl"}
