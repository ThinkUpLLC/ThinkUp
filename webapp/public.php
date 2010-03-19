<?php 
session_start();
(isset($_SESSION['user'])) ? $_u = $_SESSION['user']: $_u = '';
(isset($_SESSION['instance'])) ? $_i = $_SESSION['instance'] : $_i = '';

//Print_r  ($_i);

// set up
require_once ('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("init.php");

$db = new Database($THINKTANK_CFG);
$conn = $db->getConnection();
$cfg = new Config();

$pd = new PostDAO($db);
$id = new InstanceDAO($db);
$s = new SmartyThinkTank();

$s->assign('cfg', $cfg);
$i = $id->getInstanceFreshestOne();
$s->assign('crawler_last_run', $i->crawler_last_run);
$s->assign('i', $_i);

//Pagination setup
if(isset($_REQUEST['page'])) {
    $page = $_REQUEST['page'];
}else{
    $page = 1;
}
if($page > 1){
    $s->assign('prev_page', $page - 1);
}
$count = 15;
$next_page = $page + 1;
$start_on_record = ($page - 1) * $count;

// show tweet with public replies
if (isset($_REQUEST['t']) && $pd->isPostByPublicInstance($_REQUEST['t'])) {
    if (!$s->is_cached('public.tpl', $_REQUEST['t'])) {
        $post = $pd->getPost($_REQUEST['t']);
        $public_tweet_replies = $pd->getPublicRepliesToPost($post->post_id);
        $public_retweets = $pd->getRetweetsOfPost($post->post_id, true);
        $s->assign('post', $post);
        $s->assign('replies', $public_tweet_replies);
        $s->assign('retweets', $public_retweets);
        $rtreach = 0;
        foreach ($public_retweets as $t) {
            $rtreach += $t->author->follower_count;
        }
        $s->assign('rtreach', $rtreach);
        $s->assign('site_root', $THINKTANK_CFG['site_root_path']);
    }
    $s->display('public.tpl', $_REQUEST['t']);
    
} elseif (isset($_REQUEST['v'])) {
    $view = $_REQUEST['v'];
    $s->assign('next_page', $next_page);
    switch ($view) {
        case 'timeline':
            if (!$s->is_cached('public.tpl')) {
                $s->assign('posts', $pd->getPostsByPublicInstances($start_on_record, $count));
                $s->assign('site_root', $THINKTANK_CFG['site_root_path']);
            }
            $s->assign('header', 'Latest');
            $s->assign('description', 'Latest public posts and public replies');
            $s->display('public.tpl', 'timeline');
            break;
        case 'mostretweets':
            if (!$s->is_cached('public.tpl', 'mostretweets')) {
                $s->assign('posts', $pd->getMostRetweetedPostsByPublicInstances($start_on_record, $count));
                $s->assign('site_root', $THINKTANK_CFG['site_root_path']);
            }
            $s->assign('header', 'Most retweeted');
            $s->assign('description', 'Posts that have been forwarded most often');
            $s->display('public.tpl', 'mostretweets');
            break;
        case 'mostreplies':
            if (!$s->is_cached('public.tpl', 'mostreplies')) {
                $s->assign('posts', $pd->getMostRepliedToPostsByPublicInstances($start_on_record, $count));
                $s->assign('site_root', $THINKTANK_CFG['site_root_path']);
            }
            $s->assign('header', 'Most replied to');
            $s->assign('description', 'Posts that have been replied to most often');
            $s->display('public.tpl', 'mostreplies');
            break;
        case 'photos':
            if (!$s->is_cached('public.tpl', 'photos')) {
                $totals = $pd->getCountPhotoPostsByPublicInstances($count);
                echo $totals['total']." ".$totals['pages'];
                $s->assign('posts', $pd->getPhotoPostsByPublicInstances($start_on_record, $count));
                $s->assign('site_root', $THINKTANK_CFG['site_root_path']);
            }
            $s->assign('header', 'Photos');
            $s->assign('description', 'Posted photos');
            $s->display('public.tpl', 'photos');
            break;
        case 'links':
            if (!$s->is_cached('public.tpl', 'links')) {
                $s->assign('posts', $pd->getLinkPostsByPublicInstances($start_on_record, $count));
                $s->assign('site_root', $THINKTANK_CFG['site_root_path']);
            }
            $s->assign('header', 'Links');
            $s->assign('description', 'Posted links');
            $s->display('public.tpl', 'links');
            break;
            
    }
    
} else {
    if (!$s->is_cached('public.tpl', 'timeline-'.$i->network_username."-".$_u)) {
        $s->assign('posts', $pd->getPostsByPublicInstances($start_on_record, $count));
        $s->assign('site_root', $THINKTANK_CFG['site_root_path']);
    }
    $s->assign('header', 'Latest');
    $s->assign('description', 'Latest public posts, replies and forwards');
    $s->assign('next_page', $next_page);
    $s->display('public.tpl', 'timeline-'.$i->network_username."-".$_u);
    
}

?>
