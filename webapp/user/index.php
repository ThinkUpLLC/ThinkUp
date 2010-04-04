<?php
session_start();
if (!isset($_SESSION['user']))  {
	header("Location: ../session/login.php");
}

// set up
chdir("..");


require_once("common/init.php");

$od = new OwnerDAO($db);
$ud = new UserDAO($db);
$fd = new FollowDAO($db);
$id = new InstanceDAO($db);
$pd = new PostDAO($db);
$s = new SmartyThinkTank();

if ( isset($_REQUEST['u']) && $ud->isUserInDBByName($_REQUEST['u']) && isset($_REQUEST['i']) ){
	$user = $ud->getUserByName($_REQUEST['u']);
	$owner = $od->getByEmail($_SESSION['user']);

    // let's use the session variable to guarantee a value rather than the $i POST value, which can be blank	
	$i = $id->getByUsername($_SESSION['network_username']);
	//$i = $id->getByUsername($_SESSION['i']);
	
	if ( isset($i)) {
		$cfg = new Config($i->network_username, $i->network_user_id);
		if(!$s->is_cached('user.index.tpl', $i->network_username."-".$user->username)) {

            $s->assign('instances', $id->getByOwner($owner));

			$s->assign('profile', $user);
			$s->assign('user_statuses',  $pd->getAllPosts($user->user_id, 20));
			$s->assign('sources', $pd->getStatusSources($user->user_id));
			$s->assign('cfg', $cfg);
			$s->assign('instance', $i);
			$s->assign('i', $i); // HATE TO DO THIS BUT SOME TEMPLATES LOOKING FOR $i AND NOT $instance
			
			$exchanges =  $pd->getExchangesBetweenUsers($cfg->twitter_user_id, $user->user_id);
			$s->assign('exchanges', $exchanges);
			$s->assign('total_exchanges', count($exchanges));
			
			$mutual_friends = $fd->getMutualFriends($user->user_id, $i->network_user_id);
			$s->assign('mutual_friends', $mutual_friends);
			$s->assign('total_mutual_friends', count($mutual_friends) );
		}
		$db->closeConnection($conn);

		$s->display('index.user.tpl', $i->network_username."-".$user->username);
	}
} else {
	echo 'This user is not in the system.<br /><a href="'. $THINKTANK_CFG['site_root_path'] .'">back home</a>';
}
?>
