<?php
/**
 * Private Dashboard Controller
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class PrivateDashboardController extends ThinkTankAuthController {
    /**
     * @var PostDAO
     */
    protected $post_dao;

    /**
     * Constructor
     *
     * @param boolean $session_started
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        global $db; //@TODO: remove this when PDO port is done
        $this->post_dao = DAOFactory::getDAO('PostDAO');

        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $last_updated_instance = $instance_dao->getInstanceFreshestOne();
        if (isset($last_updated_instance)) {
            $this->addToView('crawler_last_run', $last_updated_instance->crawler_last_run);
        }
        $this->addToView('controller_title', 'Private Dashboard');
    }

    /**
     * Handle requests for private dashboard data
     * @TODO Throw an Insufficient privileges Exception when owner doesn't have access to an instance
     */
    public function auth_control() {
        global $db; //@TODO: remove this when PDO port is done
        global $webapp; //@TODO: stop globalizing this; convert Webapp object to singleton?

        $this->setViewTemplate('index.tpl');

        $continue = true;
        $owner_dao = new OwnerDAO($db);
        $owner = $owner_dao->getByEmail($this->getLoggedInUser());
        $instancenstance_dao = DAOFactory::getDAO('InstanceDAO');
        $config = Config::getInstance();

        if ( (isset($_REQUEST['u']) && isset($_REQUEST['n'])) && $instancenstance_dao->isUserConfigured($_REQUEST['u']) ){
            $username = $_REQUEST['u'];
            $owner_instance_dao = new OwnerInstanceDAO($db);
            if ( !$owner_instance_dao->doesOwnerHaveAccess($owner, $username) ) {
                $this->addToView('error','Insufficient privileges. <a href="/">Back</a>.');
                $continue = false;
            } else {
                $instance = $instancenstance_dao->getByUsernameOnNetwork($username, $_REQUEST['n']);
            }
        } else {
            $instance = $instancenstance_dao->getFreshestByOwnerId($owner->id);
            if ( !isset($instance) && $instance == null ) {
                $this->addToView('msg', 'You have no Twitter accounts configured. <a href="'.$config->getValue('site_root_path').'account/?p=twitter">Set up an account&rarr;</a>');
                $continue = false;
            }
        }

        if ($continue) {
            $this->addToViewCacheKey($instance->network_username);
            $this->addToViewCacheKey($instance->network);

            // instantiate data access objects
            $user_dao = new UserDAO($db);
            $follow_dao = DAOFactory::getDAO('FollowDAO');

            // pass data to smarty
            $owner_stats = $user_dao->getDetails($instance->network_user_id);
            $this->addToView('owner_stats', $owner_stats);

            $this->addToView('instance', $instance);
            $this->addToView('instances', $instancenstance_dao->getByOwner($owner));

            $total_follows_with_errors = $follow_dao->countTotalFollowsWithErrors($instance->network_user_id);
            $this->addToView('total_follows_with_errors', $total_follows_with_errors);

            $total_follows_with_full_details = $follow_dao->countTotalFollowsWithFullDetails($instance->network_user_id);
            $this->addToView('total_follows_with_full_details', $total_follows_with_full_details);

            $total_follows_protected = $follow_dao-> countTotalFollowsProtected($instance->network_user_id);
            $this->addToView('total_follows_protected', $total_follows_protected);

            //TODO: Get friends with full details and also friends with errors, same as with followers
            $total_friends_loaded = $follow_dao->countTotalFriends($instance->network_user_id);
            $this->addToView('total_friends', $total_friends_loaded);

            $total_friends_with_errors = $follow_dao->countTotalFriendsWithErrors($instance->network_user_id);
            $this->addToView('total_friends_with_errors', $total_friends_with_errors);

            $total_friends_protected = $follow_dao->countTotalFriendsProtected($instance->network_user_id);
            $this->addToView('total_friends_protected', $total_friends_protected);

            //Percentages
            if (isset($owner_stats)) {
                $percent_followers_loaded = Utils::getPercentage($owner_stats->follower_count, ($total_follows_with_full_details + $total_follows_with_errors));
                $percent_followers_loaded = ($percent_followers_loaded  > 100) ? 100 : $percent_followers_loaded;
                $this->addToView('percent_followers_loaded', $percent_followers_loaded);

                $percent_tweets_loaded = Utils::getPercentage($owner_stats->post_count,$instance->total_posts_in_system );
                $percent_tweets_loaded = ($percent_tweets_loaded  > 100) ? 100 : $percent_tweets_loaded;
                $this->addToView('percent_tweets_loaded', $percent_tweets_loaded);

                $percent_friends_loaded = Utils::getPercentage($owner_stats->friend_count, ($total_friends_loaded));
                $percent_friends_loaded = ($percent_friends_loaded  > 100) ? 100 : $percent_friends_loaded;
                $this->addToView('percent_friends_loaded', $percent_friends_loaded);

                $percent_followers_suspended = round(Utils::getPercentage($total_follows_with_full_details, $total_follows_with_errors), 2);
                $this->addToView('percent_followers_suspended', $percent_followers_suspended);

                $percent_followers_protected = round(Utils::getPercentage($total_follows_with_full_details, $total_follows_protected), 2);
                $this->addToView('percent_followers_protected', $percent_followers_protected);
            }
            $webapp->setActivePlugin($instance->network);
            $this->addToView('post_tabs', $webapp->getChildTabsUnderPosts($instance));
            $this->addToView('replies_tabs', $webapp->getChildTabsUnderReplies($instance));
            $this->addToView('friends_tabs', $webapp->getChildTabsUnderFriends($instance));
            $this->addToView('followers_tabs', $webapp->getChildTabsUnderFollowers($instance));
            $this->addToView('links_tabs', $webapp->getChildTabsUnderLinks($instance));
        }
        return $this->generateView();
    }
}