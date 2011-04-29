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
class TwitterPlugin implements CrawlerPlugin, DashboardPlugin, PostDetailPlugin {

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

            $crawler = new TwitterCrawler($instance, $api);

            $api->init();

            if ($api->available_api_calls_for_crawler > 0) {

                $instance_dao->updateLastRun($instance->id);

                // No auth req'd
                //$crawler->fetchInstanceUserInfo();

                // No auth for public Twitter users
                $crawler->fetchInstanceUserTweets();

                if (!$noauth) {
                    // Auth req'd, for calling user only
                    $crawler->fetchInstanceUserMentions();
                    $crawler->fetchInstanceUserFriends();
                    $crawler->fetchInstanceFavorites();
                    $crawler->fetchInstanceUserFollowers();
                    $crawler->fetchRetweetsOfInstanceUser();
                    $crawler->cleanUpMissedFavsUnFavs();
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
        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl';
        $menus = array();

        //All tab
        $all_mi = new MenuItem("All Tweets", "All tweets", $twitter_data_tpl, "Tweets");
        $all_mi_ds = new Dataset("all_tweets", 'PostDAO', "getAllPosts", array($instance->network_user_id,
        'twitter', 15, "#page_number#"), 'getAllPostsIterator', array($instance->network_user_id, 'twitter', 
        GridController::MAX_ROWS) );
        $all_mi->addDataset($all_mi_ds);
        $menus['tweets-all'] = $all_mi;

        //Questions
        $q_mi = new MenuItem("Inquiries", "Inquiries, or tweets with a question mark in them",
        $twitter_data_tpl);
        $q_mi_ds = new Dataset("all_tweets", 'PostDAO', "getAllQuestionPosts",
        array($instance->network_user_id, 'twitter', 15, "#page_number#"));
        $q_mi->addDataset($q_mi_ds);
        $menus['tweets-questions'] = $q_mi;

        // Most replied-to
        $mrt_mi = new MenuItem("Most replied-to", "Tweets with most replies", $twitter_data_tpl);
        $mrt_mi_ds = new Dataset("most_replied_to_tweets", 'PostDAO', "getMostRepliedToPosts",
        array($instance->network_user_id, 'twitter', 15, '#page_number#'));
        $mrt_mi->addDataset($mrt_mi_ds);
        $menus['tweets-mostreplies'] = $mrt_mi;

        // Most shared
        $mstab = new MenuItem("Most retweeted", "Most retweeted tweets", $twitter_data_tpl);
        $mstabds = new Dataset("most_retweeted", 'PostDAO', "getMostRetweetedPosts",
        array($instance->network_user_id, 'twitter', 15, '#page_number#'));
        $mstab->addDataset($mstabds);
        $menus["tweets-mostretweeted"] = $mstab;

        if (Session::isLoggedIn()) { //show protected tweets
            //All Mentions
            $amtab = new MenuItem("All Mentions", "Any post that mentions you", $twitter_data_tpl, 'Replies');
            $amtabds1 = new Dataset("all_tweets", 'PostDAO', "getAllPosts", array($instance->network_user_id,
           'twitter', 15), "getAllMentionsIterator", array($instance->network_username, GridController::MAX_ROWS, 
           'twitter'));
            $amtabds2 = new Dataset("all_mentions", 'PostDAO', "getAllMentions",
            array($instance->network_username, 15, $instance->network, '#page_number#'));
            $amtab->addDataset($amtabds1);
            $amtab->addDataset($amtabds2);
            $menus["mentions-all"] = $amtab;

            //All Replies
            $artab = new MenuItem("Replies",
           "Posts that directly reply to you (i.e., start with your name)", $twitter_data_tpl);
            $artabds = new Dataset("all_replies", 'PostDAO', "getAllReplies",
            array($instance->network_user_id, 'twitter', 15));
            $artab->addDataset($artabds);
            $menus["mentions-allreplies"] = $artab;

            //All Orphan Mentions
            $omtab = new MenuItem("Not Replies or Forwards",
            "Mentions that are not associated with a specific post", $twitter_data_tpl);
            $omtabds1 = new Dataset("all_tweets", 'PostDAO',
            "getAllPosts", array($instance->network_user_id, 'twitter', 15));
            $omtabds2 = new Dataset("orphan_replies", 'PostDAO', "getOrphanReplies",
            array($instance->network_username, 5, $instance->network));
            $omtab->addDataset($omtabds1);
            $omtab->addDataset($omtabds2);
            $menus["mentions-orphan"] = $omtab;

            //All Mentions Standalone
            $sttab = new MenuItem("Standalone Mentions",
            "Mentions you have marked as standalone", $twitter_data_tpl);
            $sttabds1 = new Dataset("standalone_replies", 'PostDAO', "getStandaloneReplies",
            array($instance->network_username, 'twitter', 15));
            $sttabds2 = new Dataset("all_tweets", 'PostDAO', "getAllPosts", array($instance->network_user_id,
            'twitter', 15));
            $sttab->addDataset($sttabds1);
            $sttab->addDataset($sttabds2);
            $menus["mentions-standalone"] = $sttab;
        } else {
            //All public mentions
            $amtab = new MenuItem("All Mentions", "Any post that mentions you", $twitter_data_tpl, 'Replies');
            $amtabds2 = new Dataset("all_mentions", 'PostDAO', "getAllMentions",
            array($instance->network_username, 15, $instance->network, '#page_number#', true));
            $amtab->addDataset($amtabds2);
            $menus["mentions-all"] = $amtab;
        }

        // Conversations
        $convotab = new MenuItem("Conversations", "Exchanges between you and other users", $twitter_data_tpl);
        $convotabds = new Dataset("author_replies", 'PostDAO', "getPostsAuthorHasRepliedTo",
        array($instance->network_user_id, 15, 'twitter', !Session::isLoggedIn(), '#page_number#'));
        $convotabds->addHelp('userguide/listings/twitter/dashboard_tweets-convo');
        $convotab->addDataset($convotabds);
        $menus["tweets-convo"] = $convotab;

        //Most Active Friends
        $motab = new MenuItem('Chatterboxes', '', $twitter_data_tpl, 'Who You Follow');
        $motabds = new Dataset('people', 'FollowDAO', "getMostActiveFollowees", array(
        $instance->network_user_id, 'twitter', 15));
        $motab->addDataset($motabds);
        $menus["friends-mostactive"] = $motab;

        //Least Active Friends
        $latab = new MenuItem('Deadbeats', '', $twitter_data_tpl);
        $latabds = new Dataset("people", 'FollowDAO', "getLeastActiveFollowees", array(
        $instance->network_user_id, 'twitter', 15));
        $latab->addDataset($latabds);
        $menus["friends-leastactive"] = $latab;

        //Popular friends
        $poptab = new MenuItem('Popular', '', $twitter_data_tpl);
        $poptabds = new Dataset("people", 'FollowDAO', "getMostFollowedFollowees", array(
        $instance->network_user_id, 'twitter', 15));
        $poptab->addDataset($poptabds);
        $menus["friends-mostfollowed"] = $poptab;

        //Follower count history
        $follower_history_tpl = Utils::getPluginViewDirectory('twitter').'twitter.followercount.tpl';
        $trendtab = new MenuItem('Follower Count', 'Your follower count over time',
        $follower_history_tpl, 'Followers');
        $trendtabds = new Dataset("follower_count_history_by_day", 'FollowerCountDAO', 'getHistory',
        array($instance->network_user_id, 'twitter', 'DAY', 15));
        $trendtab->addDataset($trendtabds);
        $trendtabweekds = new Dataset("follower_count_history_by_week", 'FollowerCountDAO', 'getHistory',
        array($instance->network_user_id, 'twitter', 'WEEK', 15));
        $trendtab->addDataset($trendtabweekds);
        $trendtabmonthds = new Dataset("follower_count_history_by_month", 'FollowerCountDAO', 'getHistory',
        array($instance->network_user_id, 'twitter', 'MONTH', 11));
        $trendtab->addDataset($trendtabmonthds);
        $menus['followers-history'] = $trendtab;

        //Most followed
        $mftab = new MenuItem('Most-followed', 'Followers with most followers',
        $twitter_data_tpl);
        $mftabds = new Dataset("people", 'FollowDAO', "getMostFollowedFollowers", array(
        $instance->network_user_id, 'twitter', 15));
        $mftab->addDataset($mftabds);
        $menus["followers-mostfollowed"] =  $mftab;

        //Least likely/Most Discerning
        $lltab = new MenuItem("Most Discerning", 'Followers with the greatest follower-to-friend ratio',
        $twitter_data_tpl);
        $lltabds = new Dataset("people", 'FollowDAO', "getLeastLikelyFollowers", array(
        $instance->network_user_id, 'twitter', 15));
        $lltab->addDataset($lltabds);
        $menus["followers-leastlikely"] = $lltab;

        //Earliest
        $eftab = new MenuItem("Earliest Joiners", '', $twitter_data_tpl);
        $eftabds = new Dataset("people", 'FollowDAO', "getEarliestJoinerFollowers", array(
        $instance->network_user_id, 'twitter', 15));
        $eftab->addDataset($eftabds);
        $menus["followers-earliest"] = $eftab;

        $fvalltab = new MenuItem("All", "All favorites", $twitter_data_tpl, 'Favorites');
        $fvalltabds = new Dataset("all_tweets", 'FavoritePostDAO', "getAllFavoritePosts",
        array($instance->network_user_id, 'twitter', 20, "#page_number#"), 'getAllFavoritePostsIterator',
        array($instance->network_user_id, 'twitter', GridController::MAX_ROWS) );
        $fvalltab->addDataset($fvalltabds);
        $menus["ftweets-all"] = $fvalltab;

        //Links from friends
        $fltab = new MenuItem('Links from People You Follow', 'Links your friends posted', $twitter_data_tpl, 'Links');
        $fltabds = new Dataset("links", 'LinkDAO', "getLinksByFriends",
        array($instance->network_user_id, 'twitter', 15, '#page_number#'));
        $fltab->addDataset($fltabds);
        $menus["links-friends"] = $fltab;

        //Links from favorites
        $lftab = new MenuItem('Links From Favorites', 'Links in posts you favorited', $twitter_data_tpl);
        $lftabds = new Dataset("links", 'LinkDAO', "getLinksByFavorites",
        array($instance->network_user_id, 'twitter', 15, '#page_number#'));
        $lftab->addDataset($lftabds);
        $menus["links-favorites"] = $lftab;

        //Photos
        $ptab = new MenuItem("Photos from People You Follow", 'Photos your friends have posted', $twitter_data_tpl);
        $ptabds = new Dataset("links", 'LinkDAO', "getPhotosByFriends",
        array($instance->network_user_id, 'twitter', 15, '#page_number#'));
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
        }
        return $menus;
    }
}
