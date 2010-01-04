<?php
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");
include 'dbc.php';
if ($_POST['Submit']=='Send')
{
$host = $_SERVER['HTTP_HOST'];
$rs_search = mysql_query("select user_email from ".$TWITALYTIC_CFG['table_prefix']."owners where user_email='$_POST[email]'");
$user_count = mysql_num_rows($rs_search);

if ($user_count != 0)
{
$newpwd = rand(1000,9999);
$host = $_SERVER['HTTP_HOST'];
$newmd5pwd = md5($newpwd);
mysql_query("UPDATE ".$TWITALYTIC_CFG['table_prefix']."owners set user_pwd='$newmd5pwd' where user_email='$_POST[email]'");
$message = 
"You have requested new login details from $host. Here are the login details...\n\n
User Name: $_POST[email] \n
Password: $newpwd \n
____________________________________________
*** LOGIN ***** \n
To Login: http://$host/login.php \n\n
_____________________________________________
Thank you. This is an automated response. PLEASE DO NOT REPLY.
";

	mail($_POST['email'], "New Login Details", $message,
    "From: \"Auto-Response\" <robot@$host>\r\n" .
     "X-Mailer: PHP/" . phpversion());
	 
die("Thank you. New Login details has been sent to your email address");
} else die("Account with given email does not exist");

}
?>
<h3>Forgot Password</h3>
<p>Please enter your email address and the new password will be sent.</p>
<table width="50%" border="0" cellpadding="1" cellspacing="0">
  <tr>
    <td> 
      <form name="form1" method="post" action="">
        <p><br>
          <strong>Email:</strong> 
          <input name="email" type="text" id="email">
          <input type="submit" name="Submit" value="Send">
        </p>
      </form></td>
  </tr>
</table>
<p>&nbsp;</p>

