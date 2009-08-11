<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: session/login.php"); }

// set up
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").":".$INCLUDE_PATH);
require_once($root_path . "init.php");


$db = new Database();
$conn = $db->getConnection();

$od = new OwnerDAO();
$owner = $od->getByEmail($_SESSION['user']);

$id = new InstanceDAO();

if ( isset($_REQUEST['u']) && $id->isUserConfigured($_REQUEST['u']) ){
	$username = $_REQUEST['u'];
	$oid = new OwnerInstanceDAO();
	if ( !$oid->doesOwnerHaveAccess($owner->id, $username) ) {
		echo 'Insufficient privileges. <a href="/">Back</a>.';
		$db->closeConnection($conn);
		die;
	} else {
		$i = $id->getByUsername($username);	
	}
} else {
	$db->closeConnection($conn);
	die;
}

if (!isset($_REQUEST['d']) ) 
	$_REQUEST['d'] = "all-tweets";

$s = new SmartyTwitalytic();

if(!$s->is_cached('inline.view.tpl', $i->twitter_username."-".$_SESSION['user']."-".$_REQUEST['d'])) {

	$cfg = new Config($i->twitter_username, $i->twitter_user_id);
	$s->assign('cfg', $cfg);
	$u = new Utils();

	// instantiate data access objects
	$ud = new UserDAO();
	$td = new TweetDAO();
	$fd = new FollowDAO();

	// pass data to smarty
	switch ($_REQUEST['d']) {
		case "tweets-all":
			$s->assign('all_tweets', $td->getAllTweets($cfg->twitter_user_id, 15) );
			break;
		case "tweets-mostreplies":
			$s->assign('most_replied_to_tweets', $td->getMostRepliedToTweets($cfg->twitter_user_id, 15));
			break;
		case "tweets-convo":
			$s->assign('author_replies', $td->getTweetsAuthorHasRepliedTo($cfg->twitter_user_id, 15));
			break;
		case "mentions-all":
			$s->assign('all_replies', $td->getAllReplies($cfg->twitter_username, 15) );
			break;
		case "mentions-orphan":
			$s->assign('orphan_replies', $td->getOrphanReplies($cfg->twitter_username, 5));
			break;
		case "mentions-standalone":
			$s->assign('standalone_replies', $td->getStandaloneReplies($cfg->twitter_username, 15));
			break;
		case "followers-mostfollowed":
			$s->assign('most_followed_followers', $fd->getMostFollowedFollowers($cfg->twitter_user_id, 15));
			break;
		case "followers-leastlikely":
			$s->assign('least_likely_followers', $fd->getLeastLikelyFollowers($cfg->twitter_user_id, 15));
			break;
		case "followers-former":
			$s->assign('former_followers', $fd->getFormerFollowers($cfg->twitter_user_id, 15));
			break;
		case "followers-earliest":
			$s->assign('earliest_joiner_followers', $fd->getEarliestJoinerFollowers($cfg->twitter_user_id, 15));
			break;
		case "friends-mostactive":
			$s->assign('most_active_friends', $fd->getMostActiveFollowees($cfg->twitter_user_id, 15));
			break;
		case "friends-leastactive":
			$s->assign('least_active_friends', $fd->getLeastActiveFollowees($cfg->twitter_user_id, 15));
			break;
		case "friends-mostfollowed":
			$s->assign('most_followed_friends', $fd->getMostFollowedFollowees($cfg->twitter_user_id, 15));
			break;
		case "friends-former":
			$s->assign('former_friends', $fd->getFormerFollowees($cfg->twitter_user_id, 15));
			break;
		case "friends-notmutual":
			$s->assign('not_mutual_friends', $fd->getFriendsNotFollowingBack($cfg->twitter_user_id));
			break;
	}
}

# clean up
$db->closeConnection($conn);	

$s->display('inline.view.tpl', $i->twitter_username."-".$_SESSION['user']."-".$_REQUEST['d']);




?>