<?php
// set up
chdir("..");
require_once ('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("init.php");

session_start();
$session = new Session();
if (!$session->isLoggedIn()) {
	header("Location: ../index.php");
}

$db = new Database($THINKTANK_CFG);
$conn = $db->getConnection();

$od = new OwnerDAO($db);


if (isset($_POST['changepass']) && $_POST['changepass'] == 'Change Password') {
	$originalpass = $od->getPass($_SESSION['user']);
	$origpass = $originalpass['pwd'];
	if (!$session->pwdCheck($_POST['oldpass'], $origpass)) {
		$errormsg = "Old password does not match or empty.";
	} elseif ($_POST['pass1'] != $_POST['pass2']) {
		$errormsg = "New passwords did not match. Your password has not been changed.";
	} elseif (strlen($_POST['pass1']) < 5) {
		$errormsg = "New password must be at least 5 characters. Your password has not been changed.";
	} else {
		$cryptpass = $session->pwdcrypt($_POST['pass1']);
		$od->updatePassword($_SESSION['user'], $cryptpass);
		$successmsg = "Your password has been updated.";

	}
}

$s = new SmartyThinkTank();
$s->caching = 0;

$cfg = new Config();
$id = new InstanceDAO($db);
$od = new OwnerDAO($db);

$owner = $od->getByEmail($_SESSION['user']);

$s->assign('cfg', $cfg);
$s->assign('owner', $owner);

if ( $owner->is_admin ) {
	$owners = $od->getAllOwners();
	foreach ($owners as $o) {
		$instances = $id->getByOwner($o, true);
		$o->setInstances($instances);
	}
	$s->assign('owners', $owners);
}

/* Start plugin-specific configuration handling */
$webapp = new Webapp();

//Include webapp plugin files
$plugin_files = Utils::getPlugins('plugins');
foreach ($plugin_files as $pf) {
	require_once 'plugins/'.$pf.'/'.$pf.'.php';
}

if (isset($_GET['p'])) {
	$webapp->configuration($_GET['p']);
	array_push( $s->template_dir, 'plugins/'.$_GET['p']);
	$s->assign('body', $_GET['p'].'.account.index.tpl');
}

$s-> assign('config_menu', $webapp->getConfigMenu());
/* End plugin-specific configuration handling */


# clean up
$db->closeConnection($conn);


if (isset($errormsg)) {
	$s->assign('errormsg', $errormsg);
}
if (isset($successmsg)) {
	$s->assign('successmsg', $successmsg);
}

$s->display('account.index.tpl');
?>
