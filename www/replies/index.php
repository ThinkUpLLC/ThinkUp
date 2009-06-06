<?php
// set up
chdir("..");
$root_path 			= realpath('./../include')."/";
require_once($root_path . "init.php");
$cfg = new Config();
$db = new Database();
$s = new SmartyTwitalytic();
$c = new Crawler();

$conn = $db->getConnection();

// instantiate data access objects
$ud = new UserDAO();
$fd = new FollowDAO();
$td = new TweetDAO();
$c->init();


if ( isset($_REQUEST['t']) && is_numeric($_REQUEST['t']) && $td->isTweetInDB($_REQUEST['t']) ){
	$status_id = $_REQUEST['t'];
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
	$s->assign('likely_orphans', $td->getLikelyOrphansForParent($tweet['pub_date'], $cfg->owner_user_id, 15) );


	$s->assign('cfg', $cfg);
	$s->assign('crawler', $c);
	# clean up
	$db->closeConnection($conn);	

	echo $s->fetch('replies.index.tpl');
/*
	echo $s->fetch('replies.public.tpl');
*/
} else {
	echo 'No such tweet ID<br /><a href="'. $cfg->site_root_path .'">back home</a>';
}
?>