{include file="_header.tpl"}
{include file="_statusbar.tpl"}
<div class="container_24 thinkup-canvas round-all">
  <div class="prepend_20">
    <h1>Register</h1>
  </div>
  <div class="clearfix prepend_20">
    <div class="grid_17 prefix_3 left">
{include file="_usermessage.tpl"}
    </div>
  </div>
  {if !$closed and !$successmsg}
    <form name="form1" method="post" action="register.php" class="login append_20">
      <div class="clearfix">
        <div class="grid_4 prefix_5 right">
          <label for="full_name">
            Name:
          </label>
        </div>
        <div class="grid_10 left">
          <input name="full_name" type="text" id="full_name"{if  isset($name)} value="{$name}"{/if}>
          <small>
            <br>
            Example: Angelina Jolie
          </small>
        </div>
      </div>
      <div class="clearfix">
        <div class="grid_4 prefix_5 right">
          <label for="email">
            Email:
          </label>
        </div>
        <div class="grid_10 left">
          <input name="email" type="text" id="email"{if  isset($mail)} value="{$mail}"{/if}>
          <small>
            <br>
            Example: angie@example.com
          </small>
        </div>
      </div>
      <div class="clearfix">
        <div class="grid_4 prefix_5 right">
          <label for="pass1">
            Password:
          </label>
        </div>
        <div class="grid_10 left">
          <input name="pass1" type="password" id="pass1">
          <small>
            <br>
            At least 5 characters
          </small>
        </div>
      </div>
      <div class="clearfix">
        <div class="grid_6 prefix_3 right">
          <label for="pass2">
            Retype password:
          </label>
        </div>
        <div class="grid_10 left">
          <input name="pass2" type="password" id="pass2">
          <small>
            <br>
          </small>
        </div>
      </div>
      <div class="clearfix">
        <div class="grid_6 prefix_3 right">
          <label for="user_code">
            Prove you&rsquo;re human:
          </label>
        </div>
        <div class="grid_10 left">
          <div class="captcha">
            {$captcha}
          </div>
        </div>
      </div>
      <div class="clearfix">
        <div class="grid_10 prefix_9 left">
          <input type="submit" name="Submit" id="login-save" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Register">
        </div>
      </div>
    </form>
  {/if}
  <div class="center prepend_20 append_20">
  {if !$successmsg}
    <a href="login.php">Log In</a> |
    <a href="forgot.php">Forgot password</a>
  {/if}
  </div>
</div>

{include file="_footer.tpl"}