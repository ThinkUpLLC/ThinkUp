{include file="_header.tpl" body_classes="settings account menu-off"}
{include file="_navigation.tpl"}

  <div class="container">
    <header>
      <h1>Reset your password</h1>
      <h2>You'll get a password reset email.</h2>
    </header>

    <form name="forgot-form" action="" method="POST" role="form" class="form-horizontal" id="form-forgot-password">
      <fieldset class="fieldset-no-header">
        <div class="form-group">
          <label class="control-label" for="email">Email</label>
          <input type="email" name="email" id="email" class="form-control" required
             placeholder="you@example.com" data-validation-required-message="A valid email address is required.">
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
