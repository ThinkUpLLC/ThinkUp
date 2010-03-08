<?php
session_start();
if (!isset($_SESSION['user'])) {
    $loggedin = false;
} else {
    $loggedin = true;
}

// set up
require_once ('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("init.php");

$db = new Database($THINKTANK_CFG);
$conn = $db->getConnection();
$cfg = new Config();

$td = new TweetDAO($db);
$id = new InstanceDAO($db);
$s = new SmartyThinkTank();

$s->assign('cfg', $cfg);
$i = $id->getInstanceFreshestOne();
$s->assign('crawler_last_run', $i->crawler_last_run);

// show tweet with public replies
if (isset($_REQUEST['t']) && $td->isTweetByPublicInstance($_REQUEST['t'])) {
	if (!$s->is_cached('public.tpl', $_REQUEST['t'])) {
		$tweet = $td->getTweet($_REQUEST['t']);
		$public_tweet_replies = $td->getPublicRepliesToTweet($tweet->status_id);
		$public_retweets = $td->getRetweetsOfTweet($tweet->status_id, true);
		$s->assign('tweet', $tweet);
		$s->assign('replies', $public_tweet_replies);
		$s->assign('retweets', $public_retweets);
		$rtreach = 0;
		foreach ($public_retweets as $t ) {
			$rtreach += $t->author->follower_count;
		}
		$s->assign('rtreach', $rtreach);
		$s->assign('site_root', $THINKTANK_CFG['site_root_path']);
	}
	$s->display('public.tpl', $_REQUEST['t']);

} elseif (isset($_REQUEST['v'])) {
	$view = $_REQUEST['v'];
	$s->assign('loggedin', $loggedin);
	switch ($view) {
		case 'timeline':
			if (!$s->is_cached('public.tpl')) {
				$s->assign('tweets', $td->getTweetsByPublicInstances());
				$s->assign('site_root', $THINKTANK_CFG['site_root_path']);
			}
			$s->assign('header', 'Public timeline' );
			$s->display('public.tpl', 'timeline');
			break;
		case 'mostretweets':
			if (!$s->is_cached('public.tpl', 'mostretweets')) {
				$s->assign('tweets', $td->getMostRetweetedTweetsByPublicInstances());
				$s->assign('site_root', $THINKTANK_CFG['site_root_path']);
			}
			$s->assign('header', 'Most retweeted' );
			$s->assign('description', 'Updates that have been forwarded most often');
			$s->display('public.tpl', 'mostretweets');
			break;
		case 'mostreplies':
			if (!$s->is_cached('public.tpl', 'mostreplies')) {
				$s->assign('tweets', $td->getMostRepliedToTweetsByPublicInstances());
				$s->assign('site_root', $THINKTANK_CFG['site_root_path']);
			}
			$s->assign('header', 'Most replied to' );
			$s->assign('description', 'Updates that have been replied to most often');
			$s->display('public.tpl', 'mostreplies');
			break;
		case 'photos':
			if (!$s->is_cached('public.tpl', 'photos')) {
				$s->assign('tweets', $td->getPhotoTweetsByPublicInstances());
				$s->assign('site_root', $THINKTANK_CFG['site_root_path']);
			}
			$s->assign('header', 'Photos' );
			$s->assign('description', 'Posted photos');
			$s->display('public.tpl', 'photos');
			break;
		case 'links':
			if (!$s->is_cached('public.tpl', 'links')) {
				$s->assign('tweets', $td->getLinkTweetsByPublicInstances());
				$s->assign('site_root', $THINKTANK_CFG['site_root_path']);
			}
			$s->assign('header', 'Links' );
			$s->assign('description', 'Posted links');
			$s->display('public.tpl', 'links');
			break;

	}

} else {
	if (!$s->is_cached('public.tpl', 'timeline')) {
		$s->assign('tweets', $td->getTweetsByPublicInstances());
		$s->assign('site_root', $THINKTANK_CFG['site_root_path']);
	}
	$s->assign('header', 'Public timeline' );
	$s->display('public.tpl', 'timeline');

}

?>
