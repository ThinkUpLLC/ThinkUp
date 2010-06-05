<?php
session_start();

if (!isset($_SESSION['user'])) {
	header("Location: session/login.php");
}

require_once 'init.php';

$od = new OwnerDAO($db);
$owner = $od->getByEmail($_SESSION['user']);
$id = DAOFactory::getDAO('InstanceDAO');

if (isset($_REQUEST['u']) && $id->isUserConfigured($_REQUEST['u'])) {
	$username = $_REQUEST['u'];
	$oid = new OwnerInstanceDAO($db);
	if (!$oid->doesOwnerHaveAccess($owner, $username)) {
		echo 'Insufficient privileges. <a href="/">Back</a>.';
		$db->closeConnection($conn);
		die;
	} else {
		$i = $id->getByUsernameOnNetwork($username, $_REQUEST['n']);
	}
} else {
	$db->closeConnection($conn);
	die;
}

if (!isset($_REQUEST['d'])) {
	$_REQUEST['d'] = "all-tweets";
}

$webapp->setActivePlugin($i->network);

$s = new SmartyThinkTank();
// instantiate data access objects
$ud = new UserDAO($db);
$pd = new PostDAO($db);
$fd = DAOFactory::getDAO('FollowDAO');
$ld = new LinkDAO($db);

// pass data to smarty
$view_template = $webapp->loadRequestedTabData($_GET["d"], $i);

if (!$s->is_cached($view_template, $i->network_username."-".$_SESSION['user']."-".$_REQUEST['d'])) {
	$s->assign('site_root_path', $config->getValue('site_root_path'));
	$s->assign('i', $i);
	$u = new Utils();
	$s->assign('display', $_REQUEST['d']);
}

# clean up
$db->closeConnection($conn);

//echo "TEMPLATE: ".$view_template;

$s->display($view_template, $i->network_username."-".$_SESSION['user']."-".$_REQUEST['d']);
?>