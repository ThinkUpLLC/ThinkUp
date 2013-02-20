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




    {if isset($error_msgs)}
        <div class="alert alert-error"><p>{$error_msg}</p></div>
    {/if}
    {if isset($success_msg)}
        <div class="alert alert-success"><p>{$success_msg}</p></div>
    {/if}

            <form name="forgot-form" method="post" action="" class="login form-horizontal">

                <fieldset style="background-color : white; padding-top : 30px;">
                    
                    <div class="control-group input-prepend">
                        <label class="control-label" for="email">Email:</label>
                        <div class="controls">
                            <span class="add-on"><i class="icon-envelope"></i></span>
                            <input class="input-xlarge" type="email" name="email" id="email">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                            <input type="submit" id="login-save" name="Submit" class="btn btn-primary" value="Send Reset">
                            <span class="pull-right">
                                <div class="btn-group">
                                    <a href="login.php" class="btn btn-mini">Log In</a>
                                    {if $is_registration_open}<a href="register.php" class="btn btn-mini hidden-phone">Register</a>{else}{/if}
                                    {insert name="help_link" id='forgot'}
                                </div>
                            </span>
                    </div>

                </fieldset>
            </form>

    </div><!-- end span9 -->

</div><!-- end row -->

{include file="_footer.tpl" enable_bootstrap="true"}
