{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div class="container_24 thinkup-canvas round-all">
  <div style="float:right;margin:20px">{insert name="help_link" id='forgot'}</div>
  <div class="prepend_20">
    <h1>Reset Your Password</h1>
  </div>
  <div class="clearfix prepend_20">
    <div class="grid_17 prefix_3 left">
      {if isset($error_msgs)}
        <div class="error">
          {$error_msg}
        </div>
      {/if}
      {if isset($success_msg)}
        <div class="success">
          {$success_msg}
        </div>
      {/if}
    </div>
  </div>
  <div class="clearfix append_20">
    <form name="form1" method="post" action="" class="login">
      <div class="clearfix">
        <div class="grid_4 prefix_5 right">
          <label for="email">
            Email:
          </label>
        </div>
        <div class="grid_10 left">
          <input name="email" type="text" id="email">
        </div>
      </div>
      <div class="clearfix">
        <div class="grid_10 prefix_9 left">
          <input type="submit" id="login-save" name="Submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Send Reset">
        </div>
      </div>
    </form>
    <div class="center prepend_20 append_20">
      <a href="register.php">Register</a> |
      <a href="login.php">Log In</a>
    </div>
  </div>
</div>

{include file="_footer.tpl"}
