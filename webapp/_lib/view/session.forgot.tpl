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
                    
                    <div class="control-group">
                        <label class="control-label" for="email">Email:</label>
                        <div class="controls">
                            <input class="input-xlarge" type="text" name="email" id="email">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                            <input type="submit" id="login-save" name="Submit" class="btn btn-primary" value="Send Reset">
                            <span class="pull-right">
                                <a href="login.php">Log In</a> |
                                <a href="register.php">Register</a> |
                                {insert name="help_link" id='forgot'}
                            </span>
                    </div>

                </fieldset>

            </form>

    </div><!-- end span9 -->

</div><!-- end row -->


</div> <!-- end container -->
{include file="_footer.tpl" enable_bootstrap="true"}
