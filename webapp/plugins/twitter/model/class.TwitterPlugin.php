<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterPlugin.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TwitterPlugin implements CrawlerPlugin, DashboardPlugin {

    public function crawl() {
        $config = Config::getInstance();
        $logger = Logger::getInstance();
        $id = DAOFactory::getDAO('InstanceDAO');
        $oid = DAOFactory::getDAO('OwnerInstanceDAO');
        $od = DAOFactory::getDAO('OwnerDAO');

        // get oauth values
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('twitter', true);

        $current_owner = $od->getByEmail(Session::getLoggedInUser());

        $instances = $id->getAllActiveInstancesStalestFirstByNetwork('twitter');
        foreach ($instances as $instance) {
            if (!$oid->doesOwnerHaveAccess($current_owner, $instance)) {
                // Owner doesn't have access to this instance; let's not crawl it.
                continue;
            }
            $logger->setUsername($instance->network_username);
            $logger->logUserSuccess("Starting to collect data for ".$instance->network_username." on Twitter.",
            __METHOD__.','.__LINE__);
            $tokens = $oid->getOAuthTokens($instance->id);
            $noauth = true;
            $num_twitter_errors =
            isset($options['num_twitter_errors']) ? $options['num_twitter_errors']->option_value : null;
            $max_api_calls_per_crawl =
            isset($options['max_api_calls_per_crawl']) ? $options['max_api_calls_per_crawl']->option_value : 350;
            if (isset($tokens['oauth_access_token']) && $tokens['oauth_access_token'] != ''
            && isset($tokens['oauth_access_token_secret']) && $tokens['oauth_access_token_secret'] != '') {
                $noauth = false;
            }

            if ($noauth) {
                $api = new CrawlerTwitterAPIAccessorOAuth('NOAUTH', 'NOAUTH',
                $options['oauth_consumer_key']->option_value,
                $options['oauth_consumer_secret']->option_value,
                $instance, $options['archive_limit']->option_value,
                $num_twitter_errors, $max_api_calls_per_crawl);
            } else {
                $api = new CrawlerTwitterAPIAccessorOAuth($tokens['oauth_access_token'],
                $tokens['oauth_access_token_secret'], $options['oauth_consumer_key']->option_value,
                $options['oauth_consumer_secret']->option_value,
                $instance, $options['archive_limit']->option_value,
                $num_twitter_errors, $max_api_calls_per_crawl);
            }

            $crawler = new TwitterCrawler($instance, $api);

            $api->init();

            if ($api->available_api_calls_for_crawler > 0) {

                $id->updateLastRun($instance->id);

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
                    $id->save($instance, $crawler->user->post_count, $logger);
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

    public function getDashboardMenu($instance) {
        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl';
        $menus = array();

        $tweets_menu = new Menu('Tweets');

        //All tab
        $all_mi = new MenuItem("tweets-all", "All Tweets", "All tweets", $twitter_data_tpl);
        $all_mi_ds = new Dataset("all_tweets", 'PostDAO', "getAllPosts", array($instance->network_user_id,
        'twitter', 15, "#page_number#"),
        'getAllPostsIterator', array($instance->network_user_id, 'twitter', GridController::MAX_ROWS) );
        $all_mi->addDataset($all_mi_ds);
        $tweets_menu->addMenuItem($all_mi);

        //Questions
        $q_mi = new MenuItem("tweets-questions", "Inquiries", "Inquiries, or tweets with a question mark in them",
        $twitter_data_tpl);
        $q_mi_ds = new Dataset("all_tweets", 'PostDAO', "getAllQuestionPosts",
        array($instance->network_user_id, 'twitter', 15, "#page_number#"));
        $q_mi->addDataset($q_mi_ds);
        $tweets_menu->addMenuItem($q_mi);

        // Most replied-to
        $mrt_mi = new MenuItem("tweets-mostreplies", "Most replied-to", "Tweets with most replies", $twitter_data_tpl);
        $mrt_mi_ds = new Dataset("most_replied_to_tweets", 'PostDAO', "getMostRepliedToPosts",
        array($instance->network_user_id, 'twitter', 15, '#page_number#'));
        $mrt_mi->addDataset($mrt_mi_ds);
        $tweets_menu->addMenuItem($mrt_mi);

        // Most shared
        $mstab = new MenuItem("tweets-mostretweeted", "Most retweeted", "Most retweeted tweets", $twitter_data_tpl);
        $mstabds = new Dataset("most_retweeted", 'PostDAO', "getMostRetweetedPosts",
        array($instance->network_user_id, 'twitter', 15, '#page_number#'));
        $mstab->addDataset($mstabds);
        $tweets_menu->addMenuItem($mstab);

        array_push($menus, $tweets_menu);

        $replies_menu = new Menu('Replies');

        if (Session::isLoggedIn()) { //show protected tweets
            //All Mentions
            $amtab = new MenuItem("mentions-all", "All Mentions", "Any post that mentions you", $twitter_data_tpl);
            $amtabds1 = new Dataset("all_tweets", 'PostDAO', "getAllPosts", array($instance->network_user_id,
           'twitter', 15), "getAllMentionsIterator", array($instance->network_username, GridController::MAX_ROWS, 
           'twitter'));
            $amtabds2 = new Dataset("all_mentions", 'PostDAO', "getAllMentions",
            array($instance->network_username, 15, $instance->network, '#page_number#'));
            $amtab->addDataset($amtabds1);
            $amtab->addDataset($amtabds2);
            $replies_menu->addMenuItem($amtab);

            //All Replies
            $artab = new MenuItem("mentions-allreplies", "Replies",
           "Posts that directly reply to you (i.e., start with your name)", $twitter_data_tpl);
            $artabds = new Dataset("all_replies", 'PostDAO', "getAllReplies",
            array($instance->network_user_id, 'twitter', 15));
            $artab->addDataset($artabds);
            $replies_menu->addMenuItem($artab);

            //All Orphan Mentions
            $omtab = new MenuItem("mentions-orphan", "Not Replies or Forwards",
            "Mentions that are not associated with a specific post", $twitter_data_tpl);
            $omtabds1 = new Dataset("all_tweets", 'PostDAO',
            "getAllPosts", array($instance->network_user_id, 'twitter', 15));
            $omtabds2 = new Dataset("orphan_replies", 'PostDAO', "getOrphanReplies",
            array($instance->network_username, 5, $instance->network));
            $omtab->addDataset($omtabds1);
            $omtab->addDataset($omtabds2);
            $replies_menu->addMenuItem($omtab);

            //All Mentions Standalone
            $sttab = new MenuItem("mentions-standalone", "Standalone Mentions",
            "Mentions you have marked as standalone", $twitter_data_tpl);
            $sttabds1 = new Dataset("standalone_replies", 'PostDAO', "getStandaloneReplies",
            array($instance->network_username, 'twitter', 15));
            $sttabds2 = new Dataset("all_tweets", 'PostDAO', "getAllPosts", array($instance->network_user_id,
            'twitter', 15));
            $sttab->addDataset($sttabds1);
            $sttab->addDataset($sttabds2);
            $replies_menu->addMenuItem($sttab);
        } else {
            //All public mentions
            $amtab = new MenuItem("mentions-all", "All Mentions", "Any post that mentions you", $twitter_data_tpl);
            $amtabds2 = new Dataset("all_mentions", 'PostDAO', "getAllMentions",
            array($instance->network_username, 15, $instance->network, '#page_number#', true));
            $amtab->addDataset($amtabds2);
            $replies_menu->addMenuItem($amtab);
        }

        // Conversations
        $convotab = new MenuItem("tweets-convo", "Conversations", "Exchanges between you and other users",
        $twitter_data_tpl);
        $convotabds = new Dataset("author_replies", 'PostDAO', "getPostsAuthorHasRepliedTo",
        array($instance->network_user_id, 15, 'twitter', '#page_number#'));
        $convotab->addDataset($convotabds);
        $replies_menu->addMenuItem($convotab);

        array_push($menus, $replies_menu);

        $friends_menu = new Menu('Who You Follow');

        //Most Active Friends
        $motab = new MenuItem("friends-mostactive", 'Chatterboxes', '', $twitter_data_tpl);
        $motabds = new Dataset('people', 'FollowDAO', "getMostActiveFollowees", array(
        $instance->network_user_id, 'twitter', 15));
        $motab->addDataset($motabds);
        $friends_menu->addMenuItem($motab);

        //Least Active Friends
        $latab = new MenuItem("friends-leastactive", 'Deadbeats', '', $twitter_data_tpl);
        $latabds = new Dataset("people", 'FollowDAO', "getLeastActiveFollowees", array(
        $instance->network_user_id, 'twitter', 15));
        $latab->addDataset($latabds);
        $friends_menu->addMenuItem($latab);

        //Popular friends
        $poptab = new MenuItem("friends-mostfollowed", 'Popular', '', $twitter_data_tpl);
        $poptabds = new Dataset("people", 'FollowDAO', "getMostFollowedFollowees", array(
        $instance->network_user_id, 'twitter', 15));
        $poptab->addDataset($poptabds);
        $friends_menu->addMenuItem($poptab);

        if (Session::isLoggedIn()) {
            //Former Friends
            $fftab = new MenuItem("friends-former", "Former", '', $twitter_data_tpl);
            $fftabds = new Dataset("people", 'FollowDAO', "getFormerFollowees", array(
            $instance->network_user_id, 'twitter', 15));
            $fftab->addDataset($fftabds);
            $friends_menu->addMenuItem($fftab);

            //Not Mutual Friends
            $nmtab = new MenuItem("friends-notmutual", "Not Mutual", '', $twitter_data_tpl);
            $nmtabds = new Dataset("people", 'FollowDAO', "getFriendsNotFollowingBack", array(
        'twitter', $instance->network_user_id));
            $nmtab->addDataset($nmtabds);
            $friends_menu->addMenuItem($nmtab);
        }

        array_push($menus, $friends_menu);

        $followers_menu = new Menu('Followers');

        //Follower count history
        $follower_history_tpl = Utils::getPluginViewDirectory('twitter').'twitter.followercount.tpl';
        $trendtab = new MenuItem('followers-history', 'Follower Count', 'Your follower count over time',
        $follower_history_tpl);
        $trendtabds = new Dataset("historybyday", 'FollowerCountDAO', 'getHistory',
        array($instance->network_user_id, 'twitter', 'DAY', 20));
        $trendtab->addDataset($trendtabds);
        $trendtabweekds = new Dataset("historybyweek", 'FollowerCountDAO', 'getHistory',
        array($instance->network_user_id, 'twitter', 'WEEK', 20));
        $trendtab->addDataset($trendtabweekds);
        $trendtabmonthds = new Dataset("historybymonth", 'FollowerCountDAO', 'getHistory',
        array($instance->network_user_id, 'twitter', 'MONTH', 20));
        $trendtab->addDataset($trendtabmonthds);
        $followers_menu->addMenuItem($trendtab);

        //Most followed
        $mftab = new MenuItem("followers-mostfollowed", 'Most-followed', 'Followers with most followers',
        $twitter_data_tpl);
        $mftabds = new Dataset("people", 'FollowDAO', "getMostFollowedFollowers", array(
        $instance->network_user_id, 'twitter', 15));
        $mftab->addDataset($mftabds);
        $followers_menu->addMenuItem( $mftab);

        //Least likely
        $lltab = new MenuItem("followers-leastlikely", "Least Likely",
        'Followers with the greatest follower-to-friend ratio', $twitter_data_tpl);
        $lltabds = new Dataset("people", 'FollowDAO', "getLeastLikelyFollowers", array(
        $instance->network_user_id, 'twitter', 15));
        $lltab->addDataset($lltabds);
        $followers_menu->addMenuItem($lltab);

        if (Session::isLoggedIn()) {
            //Former followers
            $fftab = new MenuItem("followers-former", "Former", '', $twitter_data_tpl);
            $fftabds = new Dataset("people", 'FollowDAO', "getFormerFollowers", array(
            $instance->network_user_id, 'twitter', 15));
            $fftab->addDataset($fftabds);
            $followers_menu->addMenuItem($fftab);
        }

        //Earliest
        $eftab = new MenuItem("followers-earliest", "Earliest Joiners", '', $twitter_data_tpl);
        $eftabds = new Dataset("people", 'FollowDAO', "getEarliestJoinerFollowers", array(
        $instance->network_user_id, 'twitter', 15));
        $eftab->addDataset($eftabds);
        $followers_menu->addMenuItem($eftab);

        array_push($menus, $followers_menu);

        $favorites_menu = new Menu('Favorites');
        $fvalltab = new MenuItem("ftweets-all", "All", "All favorites", $twitter_data_tpl);
        $fvalltabds = new Dataset("all_tweets", 'FavoritePostDAO', "getAllFavoritePosts", array($instance->network_user_id,
           'twitter', 20, "#page_number#"),
           'getAllFavoritePostsIterator', array($instance->network_user_id, 'twitter', GridController::MAX_ROWS)
        );
        $fvalltab->addDataset($fvalltabds);
        $favorites_menu->addMenuItem($fvalltab);
        array_push($menus, $favorites_menu);

        $links_menu = new Menu('Links');

        //Links from friends
        $fltab = new MenuItem("links-friends", 'Links from People You Follow', 'Links your friends posted',
        $twitter_data_tpl);
        $fltabds = new Dataset("links", 'LinkDAO', "getLinksByFriends", array($instance->network_user_id,
        'twitter'));
        $fltab->addDataset($fltabds);
        $links_menu->addMenuItem($fltab);

        //Links from favorites
        $lftab = new MenuItem("links-favorites", 'Links From Favorites', 'Links in posts you favorited',
        $twitter_data_tpl);
        $lftabds = new Dataset("links", 'LinkDAO', "getLinksByFavorites", array($instance->network_user_id, 'twitter'));
        $lftab->addDataset($lftabds);
        $links_menu->addMenuItem($lftab);

        //Photos
        $ptab = new MenuItem("links-photos", "Photos from People You Follow", 'Photos your friends have posted',
        $twitter_data_tpl);
        $ptabds = new Dataset("links", 'LinkDAO', "getPhotosByFriends", array($instance->network_user_id,
        'twitter'));
        $ptab->addDataset($ptabds);
        $links_menu->addMenuItem($ptab);

        array_push($menus, $links_menu);

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
}
