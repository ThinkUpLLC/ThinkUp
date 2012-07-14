{include file="_header.tpl" enable_bootstrap="true"}
{include file="_statusbar.tpl" enable_bootstrap="true"}


<div class="container">

<div class="row">
    <div class="span3">
          <div class="sidebar-nav">
            <ul class="nav nav-list">
              <li class="">
                    Log In
               </li>
            </ul>
          </div><!--/.well -->
    </div><!--/span3-->
    <div class="span9">




        {include file="_usermessage.tpl" enable_bootstrap="true"}

            <form name="login-form" method="post" action="{$site_root_path}session/login.php" class="login form-horizontal">

                <fieldset style="background-color : white; padding-top : 30px;">
                    
                    <div class="control-group">
                        <label class="control-label" for="email">Email:</label>
                        <div class="controls">
                            <input class="input-xlarge" type="text" name="email" id="email"{if isset($email)} value="{$email|filter_xss}"{/if}>
                        </div>
                    </div>
                    
                    <div class="control-group">        
                        <label class="control-label" for="pwd">Password:</label>
                        <div class="controls">
                            <input class="input-xlarge" type="password" name="pwd" id="pwd">
                        </div>
                    </div>
                
                    <div class="form-actions">

                            <input type="submit" id="login-save" name="Submit" class="btn btn-primary" value="Log In">
                        
                    </div>

                </fieldset>

                    <div class="control-group">
                        <div class="controls">
                            <p class="help-block"><a href="register.php">Register</a> |
                                <a href="forgot.php">Forgot password</a> |
                                {insert name="help_link" id='login'}</p>
                        </div>
                    </div>
                                             

            </form>

    </div><!-- end span9 -->

</div><!-- end row -->


</div> <!-- end container -->
{include file="_footer.tpl" enable_bootstrap="true"}
