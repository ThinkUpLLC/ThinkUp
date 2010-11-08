{include file="_header.tpl"}
{include file="_statusbar.tpl"}
<div class="container_24 thinkup-canvas round-all">
  <div class="prepend_20">
    <h1>Log In</h1>
  </div>
  <div class="clearfix prepend_20">
    <div class="grid_17 prefix_3 left">
    {include file="_usermessage.tpl"}
    </div>
  </div>
  <form name="form1" method="post" action="{$site_root_path}session/login.php" class="login">
    <div class="clearfix">
      <div class="grid_4 prefix_5 right">
        <label for="email">
          Email:
        </label>
      </div>
      <div class="grid_10 left">
        <input type="text" name="email" id="email"{if isset($email)} value="{$email}"{/if}>
      </div>
    </div>
    <div class="clearfix">
      <div class="grid_4 prefix_5 right">
        <label for="pwd">
          Password:
        </label>
      </div>
      <div class="grid_10 left">
        <input type="password" name="pwd" id="pwd">
      </div>
    </div>
    <div class="clearfix">
      <div class="grid_10 prefix_9 left">
        <input type="submit" id="login-save" name="Submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Log In">
      </div>
    </div>
  </form>
  <div class="center prepend_20 append_20">
    <a href="register.php">Register</a> |
    <a href="forgot.php">Forgot password</a>
  </div>
</div>

{include file="_footer.tpl"}
