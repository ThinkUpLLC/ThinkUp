<h1>Settings</h1>

<p>
{include file="_usermessage.tpl"}
</p>

<h2>Change Password</h2>

<form action="settings.php" method="post" name="form3" id="form3">
  <p><label for="oldpwd">Old Password:</label> <input type="password" name="oldpwd" id="oldpwd" class="form-control"></p>
  <p><label for="newpwd">New Password:</label> <input type="password" name="newpwd" id="newpwd" class="form-control"></p>
  <p><input type="submit" name="Submit" id="Submit" value="Change"></p>
</form>
