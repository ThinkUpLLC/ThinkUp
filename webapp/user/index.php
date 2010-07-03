<?php
session_start();
if (!isset($_SESSION['user']))  {
    header("Location: ../session/login.php");
}

// set up
chdir("..");

require_once 'init.php';

$od = DAOFactory::getDAO('OwnerDAO');
$ud = DAOFactory::getDAO('UserDAO');
$fd = DAOFactory::getDAO('FollowDAO');
$id = DAOFactory::getDAO('InstanceDAO');
$pd = DAOFactory::getDAO('PostDAO');
$s = new SmartyThinkTank();

if ( isset($_REQUEST['u']) && $ud->isUserInDBByName($_REQUEST['u']) && isset($_REQUEST['i']) ){
    $user = $ud->getUserByName($_REQUEST['u']);
    $owner = $od->getByEmail($_SESSION['user']);

    $i = $id->getByUsername($_REQUEST['i']);

    if ( isset($i)) {
        if(!$s->is_cached('user.index.tpl', $i->network_username."-".$user->username)) {

            $s->assign('instances', $id->getByOwner($owner));

            $s->assign('profile', $user);
            $s->assign('user_statuses',  $pd->getAllPosts($user->user_id, 20));
            $s->assign('sources', $pd->getStatusSources($user->user_id));
            $s->assign('site_root_path', $config->getValue('site_root_path'));
            $s->assign('instance', $i);

            $exchanges =  $pd->getExchangesBetweenUsers($i->network_user_id, $user->user_id);
            $s->assign('exchanges', $exchanges);
            $s->assign('total_exchanges', count($exchanges));

            $mutual_friends = $fd->getMutualFriends($user->user_id, $i->network_user_id, $i->network);
            $s->assign('mutual_friends', $mutual_friends);
            $s->assign('total_mutual_friends', count($mutual_friends) );
        }
        $s->display('user.index.tpl', $i->network_username."-".$user->username);
    }
} else {
    echo 'This user is not in the system.<br /><a href="'. $config->getValue('site_root_path') .'">back home</a>';
}