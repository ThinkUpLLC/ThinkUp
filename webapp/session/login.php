<?php 
// set up
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").":".$INCLUDE_PATH);
require_once("init.php");

include 'dbc.php';

$user_email = mysql_real_escape_string($_POST['email']);

if ($_POST['Submit']=='Login')
{
$md5pass = md5($_POST['pwd']);
$sql = "SELECT id,user_email, user_name FROM owners WHERE 
            user_email = '$user_email' AND 
            user_pwd = '$md5pass' AND user_activated='1'"; 
			
$result = mysql_query($sql) or die (mysql_error()); 
$num = mysql_num_rows($result);

    if ( $num != 0 ) { 

        // A matching row was found - the user is authenticated. 
       session_start(); 
	   list($user_id,$user_email,$user_name) = mysql_fetch_row($result);
		// this sets variables in the session 
		$_SESSION['user']= $user_email;  
		
			
		if (isset($_GET['ret']) && !empty($_GET['ret']))
		{
		header("Location: $_GET[ret]");
		} else
		{
		//header("Location: myaccount.php");
		header("Location: ".$TWITALYTIC_CFG['site_root_path']);
		}
		//echo "Logged in...";
		exit();
    } 

header("Location: login.php?msg=Invalid Login");
//echo "Error:";
exit();		
}

?>

<link href="styles.css" rel="stylesheet" type="text/css">

<?php if (isset($_GET['msg'])) { echo "<div class=\"msg\"> $_GET[msg] </div>"; } ?>


<p>&nbsp;</p><table width="40%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr> 
    <td bgcolor="#d5e8f9" class="mnuheader" >
<div align="center"><font size="5"><strong>Login 
        Members</strong></font></div></td>
  </tr>
  <tr> 
    <td bgcolor="#e5ecf9" class="mnubody"><form name="form1" method="post" action="">
        <p>&nbsp;</p>
        <p align="center">Your Email 
          <input name="email" type="text" id="email">
        </p>
        <p align="center"> Password: 
          <input name="pwd" type="password" id="pwd">
        </p>
        <p align="center"> 
          <input type="submit" name="Submit" value="Login">
        </p>
        
      </form></td>
  </tr>
</table>
	<br /><br />
<center><p>Not currently open for new registrations. But nerds can <a href="http://github.com/ginatrapani/twitalytic/tree/master">set up their own Twitalytic instance</a>.<br /><br />
Back to <a href="../public.php">the public timeline</a>.</center>