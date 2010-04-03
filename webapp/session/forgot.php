<?php
session_start();

// set up
chdir("..");


require_once ("common/init.php");

require_once ("class.Mailer.php");

$session = new Session();
if ($session->isLoggedIn()) {
	header("Location: ../index.php");
}

$od = new OwnerDAO($db);

$s = new SmartyThinkTank();
$s->caching=false;

if ($_POST['Submit'] == 'Send') {
	if ($od->doesOwnerExist($_POST['email'])) {
		$newpwd = rand(10000, 99999);
		$server = $_SERVER['HTTP_HOST'];
		$cryptpass = $session->pwdcrypt($newpwd);
		$od->updatePassword($_POST['email'], $cryptpass);

		$es = new SmartyThinkTank();
		$es->caching=false;

		$es->assign('apptitle', $THINKTANK_CFG['app_title'] );
		$es->assign('email', $_POST['email']);
		$es->assign('newpwd', $newpwd);
		$es->assign('server', $server );
		$es->assign('site_root_path', $THINKTANK_CFG['site_root_path'] );
		$message = $es->fetch('_email.forgotpassword.tpl');

		Mailer::mail($_POST['email'], "The ".$THINKTANK_CFG['app_title'] ." Account Details You Requested", $message);

		$successmsg = "Password recovery information has been sent to your email address. <a href=\"login.php\">Sign in.</a>";
	} else
	$errormsg = "Account does not exist";
}

if (isset($errormsg)) {
	$s->assign('errormsg', $errormsg);
} elseif (isset($successmsg)) {
	$s->assign('successmsg', $successmsg);
}

$db->closeConnection($conn);

$cfg = new Config();
$s->assign('cfg', $cfg);
$s->display('session.forgot.tpl');
?>
