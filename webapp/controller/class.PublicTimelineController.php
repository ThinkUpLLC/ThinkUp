<?php
/**
 * Public Timeline Controller
 *
 * Renders the public timeline and public post and reply list for all users
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class PublicTimelineController extends ThinkUpController {
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
     * @param bool $session_started
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->post_dao = DAOFactory::getDAO('PostDAO');

        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $last_updated_instance = $instance_dao->getInstanceFreshestOne();
        if (isset($last_updated_instance)) {
            $this->addToView('crawler_last_run', $last_updated_instance->crawler_last_run);
        }
        $this->setPageTitle('Public Timeline');
    }

    /**
     * Show either timeline of public posts with counts, or an individual post thread with replies and retweets
     * @return string rendered view markup
     */
    public function control() {
        $this->setViewTemplate('public.tpl');
        $this->addToView('logo_link', 'public.php');

        //if $_GET["t"], load individual post + replies + retweets
        //TODO: deprecate the t (for tweet) to p (for post), but don't break existing URLs
        if (isset($_GET['t'])) {
            //default network to twitter if not specified (don't break existing URLS)
            $network = (isset($_GET['n']) )?$_GET['n']:'twitter';
            $_GET['n'] = $network;
            if ($this->shouldRefreshCache()) {
                $this->loadSinglePostThread($_GET['t'], $network);
            }
        } elseif (isset($_GET["v"])) { //else if $_GET["v"], display correct listing
            if ($this->shouldRefreshCache()) {
                $this->loadPublicPostList($_GET["v"]);
            }
        } elseif (isset($_GET["u"]) && isset($_GET['n'])) { //else if $_GET["i"], display instance dashboard
            if ($this->shouldRefreshCache()) {
                $this->loadPublicInstanceDashboard($_GET["u"], $_GET['n']);
            }
        } else { //else default to public timeline list
            $_GET["v"] = 'timeline';
            if ($this->shouldRefreshCache()) {
                $this->loadPublicPostList('timeline');
            }
        }
        return $this->generateView();
    }

    /**
     * Load view with individual post and replies and retweets
     * @param int $post_id
     * @param str $network
     */
    private function loadSinglePostThread($post_id, $network) {
        $this->setPageTitle('Public Post Replies');
        $this->post_dao->isPostByPublicInstance($_GET['t'], $network);
        $post = $this->post_dao->getPost($post_id, $network);
        if (!isset($post)) {
            $this->addErrorMessage("Post ".$post_id." on ".ucwords($network)." is not in ThinkUp.");
        } else {
            $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
            $options = $plugin_option_dao->getOptionsHash('geoencoder', true);
            if (isset($options['distance_unit']->option_value)) {
                $distance_unit = $options['distance_unit']->option_value;
            } else {
                $distance_unit = 'km';
            }
            $public_tweet_replies = $this->post_dao->getPublicRepliesToPost($post->post_id, $network, 'default', 
                                    $distance_unit);
            $public_retweets = $this->post_dao->getRetweetsOfPost($post->post_id, $network,
                               'default', $distance_unit, true);
            $this->addToView('post', $post);
            $this->addToView('replies', $public_tweet_replies);
            $this->addToView('retweets', $public_retweets);
            $this->addToView('unit', $distance_unit);
            $rtreach = 0;
            foreach ($public_retweets as $t) {
                $rtreach += $t->author->follower_count;
            }
            $this->addToView('rtreach', $rtreach);
        }
    }

    /**
     * Load instance dashboard
     * @param str $username
     * @param str $network
     */
    private function loadPublicInstanceDashboard($username, $network) {
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $instance = $instance_dao->getByUsernameOnNetwork($username, $network);

        if (isset($instance) && $instance->is_public) {
            $this->setPageTitle($instance->network_username . "'s Public Profile");

            $this->addToView('instance', $instance);
            //user
            $user_dao = DAOFactory::getDAO('UserDAO');
            $user = $user_dao->getDetails($instance->network_user_id, $instance->network);
            $this->addToView('user_details', $user);

            //posts
            $most_replied_to_alltime = $this->post_dao->getMostRepliedToPosts($instance->network_user_id, $network, 5);
            $this->addToView('most_replied_to_alltime', $most_replied_to_alltime);
            $most_retweeted_alltime = $this->post_dao->getMostRetweetedPosts($instance->network_user_id, $network, 5);
            $this->addToView('most_retweeted_alltime', $most_retweeted_alltime);
            $most_replied_to_1wk = $this->post_dao->getMostRepliedToPostsInLastWeek($instance->network_username,
            $instance->network, 5);
            $this->addToView('most_replied_to_1wk', $most_replied_to_1wk);
            $most_retweeted_1wk = $this->post_dao->getMostRetweetedPostsInLastWeek($instance->network_username,
            $instance->network, 5);
            $this->addToView('most_retweeted_1wk', $most_retweeted_1wk);
            $conversations = $this->post_dao->getPostsAuthorHasRepliedTo($instance->network_user_id, 5);
            $this->addToView('conversations', $conversations);

            //follows
            $follow_dao = DAOFactory::getDAO('FollowDAO');
            $least_likely_followers = $follow_dao->getLeastLikelyFollowers($instance->network_user_id, 'twitter', 16);
            $this->addToView('least_likely_followers', $least_likely_followers);

            //follower count history
            $follower_count_dao = DAOFactory::getDAO('FollowerCountDAO');
            $follower_count_history_by_day = $follower_count_dao->getHistory($instance->network_user_id, 'twitter',
            'DAY');
            $this->addToView('follower_count_history_by_day', $follower_count_history_by_day);
            $follower_count_history_by_week = $follower_count_dao->getHistory($instance->network_user_id, 'twitter',
            'WEEK');
            $this->addToView('follower_count_history_by_week', $follower_count_history_by_week);
        } else {
            $this->addErrorMessage($username." on ".ucwords($network).
            " isn't set up on this ThinkUp installation.");
        }
    }

    /**
     * Load view with appropriate public post list. Default to reverse chronological order.
     * @param string $list
     */
    private function loadPublicPostList($list) {
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