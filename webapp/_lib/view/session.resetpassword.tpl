{include file="_header.tpl" enable_bootstrap="true"}
{include file="_statusbar.tpl" enable_bootstrap="true"}


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
                        <label class="control-label" for="password">New password:</label>
                        <div class="controls">
                            <input class="input-xlarge" type="password" name="password" id="password">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="password_confirm">New password:</label>
                        <div class="controls">
                            <input class="input-xlarge" type="password" name="password_confirm" id="password_confirm">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                            <input type="submit" id="login-save" name="Submit" class="btn btn-primary" value="Submit">
                            <span class="pull-right">
                                <div class="btn-group">
                                    <a href="login.php" class="btn btn-mini">Log In</a>
                                    {if $is_registration_open}<a href="register.php" class="btn btn-mini">Register</a>{else}<span class="btn btn-mini disabled">Registration closed</span>{/if}
                                    {insert name="help_link" id='reset'}
                                </div>
                            </span>
                    </div>

                </fieldset>

            </form>
            {/if}

    </div><!-- end span9 -->

</div><!-- end row -->

{include file="_footer.tpl" enable_bootstrap="true"}
