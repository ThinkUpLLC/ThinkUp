{include file="_header.tpl"}
{include file="_statusbar.tpl"}

<div class="container_24 thinkup-canvas round-all">
  <div class="prepend_20">
    <h1>Reset Password</h1>
  </div>
  <div class="clearfix prepend_20">
    <div class="grid_17 prefix_3 left">
      {if isset($errormsg)}
        <div class="error">
          {$errormsg}
        </div>
      {/if}
    </div>
  </div>
  <form method="post" action="" class="reset_password">
    <div class="clearfix">
      <div class="grid_4 prefix_5 right">
        <label for="password">
          New password:
        </label>
      </div>
      <div class="grid_10 left">
        <input type="password" name="password" id="password">
      </div>
    </div>
    <div class="clearfix">
      <div class="grid_4 prefix_5 right">
        <label for="password_confirm">
          Retype password:
        </label>
      </div>
      <div class="grid_10 left">
        <input type="password" name="password_confirm" id="password_confirm">
      </div>
    </div>
    <div class="clearfix">
      <div class="grid_10 prefix_9 left">
        <input type="submit" id="login-save" name="Submit" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Submit">
      </div>
    </div>
  </form>
</div>

{include file="_footer.tpl"}
