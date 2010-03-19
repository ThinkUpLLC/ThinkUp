{include file="session._header.tpl" title="Sign In"}

<h1>Sign Into ThinkTank</h1>

<div class="content">
	{if isset($errormsg)}<div class="error"> {$errormsg} </div>{/if}
	{if isset($successmsg)}<div class="success"> {$successmsg} </div>{/if}

<form name="form1" method="post" action="">
<br />
<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0">
    <tr> 
        <td align="right" width="50%">Email:</td><td><input name="email" type="text" id="email"></td>
	</tr>
	<tr>
        <td align="right">Password:</td><td><input name="pwd" type="password" id="pwd"></td>
	</tr>
	<tr>
		<td></td>
        <td align="left"><input type="submit" name="Submit" value="Login">
	</tr>
	<tr>
		<td></td>
        <td><a href="register.php">Register</a> | <a href="forgot.php">Forgot</a></td>
    </tr>
</table>
</form>

</div>


<p><a href="{$cfg->site_root_path}">Go to the public timeline</a></p>
<p>Set up your own <a href="http://thinktankapp.com">ThinkTank</a></p>
<p>It is nice to be nice</p>
	</center>
</body>
</html>