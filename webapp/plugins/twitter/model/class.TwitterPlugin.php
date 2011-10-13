<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterPlugin.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * Twitter Plugin
 *
 * Twitter crawler and webapp plugin retrieves data from Twitter and displays it.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TwitterPlugin extends Plugin implements CrawlerPlugin, DashboardPlugin, PostDetailPlugin {

    /**
     * Percentage of allocated API calls that each crawler function will use per run for non-authed instances.
     * @var array
     */
    var $api_budget_allocation_noauth = array(
        'fetchInstanceUserTweets' => array('percent' => 25),
        'fetchAndAddTweetRepliedTo' => array('percent' => 25), // for fetchStrayRepliedToTweets
        'fetchAndAddUser' => array('percent' => 25),
        'fetchFriendTweetsAndFriends' => array('percent' => 25),
        'fetchSearchResults' => array('percent' => 25),
        'cleanUpFollows' => array('percent' => 25)
    );

    /**
     * Percentage of allocated API calls that each crawler function will use per run for authed instances.
     * @var array
     */
    var $api_budget_allocation_auth = array(
        'fetchInstanceUserTweets' => array('percent' => 20),
        'fetchAndAddTweetRepliedTo' => array('percent' => 20), // for fetchStrayRepliedToTweets
        'fetchAndAddUser' => array('percent' => 20), // for fetchUnloadedFollowerDetails 
        'fetchFriendTweetsAndFriends' => array('percent' => 20),
        'fetchInstanceUserMentions' => array('percent' => 20),
        'fetchInstanceUserFriends' => array('percent' => 20),
        'getFavsPage' => array('percent' => 20), // called from testCleanupMissedFavs|maintFavsFetch|archivingFavsFetch
        'archivingFavsFetch' => array('percent' => 20), // called from fetchInstanceFavorites
        'fetchInstanceUserFollowersByIDs' => array('percent' => 20), // for fetchInstanceUserFollowers
        'fetchUserTimelineForRetweet' => array('percent' => 20), // fetchRetweetsOfInstanceUser->fetchStatusRetweets
        'cleanUpMissedFavsUnFavs' => array('percent' => 20),
        'cleanUpFollows' => array('percent' => 20),
        'fetchInstanceUserGroups'  => array('percent' => 20),
        'updateStaleGroupMemberships'  => array('percent' => 20),
    );

    public function __construct($vals = null) {
        parent::__construct($vals);
        $this->folder_name = 'twitter';
        $this->addRequiredSetting('oauth_consumer_key');
        $this->addRequiredSetting('oauth_consumer_secret');
    }

    public function activate() {
    }

    public function deactivate() {
        //Pause all active Twitter instances
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $twitter_instances = $instance_dao->getAllInstances("DESC", true, "twitter");
        foreach ($twitter_instances as $ti) {
            $instance_dao->setActive($ti->id, false);
        }
    }

    public function crawl() {
        $config = Config::getInstance();
        $logger = Logger::getInstance();
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');

        // get oauth values
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('twitter', true);

        $current_owner = $owner_dao->getByEmail(Session::getLoggedInUser());

        $instances = $instance_dao->getAllActiveInstancesStalestFirstByNetwork('twitter');
        foreach ($instances as $instance) {
            if (!$owner_instance_dao->doesOwnerHaveAccess($current_owner, $instance)) {
                // Owner doesn't have access to this instance; let's not crawl it.
                continue;
            }
            $logger->setUsername($instance->network_username);
            $logger->logUserSuccess("Starting to collect data for ".$instance->network_username." on Twitter.",
            __METHOD__.','.__LINE__);
            $tokens = $owner_instance_dao->getOAuthTokens($instance->id);
            $noauth = true;
            $num_twitter_errors =
            isset($options['num_twitter_errors']) ? $options['num_twitter_errors']->option_value : null;
            $max_api_calls_per_crawl =
            isset($options['max_api_calls_per_crawl']) ? $options['max_api_calls_per_crawl']->option_value : 350;
            if (isset($tokens['oauth_access_token']) && $tokens['oauth_access_token'] != ''
            && isset($tokens['oauth_access_token_secret']) && $tokens['oauth_access_token_secret'] != '') {
                $noauth = false;
            }
            $api_calls_to_leave_unmade_per_minute =  isset($options['api_calls_to_leave_unmade_per_minute']) ?
            $options['api_calls_to_leave_unmade_per_minute']->option_value : 2.0;

            if ($noauth) {
                $api = new CrawlerTwitterAPIAccessorOAuth('NOAUTH', 'NOAUTH',
                $options['oauth_consumer_key']->option_value,
                $options['oauth_consumer_secret']->option_value,
                $api_calls_to_leave_unmade_per_minute, $options['archive_limit']->option_value,
                $num_twitter_errors, $max_api_calls_per_crawl);
            } else {
                $api = new CrawlerTwitterAPIAccessorOAuth($tokens['oauth_access_token'],
                $tokens['oauth_access_token_secret'], $options['oauth_consumer_key']->option_value,
                $options['oauth_consumer_secret']->option_value,
                $api_calls_to_leave_unmade_per_minute, $options['archive_limit']->option_value,
                $num_twitter_errors, $max_api_calls_per_crawl);
            }

            // budget our twitter calls
            $call_limits = $this->budgetCrawlLimits($max_api_calls_per_crawl, $noauth);

            $crawler = new TwitterCrawler($instance, $api);

            $api->init();
            $api->setCallerLimits($call_limits);

            if ($api->available_api_calls_for_crawler > 0) {

                $instance_dao->updateLastRun($instance->id);

                // No auth for public Twitter users
                $crawler->fetchInstanceUserTweets();

                if (!$noauth) {
                    // Auth req'd, for calling user only
                    $crawler->fetchInstanceUserMentions();
                    $crawler->fetchInstanceUserFriends();
                    $crawler->fetchInstanceFavorites();
                    $crawler->fetchInstanceUserFollowers();
                    $crawler->fetchInstanceUserGroups();
                    $crawler->fetchRetweetsOfInstanceUser();
                    $crawler->cleanUpMissedFavsUnFavs();
                    $crawler->updateStaleGroupMemberships();
                }

                $crawler->fetchStrayRepliedToTweets();
                $crawler->fetchUnloadedFollowerDetails();
                $crawler->fetchFriendTweetsAndFriends();

                if ($noauth) {
                    // No auth req'd
                    $crawler->fetchSearchResults($instance->network_username);
                }

                $crawler->cleanUpFollows();

                // Save instance
                if (isset($crawler->user)) {
                    $instance_dao->save($instance, $crawler->user->post_count, $logger);
                }
                $logger->logUserSuccess("Finished collecting data for ".$instance->network_username." on Twitter.",
                __METHOD__.','.__LINE__);
            }
        }
    }

    public function renderConfiguration($owner) {
        $controller = new TwitterPluginConfigurationController($owner, 'twitter');
        return $controller->go();
    }

    public function getDashboardMenuItems($instance) {

        // determine if the Twitter Realtime plugin is active.
        $rt_plugin_active = false;
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId('twitterrealtime');
        if (isset($plugin_id)) {
            $rt_plugin_active = $plugin_dao->isPluginActive($plugin_id);
        }

        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl';
        $menus = array();
        //All tab
        $all_mi = new MenuItem("All tweets", "All tweets", $twitter_data_tpl, "Tweets");
        $all_mi_ds = new Dataset("all_tweets", 'PostDAO', "getAllPosts", array($instance->network_user_id,
        'twitter', 15, "#page_number#"), 'getAllPostsIterator', array($instance->network_user_id, 'twitter', 
        GridController::getMaxRows()) );
        $all_mi_ds->addHelp('userguide/listings/twitter/dashboard_tweets-all');
        $all_mi->addDataset($all_mi_ds);
        $menus['tweets-all'] = $all_mi;

        //Questions
        $q_mi = new MenuItem("Inquiries", "Inquiries, or tweets with a question mark in them",
        $twitter_data_tpl);
        $q_mi_ds = new Dataset("all_tweets", 'PostDAO', "getAllQuestionPosts",
        array($instance->network_user_id, 'twitter', 15, "#page_number#"));
        $q_mi_ds->addHelp( 'userguide/listings/twitter/dashboard_tweets-questions');
        $q_mi->addDataset($q_mi_ds);
        $menus['tweets-questions'] = $q_mi;

        // Most replied-to
        $mrt_mi = new MenuItem("Most replied-to", "Tweets with most replies", $twitter_data_tpl);
        $mrt_mi_ds = new Dataset("most_replied_to_tweets", 'PostDAO', "getMostRepliedToPosts",
        array($instance->network_user_id, 'twitter', 15, '#page_number#'));
        $mrt_mi_ds->addHelp('userguide/listings/twitter/dashboard_tweets-mostreplies');
        $mrt_mi->addDataset($mrt_mi_ds);
        $menus['tweets-mostreplies'] = $mrt_mi;

        // Most shared
        $mstab = new MenuItem("Most retweeted", "Most retweeted tweets", $twitter_data_tpl);
        $mstabds = new Dataset("most_retweeted", 'PostDAO', "getMostRetweetedPosts",
        array($instance->network_user_id, 'twitter', 15, '#page_number#'));
        $mstabds->addHelp('userguide/listings/twitter/dashboard_tweets-mostretweeted');
        $mstab->addDataset($mstabds);
        $menus["tweets-mostretweeted"] = $mstab;

        if ($rt_plugin_active) {
            // 'home timeline'
            $tltab = new MenuItem("Timeline", "Your Timeline", $twitter_data_tpl);
            $tltab2 = new Dataset("home_timeline", 'PostDAO', "getPostsByFriends",
            array($instance->network_user_id, $instance->network, 20, '#page_number#', !Session::isLoggedIn()),
            'getPostsByFriendsIterator', array($instance->network_user_id, 'twitter', GridController::getMaxRows()));
            $tltab->addDataset($tltab2);
            $menus["home-timeline"] = $tltab;
        }

        // Conversations
        $convotab = new MenuItem("Conversations", "Exchanges between you and other users", $twitter_data_tpl);
        $convotabds = new Dataset("author_replies", 'PostDAO', "getPostsAuthorHasRepliedTo",
        array($instance->network_user_id, 15, 'twitter', '#page_number#', !Session::isLoggedIn()));
        $convotabds->addHelp('userguide/listings/twitter/dashboard_tweets-convo');
        $convotab->addDataset($convotabds);
        $menus["tweets-convo"] = $convotab;

        // Messages to you
        $messagestab = new MenuItem("Tweets to you", "Tweets other users sent you", $twitter_data_tpl);
        $messagestabds = new Dataset("messages_to_you", 'PostDAO', "getPostsToUser",
        array($instance->network_user_id, $instance->network, 15, '#page_number#', !Session::isLoggedIn()),
        'getPostsToUserIterator', array($instance->network_user_id, $instance->network, GridController::getMaxRows()));
        $messagestabds->addHelp('userguide/listings/twitter/dashboard_tweets-touser');
        $messagestab->addDataset($messagestabds);
        $menus["tweets-messages"] = $messagestab;

        $fvalltab = new MenuItem("Favorites", "All your favorites", $twitter_data_tpl);
        $fvalltabds = new Dataset("all_tweets", 'FavoritePostDAO', "getAllFavoritePosts",
        array($instance->network_user_id, 'twitter', 20, "#page_number#", !Session::isLoggedIn()),
        'getAllFavoritePostsIterator',
        array($instance->network_user_id, 'twitter', GridController::getMaxRows()) );
        $fvalltabds->addHelp('userguide/listings/twitter/dashboard_ftweets-all');
        $fvalltab->addDataset($fvalltabds);
        $menus["ftweets-all"] = $fvalltab;

        //Most Active Friends
        $motab = new MenuItem('Chatterboxes', 'People you follow who tweet the most',
        $twitter_data_tpl, 'Who You Follow');
        $motabds = new Dataset('people', 'FollowDAO', "getMostActiveFollowees", array(
        $instance->network_user_id, 'twitter', 15, '#page_number#'));
        $motabds->addHelp('userguide/listings/twitter/dashboard_friends-mostactive');
        $motab->addDataset($motabds);
        $menus["friends-mostactive"] = $motab;

        //Least Active Friends
        $latab = new MenuItem('Deadbeats', 'People you follow who tweet the least', $twitter_data_tpl);
        $latabds = new Dataset("people", 'FollowDAO', "getLeastActiveFollowees", array(
        $instance->network_user_id, 'twitter', 15, '#page_number#'));
        $latabds->addHelp('userguide/listings/twitter/dashboard_friends-leastactive');
        $latab->addDataset($latabds);
        $menus["friends-leastactive"] = $latab;

        //Popular friends
        $poptab = new MenuItem('Popular', 'Most-followed people you follow', $twitter_data_tpl);
        $poptabds = new Dataset("people", 'FollowDAO', "getMostFollowedFollowees", array(
        $instance->network_user_id, 'twitter', 15, '#page_number#'));
        $poptabds->addHelp('userguide/listings/twitter/dashboard_followers-mostfollowed');
        $poptab->addDataset($poptabds);
        $menus["friends-mostfollowed"] = $poptab;

        //Least likely/Most Discerning
        $lltab = new MenuItem("Discerning", 'Followers with the greatest follower-to-friend ratio',
        $twitter_data_tpl, 'Followers');
        $lltabds = new Dataset("people", 'FollowDAO', "getLeastLikelyFollowers", array(
        $instance->network_user_id, 'twitter', 15, '#page_number#'));
        $lltabds->addHelp('userguide/listings/twitter/dashboard_followers-leastlikely');
        $lltab->addDataset($lltabds);
        $menus["followers-leastlikely"] = $lltab;

        //Most followed
        $mftab = new MenuItem('Popular', 'Followers with the most followers',
        $twitter_data_tpl);
        $mftabds = new Dataset("people", 'FollowDAO', "getMostFollowedFollowers", array(
        $instance->network_user_id, 'twitter', 15, '#page_number#'));
        $mftabds->addHelp('userguide/listings/twitter/dashboard_followers-mostfollowed');
        $mftab->addDataset($mftabds);
        $menus["followers-mostfollowed"] =  $mftab;

        //Follower count history
        $follower_history_tpl = Utils::getPluginViewDirectory('twitter').'twitter.followercount.tpl';
        $trendtab = new MenuItem('Count history', 'Your follower count over time',
        $follower_history_tpl);
        $trendtabds = new Dataset("follower_count_history_by_day", 'FollowerCountDAO', 'getHistory',
        array($instance->network_user_id, 'twitter', 'DAY', 15));
        $trendtab->addDataset($trendtabds);
        $trendtabweekds = new Dataset("follower_count_history_by_week", 'FollowerCountDAO', 'getHistory',
        array($instance->network_user_id, 'twitter', 'WEEK', 15));
        $trendtab->addDataset($trendtabweekds);
        $trendtabmonthds = new Dataset("follower_count_history_by_month", 'FollowerCountDAO', 'getHistory',
        array($instance->network_user_id, 'twitter', 'MONTH', 11));
        $trendtabmonthds->addHelp('userguide/listings/twitter/dashboard_followers-history');
        $trendtab->addDataset($trendtabmonthds);
        $menus['followers-history'] = $trendtab;

        //List membership count history
        $group_membership_history_tpl = Utils::getPluginViewDirectory('twitter').'twitter.listmembershipcount.tpl';
        $group_trend_tab = new MenuItem('List stats', 'Your list membership count over time',
        $group_membership_history_tpl);
        $group_trend_tab_ds = new Dataset("list_membership_count_history_by_day", 'GroupMembershipCountDAO',
        'getHistory', array($instance->network_user_id, 'twitter', 'DAY', 15));
        $group_trend_tab->addDataset($group_trend_tab_ds);
        $group_trend_tab_week_ds = new Dataset("list_membership_count_history_by_week", 'GroupMembershipCountDAO',
        'getHistory', array($instance->network_user_id, 'twitter', 'WEEK', 15));
        $group_trend_tab->addDataset($group_trend_tab_week_ds);
        $group_trend_tab_month_ds = new Dataset("list_membership_count_history_by_month", 'GroupMembershipCountDAO',
        'getHistory', array($instance->network_user_id, 'twitter', 'MONTH', 11));
        $group_trend_tab_month_ds->addHelp('userguide/listings/twitter/dashboard_followers-liststats');
        $group_trend_tab->addDataset($group_trend_tab_month_ds);
        $menus['group-membership-history'] = $group_trend_tab;

        if ($rt_plugin_active) {
            $fvdtab = new MenuItem("Favorited by Others", "Favorited by Others", $twitter_data_tpl);
            $ftab2 = new Dataset("all_favd", 'FavoritePostDAO', "getAllFavoritedPosts",
            array($instance->network_user_id, $instance->network, 20, '#page_number#'));
            $fvdtab->addDataset($ftab2);
            $menus["favd-all"] = $fvdtab;
        }

        //Links from favorites
        $lftab = new MenuItem('Links in favorites', 'Links in posts you favorited', $twitter_data_tpl, 'Links');
        $lftabds = new Dataset("links", 'LinkDAO', "getLinksByFavorites",
        array($instance->network_user_id, 'twitter', 15, '#page_number#',!Session::isLoggedIn()));
        $lftabds->addHelp('userguide/listings/twitter/dashboard_links-favorites');
        $lftab->addDataset($lftabds);
        $menus["links-favorites"] = $lftab;

        //Links from friends
        $fltab = new MenuItem('Links by who you follow', 'Links your friends posted', $twitter_data_tpl);
        $fltabds = new Dataset("links", 'LinkDAO', "getLinksByFriends",
        array($instance->network_user_id, 'twitter', 15, '#page_number#',!Session::isLoggedIn()));
        $fltabds->addHelp('userguide/listings/twitter/dashboard_links-friends');
        $fltab->addDataset($fltabds);
        $menus["links-friends"] = $fltab;

        //Photos
        $ptab = new MenuItem("Photos by who you follow", 'Photos your friends have posted', $twitter_data_tpl);
        $ptabds = new Dataset("links", 'LinkDAO', "getPhotosByFriends",
        array($instance->network_user_id, 'twitter', 15, '#page_number#',!Session::isLoggedIn()));
        $ptabds->addHelp('userguide/listings/twitter/dashboard_links-photos');
        $ptab->addDataset($ptabds);
        $menus["links-photos"] = $ptab;

        return $menus;
    }

    /**
     * Defines the ordering of replies in the post page (/post/?t=...)
     *
     * @param $order_by Order by distance ('location') or not ('default')
     * @return string Ordering, to be used in a SQL 'ORDER BY' statement
     */
    public static function repliesOrdering($order_by) {
        if ($order_by == 'location') {
            return "geo_status, reply_retweet_distance, is_reply_by_friend DESC, follower_count DESC";
        } else {
            return "is_reply_by_friend DESC, follower_count DESC";
        }
    }

    /**
     * @param Post $post
     * @return array MenuItems
     */
    public function getPostDetailMenuItems($post) {
        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.post.retweets.tpl';
        $menus = array();

        if ($post->network == 'twitter') {
            $retweets_menu_item = new MenuItem("Retweets", "Retweets of this tweet", $twitter_data_tpl, 'Twitter');
            //if not logged in, show only public retweets
            $retweets_dataset = new Dataset("retweets", 'PostDAO', "getRetweetsOfPost", array($post->post_id,
            'twitter', 'default', 'km', !Session::isLoggedIn()) );
            $retweets_menu_item->addDataset($retweets_dataset);
            $menus['fwds'] = $retweets_menu_item;
            $favd_menu_item = new MenuItem("Favorited", "Those who favorited this tweet", $twitter_data_tpl);
            //if not logged in, show only public fav'd info
            $favd_dataset = new Dataset("favds", 'FavoritePostDAO', "getUsersWhoFavedPost", array($post->post_id,
            'twitter', !Session::isLoggedIn()) );
            $favd_menu_item->addDataset($favd_dataset);
            $menus['favs'] = $favd_menu_item;
        }
        return $menus;
    }

    /**
     * Allocates api call counts to each crawler function
     * @param int $max_api_calls_per_crawl
     * @param bool $noauth
     * @return @array Budget array
     */
    public function budgetCrawlLimits($max_api_calls_per_crawl, $noauth) {
        $budget_array_config = $noauth ? $this->api_budget_allocation_noauth : $this->api_budget_allocation_auth;
        $budget_array = array();
        foreach($budget_array_config as $function_name => $value) {
            $count = intval( $max_api_calls_per_crawl * ($value['percent'] * .01) );
            $budget_array[$function_name] = array('count' => $count, 'remaining' => $count);
        }
        return $budget_array;
    }
}
