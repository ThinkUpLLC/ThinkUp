<h1>Settings</h1>
<p>
{if isset($msg)} <div class="msg"> {$msg} </div> {/if}
</p>
<h2>Change Password</h2>
<form action="settings.php" method="post" name="form3" id="form3">
<p>Old Password
<input name="oldpwd" type="password" id="oldpwd">
</p>
<p>New Password:
<input name="newpwd" type="password" id="newpwd">
</p>
<p>
<input name="Submit" type="submit" id="Submit" value="Change">
</p>
</form>
