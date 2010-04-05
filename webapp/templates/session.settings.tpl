<h1>Settings</h1>

<p>
  {if isset($msg)}
    <div class="msg">
      {$msg}
    </div>
  {/if}
</p>

<h2>Change Password</h2>

<form action="settings.php" method="post" name="form3" id="form3">
  <p>Old Password <input type="password" name="oldpwd" id="oldpwd"></p>
  <p>New Password: <input type="password" name="newpwd" id="newpwd"></p>
  <p><input type="submit" name="Submit" id="Submit" value="Change"></p>
</form>
