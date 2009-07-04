<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: /session/login.php"); }

// set up
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").":".$INCLUDE_PATH);
require_once("init.php");

$db = new Database();
$conn = $db->getConnection();

$ud = new UserDAO();
$id = new InstanceDAO();
$td = new TweetDAO();

if ( isset($_REQUEST['u']) && $ud->isUserInDBByName($_REQUEST['u']) && isset($_REQUEST['i']) ){
	$user = $ud->getUserByName($_REQUEST['u']);
	$i = $id->getByUsername($_REQUEST['i']);

	if ( isset($i)) {
		$cfg = new Config($i->twitter_username, $i->twitter_user_id);
		$user_statuses = $td->getAllTweets($user['user_id'], 20);
		
		$s = new SmartyTwitalytic();

		$s->assign('profile', $user);
		$s->assign('user_statuses', $user_statuses);
		$s->assign('cfg', $cfg);
		$s->assign('instance', $i);
		$s->assign('exchanges', $td->getExchangesBetweenUsers($cfg->twitter_user_id, $user['user_id']));
		
		$db->closeConnection($conn);	

		echo $s->fetch('user.index.tpl');
	}
} else {
	echo 'This user is not in the system.<br /><a href="'. $cfg->site_root_path .'">back home</a>';
}
?>