+<?php 
session_start();
if (!isset($_SESSION['user']))
header("Location: login.php");
}

include ('dbc.php'); 

if ($_POST['Submit']=='Change'){
  $rsPwd = mysql_query("select user_pwd from owners where user_email='$_SESSION[user]'") or die(mysql_error());
  list ($oldpwd) = mysql_fetch_row($rsPwd);

  if ($oldpwd == md5($_POST['oldpwd'])){
    $newpasswd = md5($_POST['newpwd']);
    mysql_query(
      "Update ".$THINKTANK_CFG['table_prefix']."owners
      SET user_pwd = '$newpasswd'       WHERE user_email = '$_SESSION[user]'") 
      or die(mysql_error());
  $msg = "Location: settings.php?msg=Password updated...";
  } 
  else{ 
    $msg = "Location: settings.php?msg=ERROR: Password does not match..."; 
  }
}
$s = new SmartyThinkTank();
if(isset($msg)){
  $s->assign('msg', $msg );
  $s->display('session.settings.tpl', sha1($msg));
}
else{
  $s->display('session.settings.tpl');
}
?> 
