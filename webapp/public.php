<?php 
// set up
require_once ('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("init.php");
session_start();
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
        $s->assign('site_root', $THINKTANK_CFG['site_root_path']);
    }
    $s->display('public.tpl', $_REQUEST['t']);
    
} else {
    if (!$s->is_cached('public.tpl')) {
        $s->assign('tweets', $td->getTweetsByPublicInstances());
        $s->assign('site_root', $THINKTANK_CFG['site_root_path']);
    }
    $s->display('public.tpl');
    
}

?>
