{include file="_header.tpl"}
{include file="_statusbar.tpl"}


<div class="container_24 thinkup-canvas round-all clearfix">

    <div class="grid_18" style="margin-bottom : 20px; margin-left : 100px;">
        {include file="_usermessage.tpl"}
    </div>
    
    <div class="grid_18 section" style="margin-bottom : 100px; margin-left : 100px;">
    
        {insert name="help_link" id='login'}
    
        <h2>Log In</h2>
        <div class="article">
            <form name="form1" method="post" action="{$site_root_path}session/login.php" class="login" style="padding-bottom : 20px;">
            <div class="clearfix">
              <div class="grid_4 prefix_2 right">
                <label for="email">
                  Email:
                </label>
              </div>
              <div class="grid_10 left">
                <input type="text" name="email" id="email"{if isset($email)} value="{$email|filter_xss}"{/if}>
              </div>
            </div>
            <div class="clearfix">
              <div class="grid_4 prefix_2 right">
                <label for="pwd">
                  Password:
                </label>
              </div>
              <div class="grid_10 left">
                <input type="password" name="pwd" id="pwd">
              </div>
            </div>
            <div class="clearfix">
              <div class="grid_10 prefix_6 left">
                <input type="submit" id="login-save" name="Submit" class="linkbutton emphasized" value="Log In">
              </div>
            </div>
            </form>
        </div>
        <div class="view-all">
        <a href="register.php">Register</a> |
        <a href="forgot.php">Forgot password</a>
        </div>
    </div>

</div>
{include file="_footer.tpl"}
