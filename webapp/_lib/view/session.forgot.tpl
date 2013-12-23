{include file="_header.tpl"}
{include file="_statusbar.tpl"}


<div class="container">

<div class="row">
    <div class="col-md-3">
    </div><!--/col-md-3-->
    <div class="col-md-9">

    {if isset($error_msgs)}
        <div class="alert alert-error"><p>{$error_msg}</p></div>
    {/if}
    {if isset($success_msg)}
        <div class="alert alert-success"><p>{$success_msg}</p></div>
    {/if}

            <div class="panel panel-default">

            <form name="forgot-form" method="post" action="" class="login form-horizontal">

                <fieldset>
                <legend class="panel-heading">Forgot your password?</legend>
                
                <div class="panel-body">
                
                    <div class="form-group">
                        <label class="col-sm-2" for="site_email">Email&nbsp;Address</label>
                        <div class="col-sm-8">
                            <span class="input-group">
                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control" required 
                                data-validation-required-message="<i class='fa fa-exclamation-triangle'></i> A valid email address is required.">
                            </span>
                            <span class="help-block">Put in your email address and you'll get emailed a link to reset your password.</span>
                            {include file="_usermessage.tpl" field="email"}
                        </div>
                    </div>
                    
                    <div class="form-group form-actions">
                        <label class="col-sm-2"></label>
                        <div class="col-sm-8">
                            <input type="submit" id="login-save" name="Submit" class="btn btn-primary" value="Okay, send it">
                            <span class="pull-right">
                                <div class="btn-group">
                                    <a href="login.php" class="btn btn-xs">Log In</a>
                                    {if $is_registration_open}<a href="register.php" class="btn btn-xs hidden-phone">Register</a>{else}{/if}
                                    {insert name="help_link" id='forgot'}
                                </div>
                            </span>
                        </div>
                    </div>
                    
                </div>

                </fieldset>
            </form>
            
            </div><!-- /panel -->

    </div><!-- end col-md-9 -->

</div><!-- end row -->

{include file="_footer.tpl"}
