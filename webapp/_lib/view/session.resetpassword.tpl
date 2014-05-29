{include file="_header.tpl" body_classes="settings account menu-off"}
{include file="_navigation.tpl"}

  <div class="container">
    <header>
      <h1>Reset your password</h1>
      <h2></h2>
    </header>

    <form name="forgot-form" action="" method="POST" role="form" class="form-horizontal" id="form-forgot-password">
      <fieldset class="fieldset-no-header">

        <div class="form-group">
          <label class="control-label" for="password">New Password</label>
          <input type="password" class="form-control" id="password" name="password" value=""
          placeholder="********">
        </div>

        <div class="form-group">
          <label class="control-label" for="confirm_password">Confirms</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" value=""
          placeholder="********">
        </div>

      </fieldset>

      <input type="submit" name="Submit" value="Send" class="btn btn-circle btn-submit">

      <p class="form-note">
        <a href="login.php">Back to login</a>
        {if $is_registration_open}&nbsp; <a href="register.php">Register</a>{else}{/if}
      </p>

    </form>
  </div>

{include file="_footer.tpl"}
