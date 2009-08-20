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
	if ( !$oid->doesOwnerHaveAccess($owner, $username) ) {
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
	$s->assign('i', $i);

	$u = new Utils();

	// instantiate data access objects
	$ud = new UserDAO();
	$td = new TweetDAO();
	$fd = new FollowDAO();


	$s->assign('display', $_REQUEST['d'] );

	// pass data to smarty
	switch ($_REQUEST['d']) {
		case "tweets-all":
			$s->assign('header', 'All Tweets' );
			$s->assign('all_tweets', $td->getAllTweets($cfg->twitter_user_id, 15) );
			break;
		case "tweets-mostreplies":
			$s->assign('header', 'Most Replied-To Tweets' );		
			$s->assign('most_replied_to_tweets', $td->getMostRepliedToTweets($cfg->twitter_user_id, 15));
			break;
		case "tweets-convo":
			$s->assign('header', 'Conversations' );		
			$s->assign('author_replies', $td->getTweetsAuthorHasRepliedTo($cfg->twitter_user_id, 15));
			break;
		case "mentions-all":
			$s->assign('header', 'All Mentions' );		
			$s->assign('all_replies', $td->getAllReplies($cfg->twitter_username, 15) );
			break;
		case "mentions-orphan":
			$s->assign('header', 'Orphan Mentions' );		
			$s->assign('all_tweets', $td->getAllTweets($cfg->twitter_user_id, 15) );
			$s->assign('orphan_replies', $td->getOrphanReplies($cfg->twitter_username, 5));
			break;
		case "mentions-standalone":
			$s->assign('header', 'Standalone Mentions' );		
			$s->assign('all_tweets', $td->getAllTweets($cfg->twitter_user_id, 15) );
			$s->assign('standalone_replies', $td->getStandaloneReplies($cfg->twitter_username, 15));
			break;
		case "followers-mostfollowed":
			$s->assign('header', 'Most-Followed Followers' );		
			$s->assign('people', $fd->getMostFollowedFollowers($cfg->twitter_user_id, 15));
			break;
		case "followers-leastlikely":
			$s->assign('header', 'Least-Likely Followers' );		
			$s->assign('people', $fd->getLeastLikelyFollowers($cfg->twitter_user_id, 15));
			break;
		case "followers-former":
			$s->assign('header', 'Former Followers' );		
			$s->assign('people', $fd->getFormerFollowers($cfg->twitter_user_id, 15));
			break;
		case "followers-earliest":
			$s->assign('header', 'Earliest Joiners' );		
			$s->assign('people', $fd->getEarliestJoinerFollowers($cfg->twitter_user_id, 15));
			break;
		case "friends-mostactive":
			$s->assign('header', 'Most Active Friends' );		
			$s->assign('people', $fd->getMostActiveFollowees($cfg->twitter_user_id, 15));
			break;
		case "friends-leastactive":
			$s->assign('header', 'Least Active Friends' );		
			$s->assign('people', $fd->getLeastActiveFollowees($cfg->twitter_user_id, 15));
			break;
		case "friends-mostfollowed":
			$s->assign('header', 'Most-Followed Friends' );		
			$s->assign('people', $fd->getMostFollowedFollowees($cfg->twitter_user_id, 15));
			break;
		case "friends-former":
			$s->assign('header', 'Former Friends' );		
			$s->assign('people', $fd->getFormerFollowees($cfg->twitter_user_id, 15));
			break;
		case "friends-notmutual":
			$s->assign('header', 'Not Mutual Friends' );		
			$s->assign('people', $fd->getFriendsNotFollowingBack($cfg->twitter_user_id));
			break;
	}
}

# clean up
$db->closeConnection($conn);	

$s->display('inline.view.tpl', $i->twitter_username."-".$_SESSION['user']."-".$_REQUEST['d']);




?>