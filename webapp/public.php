<?php
session_start();
(isset($_SESSION['user'])) ? $_u = $_SESSION['user'] : $_u = '';
(isset($_SESSION['instance'])) ? $_i = $_SESSION['instance'] : $_i = '';

//Print_r  ($_i);

require_once 'init.php';

$pd = new PostDAO($db);
$id = DAOFactory::getDAO('InstanceDAO');
$s = new SmartyThinkTank();

//Pagination
$count = 15;
if (isset($_REQUEST['page'])) {
    $page = $_REQUEST['page'];
} else {
    $page = 1;
}
if ($page > 1) {
    $s->assign('prev_page', $page - 1);
}

$s->assign('site_root_path', $config->getValue('site_root_path'));
$i = $id->getInstanceFreshestOne();
if (isset($i)) {
    $s->assign('crawler_last_run', $i->crawler_last_run);
}
$s->assign('i', $_i);
$s->assign('site_root', $config->getValue('site_root_path'));

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
    }
    $s->display('public.tpl', $_REQUEST['t']);

} elseif (isset($_REQUEST['v'])) {
    $view = $_REQUEST['v'];
    switch ($view) {
        case 'timeline':
            if (!$s->is_cached('public.tpl', $page)) {
                $totals = $pd->getTotalPagesAndPostsByPublicInstances($count);
                if ($totals['total_pages'] > $page) {
                    $s->assign('next_page', $page + 1);
                }
                $s->assign('current_page', $page);
                $s->assign('total_pages', $totals['total_pages']);
                $s->assign('posts', $pd->getPostsByPublicInstances($page, $count));
            }
            $s->assign('header', 'Latest');
            $s->assign('description', 'Latest public posts and public replies');
            $s->display('public.tpl', 'timeline-'.$i->network_username."-".$_u."-".$page);
            break;
        case 'mostretweets':
            if (!$s->is_cached('public.tpl', 'mostretweets-'.$page)) {
                $totals = $pd->getTotalPagesAndPostsByPublicInstances($count);
                if ($totals['total_pages'] > $page) {
                    $s->assign('next_page', $page + 1);
                }
                $s->assign('current_page', $page);
                $s->assign('total_pages', $totals['total_pages']);
                $s->assign('posts', $pd->getMostRetweetedPostsByPublicInstances($page, $count));
            }
            $s->assign('header', 'Most forwarded');
            $s->assign('description', 'Posts that have been forwarded most often');
            $s->display('public.tpl', 'mostretweets-'.$i->network_username."-".$_u."-".$page);
            break;
        case 'mostreplies':
            if (!$s->is_cached('public.tpl', 'mostreplies-'.$page)) {
                $totals = $pd->getTotalPagesAndPostsByPublicInstances($count);
                if ($totals['total_pages'] > $page) {
                    $s->assign('next_page', $page + 1);
                }
                $s->assign('current_page', $page);
                $s->assign('total_pages', $totals['total_pages']);
                $s->assign('posts', $pd->getMostRepliedToPostsByPublicInstances($page, $count));
            }
            $s->assign('header', 'Most replied to');
            $s->assign('description', 'Posts that have been replied to most often');
            $s->display('public.tpl', 'mostreplies-'.$i->network_username."-".$_u."-".$page);
            break;
        case 'photos':
            if (!$s->is_cached('public.tpl', 'photos-'.$page)) {
                $totals = $pd->getTotalPhotoPagesAndPostsByPublicInstances($count);
                if ($totals['total_pages'] > $page) {
                    $s->assign('next_page', $page + 1);
                }
                $s->assign('current_page', $page);
                $s->assign('total_pages', $totals['total_pages']);
                $s->assign('posts', $pd->getPhotoPostsByPublicInstances($page, $count));
            }
            $s->assign('header', 'Photos');
            $s->assign('description', 'Posted photos');
            $s->display('public.tpl', 'photos-'.$i->network_username."-".$_u."-".$page);
            break;
        case 'links':
            if (!$s->is_cached('public.tpl', 'links-'.$page)) {
                $totals = $pd->getTotalLinkPagesAndPostsByPublicInstances($count);
                if ($totals['total_pages'] > $page) {
                    $s->assign('next_page', $page + 1);
                }
                $s->assign('current_page', $page);
                $s->assign('total_pages', $totals['total_pages']);
                $s->assign('posts', $pd->getLinkPostsByPublicInstances($page, $count));
            }
            $s->assign('header', 'Links');
            $s->assign('description', 'Posted links');
            $s->display('public.tpl', 'links-'.$i->network_username."-".$_u."-".$page);
            break;

    }

} else {
    if (isset($i) && !$s->is_cached('public.tpl', 'timeline-'.$i->network_username."-".$_u."-".$page)) {
        $totals = $pd->getTotalPagesAndPostsByPublicInstances($count);
        if ($totals['total_pages'] > $page) {
            $s->assign('next_page', $page + 1);
        }
        $s->assign('current_page', $page);
        $s->assign('total_pages', $totals['total_pages']);
        $s->assign('posts', $pd->getPostsByPublicInstances($page, $count));
    }
    $s->assign('header', 'Latest');
    $s->assign('description', 'Latest public posts, replies and forwards');
    if (isset($i)) {
        $s->display('public.tpl', 'timeline-'.$i->network_username."-".$_u."-".$page);
    } else {
        $s->display('public.tpl', 'timeline--'.$_u."-".$page);
    }

}
?>
