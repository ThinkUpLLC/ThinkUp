<?php

// set up
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path"). PATH_SEPARATOR .$INCLUDE_PATH);
require_once("init.php");

$SQLLogger = new LoggerSlowSQL($THINKTANK_CFG['sql_log_location']);
$db = new Database($THINKTANK_CFG, $SQLLogger);
$conn = $db->getConnection();

$td = new TweetDAO($db);
$s = new SmartyThinkTank();

// show tweet with public replies
if ( isset($_REQUEST['t']) && $td->isTweetByPublicInstance($_REQUEST['t']) ){
	if(!$s->is_cached('public.tpl', $_REQUEST['t'])) {
		$tweet = $td->getTweet($_REQUEST['t']);
		$public_tweet_replies = $td->getPublicRepliesToTweet($tweet->status_id);
		$s->assign('tweet', $tweet);
		$s->assign('replies', $public_tweet_replies);
		$s->assign('site_root', $THINKTANK_CFG['site_root_path']);
	}
	$s->display('public.tpl', $_REQUEST['t']);

} else {
	if(!$s->is_cached('public.tpl')) {
		$s->assign('tweets', $td-> getTweetsByPublicInstances() );
		$s->assign('site_root', $THINKTANK_CFG['site_root_path']);
	}
	$s->display('public.tpl');

}
$SQLLogger->close();

?>
