{include file="_header.tpl" body_classes="settings account menu-off"}
{include file="_navigation.tpl"}
  <div class="container">
    <header>
      <h1>Welcome!</h1>
      <h2>Please log in.</h2>
    </header>

    <form action="index.php{if isset($usr) && isset($smarty.get.code)}?usr={$usr}&code={$smarty.get.code}{/if}" method="POST" class="form-horizontal" id="form-signin">
      <fieldset class="fieldset-no-header">
        <div class="form-group">
          <label class="control-label" for="email">Email</label>
          <input type="email" name="email" class="form-control" id="email" autofocus="autofocus"
          {if isset($email)}value="{$email|filter_xss}"{/if} placeholder="you@example.com" required>
        </div>
        <div class="form-group">
          <label class="control-label" for="pwd">Password</label>
          <input type="password" class="form-control" value="" placeholder="********" id="pwd" name="pwd"
           required>
          <input type="hidden" name="csrf_token" value="{$csrf_token}" />
        </div>
        {if isset($redirect)}
        <input type="hidden" name="redirect" value="{$redirect}">
        {/if}
      </fieldset>

      <input type="Submit" name="Submit" value="Log In" class="btn btn-circle btn-submit">

      <p class="form-note">
        <a href="forgot.php">Forgot your password?</a>
        {if $is_registration_open}&nbsp; <a href="{$site_root_path}session/register.php">Register</a>{else}{/if}
      </p>
    </form>
  </div>
{include file="_footer.tpl"}
