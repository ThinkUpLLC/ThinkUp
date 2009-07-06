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

$td = new TweetDAO();


if ( isset($_REQUEST['t']) && is_numeric($_REQUEST['t']) && $td->isTweetInDB($_REQUEST['t']) ){
	$status_id = $_REQUEST['t'];
	$tweet = $td->getTweet($status_id);

	$s = new SmartyTwitalytic();
	$u = new Utils();

	$id = new InstanceDAO();
	$i = $id->getByUsername($tweet['author_username']);
	if ( isset($i) ) {
		$s->assign('likely_orphans', $td->getLikelyOrphansForParent($tweet['pub_date'],  $i->twitter_user_id, 15) );
		$s->assign('all_tweets', $td->getAllTweets($i->twitter_user_id, 15) );
		
	}
	$cfg = new Config($i->twitter_username, $i->twitter_user_id);
	

	// instantiate data access objects
	$ud = new UserDAO();
	
	
	$all_replies = $td->getRepliesToTweet($status_id);
	$all_replies_count = count($all_replies);
	$public_replies = $td->getPublicRepliesToTweet($status_id);
	$public_replies_count = count($public_replies);
	$private_replies_count = $all_replies_count - $public_replies_count;
	$tweet = $td->getTweet($status_id);


	$s->assign('tweet', $tweet);
	$s->assign('replies', $all_replies );
	$s->assign('public_reply_count', $public_replies_count );
	$s->assign('private_reply_count', $private_replies_count );
	$s->assign('reply_count', $all_replies_count );


	$s->assign('cfg', $cfg);
	$s->assign('instance', $i);
	# clean up
	$db->closeConnection($conn);	

	echo $s->fetch('status.index.tpl');
/*
	echo $s->fetch('replies.public.tpl');
*/
} else {
	echo 'This update is not in the system.<br /><a href="'. $cfg->site_root_path .'">back home</a>';
}
?>