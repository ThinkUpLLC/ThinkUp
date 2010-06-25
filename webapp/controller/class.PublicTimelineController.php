<?php
/**
 * Public Timeline Controller
 *
 * Renders the public timeline and public post and reply list for all users
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class PublicTimelineController extends ThinkTankController implements Controller {
    /**
     * @var int
     */
    protected $current_page;
    /**
     * @var PostDAO
     */
    protected $post_dao;
    /**
     * @var int
     */
    protected $total_posts_per_page = 15;

    /**
     * Constructor
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->post_dao = DAOFactory::getDAO('PostDAO');

        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $last_updated_instance = $instance_dao->getInstanceFreshestOne();
        if (isset($last_updated_instance)) {
            $this->addToView('crawler_last_run', $last_updated_instance->crawler_last_run);
        }
        $this->addToView('controller_title', 'Public Timeline');
    }

    /**
     * Show either timeline of public posts with counts, or an individual post thread with replies and retweets
     * @return string rendered view markup
     */
    public function control() {
        $this->setViewTemplate('public.tpl');
        $this->addToView('logo_link', 'public.php');

        if (isset($_GET['page']) && is_numeric($_GET['page'])) {
            $this->current_page = $_GET['page'];
        } else {
            $_GET['page'] = 1;
            $this->current_page = 1;
        }
        if ($this->current_page > 1) {
            $this->addToView('prev_page', $this->current_page - 1);
        }

        $this->addToView('current_page', $this->current_page);

        //if $_GET["t"], load individual post + replies + retweets
        //TODO: change the t (for tweet) to p (for post)
        if (isset($_GET['t']) && $this->post_dao->isPostByPublicInstance($_GET['t'])) {
            if ($this->shouldRefreshCache()) {
                $this->loadSinglePostThread($_GET['t']);
            }
        } elseif (isset($_GET["v"])) { //else if $_GET["v"], display correct listing
            if ($this->shouldRefreshCache()) {
                $this->loadPublicPostList($_GET["v"]);
            }
        } else { //else default to public timeline list
            if ($this->shouldRefreshCache()) {
                $_GET["v"] = 'timeline';
                $this->loadPublicPostList('timeline');
            }
        }
        return $this->generateView();
    }

    /**
     * Load view with individual post and replies and retweets
     * @param int $post_id
     */
    private function loadSinglePostThread($post_id) {
        $post = $this->post_dao->getPost($post_id);
        $public_tweet_replies = $this->post_dao->getPublicRepliesToPost($post->post_id);
        $public_retweets = $this->post_dao->getRetweetsOfPost($post->post_id, true);
        $this->addToView('post', $post);
        $this->addToView('replies', $public_tweet_replies);
        $this->addToView('retweets', $public_retweets);
        $rtreach = 0;
        foreach ($public_retweets as $t) {
            $rtreach += $t->author->follower_count;
        }
        $this->addToView('rtreach', $rtreach);
    }

    /**
     * Load view with appropriate public post list. Default to reverse chronological order.
     * @param string $list
     */
    private function loadPublicPostList($list) {
        $totals = $this->post_dao->getTotalPagesAndPostsByPublicInstances($this->total_posts_per_page);

        switch ($list) {
            case 'timeline':
                $this->addToView('posts', $this->post_dao->getPostsByPublicInstances($this->current_page,
                $this->total_posts_per_page));
                $this->addToView('header', 'Latest');
                $this->addToView('description', 'Latest public posts and public replies');
                break;
            case 'mostretweets':
                $this->addToView('posts', $this->post_dao->getMostRetweetedPostsByPublicInstances($this->current_page,
                $this->total_posts_per_page));
                $this->addToView('header', 'Most forwarded');
                $this->addToView('description', 'Posts that have been forwarded most often');
                break;
            case 'mostretweets1wk':
                $this->addToView('posts', $this->post_dao->getMostRetweetedPostsByPublicInstancesInLastWeek(
                $this->current_page, $this->total_posts_per_page));
                $this->addToView('header', 'Most forwarded this week');
                $this->addToView('description', 'Posts that have been forwarded most often this week');
                $totals = $this->post_dao->getTotalPagesAndPostsByPublicInstances($this->total_posts_per_page, 7);
                break;
            case 'mostreplies':
                $this->addToView('posts', $this->post_dao->getMostRepliedToPostsByPublicInstances($this->current_page,
                $this->total_posts_per_page));
                $this->addToView('header', 'Most replied to');
                $this->addToView('description', 'Posts that have been replied to most often');
                break;
            case 'mostreplies1wk':
                $this->addToView('posts', $this->post_dao->getMostRepliedToPostsByPublicInstancesInLastWeek(
                $this->current_page, $this->total_posts_per_page));
                $this->addToView('header', 'Most replied to this week');
                $this->addToView('description', 'Posts that have been replied to most often this week');
                $totals = $this->post_dao->getTotalPagesAndPostsByPublicInstances($this->total_posts_per_page, 7);
                break;
            case 'photos':
                $this->addToView('posts', $this->post_dao->getPhotoPostsByPublicInstances($this->current_page,
                $this->total_posts_per_page));
                $this->addToView('header', 'Photos');
                $this->addToView('description', 'Posted photos');
                break;
            case 'links':
                $totals = $this->post_dao->getTotalLinkPagesAndPostsByPublicInstances($this->total_posts_per_page);
                $this->addToView('posts', $this->post_dao->getLinkPostsByPublicInstances($this->current_page,
                $this->total_posts_per_page));
                $this->addToView('header', 'Links');
                $this->addToView('description', 'Posted links');
                break;
            default:
                $this->addToView('posts', $this->post_dao->getPostsByPublicInstances($this->current_page,
                $this->total_posts_per_page));
                $this->addToView('header', 'Latest');
                $this->addToView('description', 'Latest public posts and public replies');
                break;

        }
        if ($totals['total_pages'] > $this->current_page) {
            $this->addToView('next_page', $this->current_page + 1);
        }
        $this->addToView('total_pages', $totals['total_pages']);
    }
}