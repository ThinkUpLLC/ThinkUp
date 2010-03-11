<?php
session_start();
if (!isset($_SESSION['user']))  {
	header("Location: ../session/login.php");
}

// set up
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");

$ud = new UserDAO($db);
$fd = new FollowDAO($db);
$id = new InstanceDAO($db);
$pd = new PostDAO($db);
$s = new SmartyThinkTank();

if ( isset($_REQUEST['u']) && $ud->isUserInDBByName($_REQUEST['u']) && isset($_REQUEST['i']) ){
	$user = $ud->getUserByName($_REQUEST['u']);
	$i = $id->getByUsername($_REQUEST['i']);

	if ( isset($i)) {
		$cfg = new Config($i->network_username, $i->network_user_id);
		if(!$s->is_cached('user.index.tpl', $i->network_username."-".$user->user_name)) {

			$s->assign('profile', $user);
			$s->assign('user_statuses',  $pd->getAllPosts($user->user_id, 20));
			$s->assign('sources', $pd->getStatusSources($user->user_id));
			$s->assign('cfg', $cfg);
			$s->assign('instance', $i);
			$exchanges =  $pd->getExchangesBetweenUsers($cfg->twitter_user_id, $user->user_id);
			$s->assign('exchanges', $exchanges);
			$s->assign('total_exchanges', count($exchanges));
			$mutual_friends = $fd->getMutualFriends($user->user_id, $i->network_user_id);
			$s->assign('mutual_friends', $mutual_friends);
			$s->assign('total_mutual_friends', count($mutual_friends) );
		}
		$db->closeConnection($conn);

		$s->display('user.index.tpl', $i->network_username."-".$user->user_name);
	}
} else {
	echo 'This user is not in the system.<br /><a href="'. $THINKTANK_CFG['site_root_path'] .'">back home</a>';
}
?>
