{include file="_header.tpl" body_classes="settings account menu-off"}
{include file="_navigation.tpl"}

  <div class="container">
    <header class="container-header">
      <h1>Reset your password</h1>
      <h2></h2>
    </header>

    <form name="forgot-form" action="" method="POST" role="form" class="form" id="form-forgot-password">
      <fieldset class="fieldset-no-header">

        <div class="form-group">
          <label class="control-label" for="password">New Password</label>
          <input type="password" class="form-control" id="password" name="password" value=""
          placeholder="********">
        </div>

        <div class="form-group">
          <label class="control-label" for="password_confirm">Confirms</label>
          <input type="password" class="form-control" id="password_confirm" name="password_confirm" value=""
          placeholder="********">
        </div>

      </fieldset>

      <input type="submit" name="Submit" value="Send" class="btn btn-submit">

      <p class="form-note">
        <a href="{$site_root_path}session/login.php">Back to login</a>
        {if $is_registration_open}&nbsp; <a href="{$site_root_path}session/register.php">Register</a>{else}{/if}
      </p>

    </form>
  </div>

{include file="_footer.tpl"}
