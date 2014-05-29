{include file="_header.tpl" body_classes="settings account menu-off"}
{include file="_navigation.tpl"}
  <div class="container">


{include file="_usermessage.tpl-v2" enable_bootstrap=1}

{if !$closed and !$has_been_registered}

    <header>
      <h1>Register</h1>
      <h2>Create your ThinkUp account.</h2>
    </header>

    <form name="form1" method="POST" id="registerform"
        action="register.php"
         class="login form-horizontal" >
      <fieldset class="fieldset-no-header">
        <div class="form-group">
          <label class="control-label" for="full_name">Name</label>
          <input type="text" name="full_name" class="form-control" id="full_name" required
            {if  isset($name)} value="{$name|filter_xss}"{/if}
            data-validation-required-message="Name can't be blank.">
        </div>
        <div class="form-group">
          <label class="control-label" for="email">Email</label>
          <input type="email" name="email" class="form-control" id="email" required
          {if isset($mail)}value="{$mail|filter_xss}"{/if} placeholder="you@example.com">
          {include file="_usermessage-v2.tpl" field="email" inline="true"}
        </div>
        <div class="form-group">
          <label class="control-label" for="pass1">Password</label>
          <input type="password" class="form-control" id="pass1" name="pass1" value="" required
            placeholder="********" {literal}pattern="^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*).{8,}$"{/literal}
            class="form-control password"
            data-validation-required-message="You'll need a enter a password of at least 8 characters."
            data-validation-pattern-message="Must be at least 8 characters, with both numbers &amp; letters.">
            {include file="_usermessage-v2.tpl" field="password" inline="true"}
        </div>
        <div class="form-group">
          <label class="control-label" for="pass2">Confirm Password</label>
          <input type="password" class="form-control" id="pass2" name="pass2" value="" required
            placeholder="********" {literal}pattern="^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*).{8,}$"{/literal}
            class="form-control password"
            data-validation-required-message="You'll need a enter a password of at least 8 characters."
            data-validation-match-match="pass1"
            data-validation-pattern-message="Must be at least 8 characters, with both numbers &amp; letters.">
        </div>
        <div class="form-group">
          {$captcha}
          {include file="_usermessage-v2.tpl" field="captcha" inline="true"}
        </div>

      </fieldset>

      <input type="submit" name="Submit" id="login-save" value="Register" class="btn btn-circle btn-submit">
{else}

    <header>
      <h1>Sorry!</h1>
      <h2>Registration is closed for {$app_title}.</h2>
    </header>


{/if}

      <p class="form-note">
        <a href="login.php">Back to login</a>
        <a href="forgot.php">Forgot your password?</a>
      </p>
    </form>
  </div>
{include file="_footer.tpl"}
