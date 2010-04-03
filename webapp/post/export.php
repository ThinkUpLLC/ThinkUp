<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: /session/login.php"); }

// set up
chdir("..");


require_once("common/init.php");

$od = new OwnerDAO($db);
$owner = $od->getByEmail($_SESSION['user']);

$pd = new PostDAO($db);
$id = new InstanceDAO($db);

if ( isset($_REQUEST['u']) && $id->isUserConfigured($_REQUEST['u']) ){
	$username = $_REQUEST['u'];
	$oid = new OwnerInstanceDAO($db);
	if ( !$oid->doesOwnerHaveAccess($owner, $username) ) {
		echo 'Insufficient privileges. <a href="/">Back</a>.';
		die;
	} else {
		$tweets = $pd->getAllPostsByUsername($username);	
	}
} else {
	echo 'No access';
	$db->closeConnection($conn);
	die;
}

$db->closeConnection($conn);

$s = new SmartyThinkTank();
$s->assign('tweets', $tweets);
$s->display('post.export.tpl', $username);


?>
