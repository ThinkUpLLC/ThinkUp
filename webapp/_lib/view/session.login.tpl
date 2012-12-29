{include file="_header.tpl" enable_bootstrap="true"}
{include file="_statusbar.tpl" enable_bootstrap="true"}

<div class="container">

<div class="row">
    <div class="span3">
          <div class="embossed-block">
            <ul>
              <li>Log In</li>
            </ul>
          </div>
    </div><!--/span3-->
    <div class="span6">

        {include file="_usermessage.tpl" enable_bootstrap="true"}

            <form name="login-form" method="post" action="{$site_root_path}session/login.php" class="login form-horizontal">

                <fieldset style="background-color : white; padding-top : 30px;">

                    <div class="control-group">
                        <label class="control-label" for="email">Email</label>
                        <div class="controls">
                            <input class="input-xlarge" type="text" name="email" id="email"{if isset($email)} value="{$email|filter_xss}"{/if} autofocus="autofocus">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="pwd">Password</label>
                        <div class="controls">
                            <input class="input-xlarge" type="password" name="pwd" id="pwd">
                        </div>
                    </div>

                    <div class="form-actions">
                            <input type="submit" id="login-save" name="Submit" class="btn btn-primary" value="Log In">
                            <span class="pull-right">
                                <div class="btn-group">
                                    {if $is_registration_open}<a href="register.php" class="btn btn-mini">Register</a>{else}<span class="btn btn-mini disabled">Registration closed</span>{/if}
                                    <a href="forgot.php" class="btn btn-mini">Forgot password</a>
                                    {insert name="help_link" id='login'}
                                </div>
                            </span>
                    </div>

                </fieldset>

            </form>

    </div><!-- end span9 -->

</div><!-- end row -->

{include file="_footer.tpl" enable_bootstrap="true"}
