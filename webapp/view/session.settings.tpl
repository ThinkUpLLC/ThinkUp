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
  <p><label for="oldpwd">Old Password:</label> <input type="password" name="oldpwd" id="oldpwd"></p>
  <p><label for="newpwd">New Password:</label> <input type="password" name="newpwd" id="newpwd"></p>
  <p><input type="submit" name="Submit" id="Submit" value="Change"></p>
</form>
