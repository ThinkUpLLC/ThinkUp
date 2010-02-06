<?php 
session_start();

// set up
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");
 
if ( !$THINKTANK_CFG['is_registration_open']) {
	echo 'So sorry, but registration on this instance of Twitalytic is closed. <br /><br /><a href="http://github.com/ginatrapani/twitalytic/tree/master">Install Twitalytic on your own server</a> or go back to <a href="'.$THINKTANK_CFG['site_root_path'].'public.php">the public timeline</a>.';
	die();
} else {


include ('dbc.php'); 

if ($_POST['Submit'] == 'Register')
{
   if (strlen($_POST['email']) < 5)
   {
    die ("Incorrect email. Please enter valid email address..");
    }
   if (strcmp($_POST['pass1'],$_POST['pass2']) || empty($_POST['pass1']) )
	{ 
	//die ("Password does not match");
	die("ERROR: Password does not match or empty..");

	}
	if (strcmp(md5($_POST['user_code']),$_SESSION['ckey']))
	{ 
			 die("Invalid code entered. Please enter the correct code as shown in the Image");
  		} 
	$rs_duplicates = mysql_query("select id from ".$THINKTANK_CFG['table_prefix']."owners where user_email='$_POST[email]'");
	$duplicates = mysql_num_rows($rs_duplicates);
	
	if ($duplicates > 0)
	{	
	//die ("ERROR: User account already exists.");
	header("Location: register.php?msg=ERROR: User account already exists..");
	exit();
	}
	
		
		
	
	$md5pass = md5($_POST['pass2']);
	$activ_code = rand(1000,9999);
	$server = $_SERVER['HTTP_HOST'];
	$host = ereg_replace('www.','',$server);
	mysql_query("INSERT INTO ".$THINKTANK_CFG['table_prefix']."owners
	              (`user_email`,`user_pwd`,`country`,`joined`,`activation_code`,`full_name`)
				  VALUES
				  ('$_POST[email]','$md5pass','$_POST[country]',now(),'$activ_code','$_POST[full_name]')") or die(mysql_error());
	
	$message = 
"Thank you for registering an account with ".$THINKTANK_CFG['app_title'].". Click on the link below to activate your account...\n\n
http://$server/".$THINKTANK_CFG['site_root_path']."session/activate.php?usr=$_POST[email]&code=$activ_code \n\n
_____________________________________________
Thank you. This is an automated response. PLEASE DO NOT REPLY.
";

	mail($_POST['email'] , "Login Activation", $message,
    "From: \"Auto-Response\" <notifications@$host>\r\n" .
     "X-Mailer: PHP/" . phpversion());
	unset($_SESSION['ckey']);
	echo("Registration Successful! An activation code has been sent to your email address with an activation link...");
	exit;
	}	
    $s = new SmartyThinkTank();
    if(isset($_GET['msg'])){ 
        $s->assign('msg', $_GET[msg] );
        $s->display('user.index.tpl', sha1($_GET['msg']));
    }
    else{
        $s->display('session.register.tpl');
    }
}
?>
