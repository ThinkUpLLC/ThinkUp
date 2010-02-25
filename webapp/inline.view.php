<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: session/login.php"); }

// set up
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");

$db = new Database($THINKTANK_CFG);
$conn = $db->getConnection();

$od = new OwnerDAO($db);
$owner = $od->getByEmail($_SESSION['user']);

$id = new InstanceDAO($db);

if ( isset($_REQUEST['u']) && $id->isUserConfigured($_REQUEST['u']) ){
	$username = $_REQUEST['u'];
	$oid = new OwnerInstanceDAO($db);
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

if (!isset($_REQUEST['d']) ) {
	$_REQUEST['d'] = "all-tweets";
}

$s = new SmartyThinkTank();

if(!$s->is_cached('inline.view.tpl', $i->twitter_username."-".$_SESSION['user']."-".$_REQUEST['d'])) {

	$cfg = new Config($i->twitter_username, $i->twitter_user_id);
	$s->assign('cfg', $cfg);
	$s->assign('i', $i);

	$u = new Utils();

	// instantiate data access objects
	$ud = new UserDAO($db);
	$td = new TweetDAO($db);
	$fd = new FollowDAO($db);
	$ld = new LinkDAO($db);


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
		case "tweets-mostretweeted":
			$s->assign('header', 'Most Retweeted' );
			$s->assign('most_retweeted', $td->getMostRetweetedTweets($cfg->twitter_user_id, 15));
			break;
		case "tweets-convo":
			$s->assign('header', 'Conversations' );
			$s->assign('author_replies', $td->getTweetsAuthorHasRepliedTo($cfg->twitter_user_id, 15));
			break;
		case "mentions-all":
			$s->assign('header', 'All Mentions' );
			$s->assign('description', 'Any tweet that mentions you.');
			$s->assign('all_mentions', $td->getAllMentions($cfg->twitter_username, 15) );
			$s->assign('all_tweets', $td->getAllTweets($cfg->twitter_user_id, 15) );
			break;
		case "mentions-allreplies":
			$s->assign('header', 'Replies' );
			$s->assign('description', 'Tweets that directly reply to you (i.e., start with your name).');
			$s->assign('all_replies', $td->getAllReplies($cfg->twitter_user_id, 15) );
			break;
		case "mentions-orphan":
			$s->assign('header', 'Not Replies or Retweets' );
			$s->assign('description', 'Mentions that are not associated with a specific tweet.');
			$s->assign('all_tweets', $td->getAllTweets($cfg->twitter_user_id, 15) );
			$s->assign('orphan_replies', $td->getOrphanReplies($cfg->twitter_username, 5));
			break;
		case "mentions-standalone":
			$s->assign('header', 'Standalone Mentions' );
			$s->assign('description', 'Mentions you have marked as standalone.');
			$s->assign('all_tweets', $td->getAllTweets($cfg->twitter_user_id, 15) );
			$s->assign('standalone_replies', $td->getStandaloneReplies($cfg->twitter_username, 15));
			break;
		case "followers-mostfollowed":
			$s->assign('header', 'Most-Followed Followers' );
			$s->assign('description', 'Followers with most followers.');
			$s->assign('people', $fd->getMostFollowedFollowers($cfg->twitter_user_id, 15));
			break;
		case "followers-leastlikely":
			$s->assign('header', 'Least-Likely Followers' );
			$s->assign('description', 'Followers with the greatest follower-to-friend ratio.');
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
		case "links-friends":
			$s->assign('header', 'Links From Friends' );
			$s->assign('description', 'Links your friends tweeted.');
			$s->assign('links', $ld->getLinksByFriends($cfg->twitter_user_id));
			break;
		case "links-favorites":
			$s->assign('header', 'Links From Favorites' );
			$s->assign('description', 'Links in tweets you favorited.');
			//$s->assign('links', $ld->getLinksByFriends($cfg->twitter_user_id));
			break;
		case "links-photos":
			$s->assign('header', 'Photos' );
			$s->assign('description', 'Photos your friends have tweeted.');
			$s->assign('links', $ld->getPhotosByFriends($cfg->twitter_user_id));
			break;

	}
}

# clean up
$db->closeConnection($conn);

$s->display('inline.view.tpl', $i->twitter_username."-".$_SESSION['user']."-".$_REQUEST['d']);




?>
