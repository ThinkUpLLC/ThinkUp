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

$db = new Database($THINKTANK_CFG);
$conn = $db->getConnection();

$ud = new UserDAO($db);
$fd = new FollowDAO($db);
$id = new InstanceDAO($db);
$td = new TweetDAO($db);

if ( isset($_REQUEST['u']) && $ud->isUserInDBByName($_REQUEST['u']) && isset($_REQUEST['i']) ){
	$user = $ud->getUserByName($_REQUEST['u']);
	$i = $id->getByUsername($_REQUEST['i']);

	if ( isset($i)) {
		$cfg = new Config($i->twitter_username, $i->twitter_user_id);

		$s = new SmartyThinkTank();
		if(!$s->is_cached('user.index.tpl', $i->twitter_username."-".$user->user_name)) {

			$s->assign('profile', $user);
			$s->assign('user_statuses',  $td->getAllTweets($user->user_id, 20));
			$s->assign('sources', $td->getStatusSources($user->user_id));
			$s->assign('cfg', $cfg);
			$s->assign('instance', $i);
			$exchanges =  $td->getExchangesBetweenUsers($cfg->twitter_user_id, $user->user_id);
			$s->assign('exchanges', $exchanges);
			$s->assign('total_exchanges', count($exchanges));
			$mutual_friends = $fd->getMutualFriends($user->user_id, $i->twitter_user_id);
			$s->assign('mutual_friends', $mutual_friends);
			$s->assign('total_mutual_friends', count($mutual_friends) );
		}
		$db->closeConnection($conn);

		$s->display('user.index.tpl', $i->twitter_username."-".$user->user_name);
	}
} else {
	echo 'This user is not in the system.<br /><a href="'. $THINKTANK_CFG['site_root_path'] .'">back home</a>';
}
?>
