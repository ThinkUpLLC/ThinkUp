<?php 
session_start();

// set up
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");
 
if(!$THINKTANK_CFG['is_registration_open']){
  echo 'So sorry, but registration on this instance of ThinkTank is closed. <br /><br /><a href="http://github.com/ginatrapani/thinktank/tree/master">Install thinktank on your own server</a> or go back to <a href="'.$THINKTANK_CFG['site_root_path'].'public.php">the public timeline</a>.';
  die();
}
else{
  $s = new SmartyThinkTank();
  include ('dbc.php'); 
  $captcha = new captcha($THINKTANK_CFG);
  if ($_POST['Submit'] == 'Register'){
    if (strlen($_POST['email']) < 5){
      die ("Incorrect email. Please enter valid email address..");
    }
    if (strcmp($_POST['pass1'],$_POST['pass2']) || empty($_POST['pass1']) ){ 
      //die ("Password does not match");
      die("ERROR: Password does not match or empty..");
    }
    elseif(!$captcha->check()){ 
    }
    else{ 
      $rs_duplicates = mysql_query("select id from ".$THINKTANK_CFG['table_prefix']."owners where user_email='$_POST[email]'");
      $duplicates = mysql_num_rows($rs_duplicates);	
      if ($duplicates > 0){	
        //die ("ERROR: User account already exists.");
        header("Location: register.php?msg=ERROR: User account already exists..");
        exit();
      }
      else{
        $md5pass = md5($_POST['pass2']);
        $activ_code = rand(1000,9999);
        $server = $_SERVER['HTTP_HOST'];
        $host = ereg_replace('www.','',$server);
        $sql = "INSERT INTO ".$THINKTANK_CFG['table_prefix'];
        $sql .= "owners (`user_email`,`user_pwd`,`country`,`joined`,`activation_code`,`full_name`)";
        $sql .= "VALUES ('$_POST[email]','$md5pass','$_POST[country]',now(),'$activ_code','$_POST[full_name]')";
        mysql_query($sql) or die(mysql_error());
        $message = "Thank you for registering an account with ".$THINKTANK_CFG['app_title'];
        $message .= ". Click on the link below to activate your account...\n\n";
        $message .= "http://$server/".$THINKTANK_CFG['site_root_path'];
        $message .= "session/activate.php?usr=$_POST[email]&code=$activ_code \n\n";
        $message .= "_____________________________________________";
        $message .= "Thank you. This is an automated response. PLEASE DO NOT REPLY.";
        $mailheader = "From: \"Auto-Response\" <notifications@$host>\r\n";
        $mailheader .= "X-Mailer: PHP/" . phpversion();
        mail($_POST['email'] , "Login Activation", $message, $mailheader);
        unset($_SESSION['ckey']);
        echo("Registration Successful! An activation code has been sent to your email address with an activation link...");
        exit;
      }
    }
    $s->assign('name', $_POST["full_name"]);
    $s->assign('mail', $_POST["email"]);
  }
  $challenge = $captcha->generate($msg);
  $s->assign('captcha', $challenge);
  if(isset($_GET['msg'])){ 
    $s->assign('msg', $_GET[msg] );
    $s->display('session.register.tpl', sha1($_GET['msg']));
  }
  else{
   $s->display('session.register.tpl');
  }
}
?>
