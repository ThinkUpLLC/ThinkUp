<?php
/**
 * Twitter Plugin
 *
 * the Twitter crawler and webapp plugin which retrieves data from Twitter and displays it
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TwitterPlugin implements CrawlerPlugin, WebappPlugin {

    public function crawl() {
        global $db;//@TODO Remove this when PDO port is complete
        global $conn;

        $config = Config::getInstance();
        $logger = Logger::getInstance();
        $id = DAOFactory::getDAO('InstanceDAO');
        $oid = new OwnerInstanceDAO($db, $logger);

        $instances = $id->getAllActiveInstancesStalestFirstByNetwork('twitter');
        foreach ($instances as $instance) {
            $logger->setUsername($instance->network_username);
            $tokens = $oid->getOAuthTokens($instance->id);
            $noauth = true;

            if (isset($tokens['oauth_access_token']) && $tokens['oauth_access_token'] != ''
            && isset($tokens['oauth_access_token_secret']) && $tokens['oauth_access_token_secret'] != '') {
                $noauth = false;
            }

            if ($noauth) {
                $api = new CrawlerTwitterAPIAccessorOAuth('NOAUTH', 'NOAUTH', $config->getValue('oauth_consumer_key'),
                $config->getValue('oauth_consumer_secret'), $instance, $config->getValue('archive_limit'));
            } else {
                $api = new CrawlerTwitterAPIAccessorOAuth($tokens['oauth_access_token'],
                $tokens['oauth_access_token_secret'], $config->getValue('oauth_consumer_key'),
                $config->getValue('oauth_consumer_secret'), $instance, $config->getValue('archive_limit'));
            }

            $crawler = new TwitterCrawler($instance, $api);

            $api->init();

            if ($api->available_api_calls_for_crawler > 0) {

                $id->updateLastRun($instance->id);

                // No auth req'd
                $crawler->fetchInstanceUserInfo();

                // No auth for public Twitter users
                $crawler->fetchInstanceUserTweets();

                if (!$noauth) {
                    // Auth req'd, for calling user only
                    $crawler->fetchInstanceUserMentions();
                    $crawler->fetchRetweetsOfInstanceUser();
                    $crawler->fetchInstanceUserFriends();
                    $crawler->fetchInstanceUserFollowers();
                }

                $crawler->fetchStrayRepliedToTweets();
                $crawler->fetchUnloadedFollowerDetails();
                $crawler->fetchFriendTweetsAndFriends();

                // TODO: Get direct messages
                // TODO: Gather favorites data

                if ($noauth) {
                    // No auth req'd
                    $crawler->fetchSearchResults($instance->network_username);
                }

                $crawler->cleanUpFollows();

                // Save instance
                $id->save($crawler->instance, $crawler->owner_object->post_count, $logger, $api);
            }
        }

        $logger->close(); # Close logging
    }

    public function renderConfiguration($owner) {
        $controller = new TwitterPluginConfigurationController($owner);
        return $controller->go();
    }

    public function getChildTabsUnderPosts($instance) {
        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl';

        $child_tabs = array();

        //All tab
        $alltab = new WebappTab("tweets-all", "All", "All tweets", $twitter_data_tpl);
        $alltabds = new WebappTabDataset("all_tweets", 'PostDAO', "getAllPosts", array($instance->network_user_id,
        15));
        $alltab->addDataset($alltabds);
        array_push($child_tabs, $alltab);

        // Most replied-to tab
        $mrttab = new WebappTab("tweets-mostreplies", "Most replied-to", "Tweets with most replies", $twitter_data_tpl);
        $mrttabds = new WebappTabDataset("most_replied_to_tweets", 'PostDAO', "getMostRepliedToPosts",
        array($instance->network_user_id, 15));
        $mrttab->addDataset($mrttabds);
        array_push($child_tabs, $mrttab);

        // Most shared tab
        $mstab = new WebappTab("tweets-mostretweeted", "Most retweeted", "Most retweeted tweets", $twitter_data_tpl);
        $mstabds = new WebappTabDataset("most_retweeted", 'PostDAO', "getMostRetweetedPosts",
        array($instance->network_user_id, 15));
        $mstab->addDataset($mstabds);
        array_push($child_tabs, $mstab);

        // Conversations
        $convotab = new WebappTab("tweets-convo", "Conversations", "", $twitter_data_tpl);
        $convotabds = new WebappTabDataset("author_replies", 'PostDAO', "getPostsAuthorHasRepliedTo",
        array($instance->network_user_id, 15));
        $convotab->addDataset($convotabds);
        array_push($child_tabs, $convotab);

        return $child_tabs;
    }

    public function getChildTabsUnderReplies($instance) {
        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl';

        $child_tabs = array();

        //All Mentions
        $amtab = new WebappTab("mentions-all", "All Mentions", "Any post that mentions you", $twitter_data_tpl);
        $amtabds1 = new WebappTabDataset("all_tweets", 'PostDAO', "getAllPosts", array($instance->network_user_id,
        15));
        $amtabds2 = new WebappTabDataset("all_mentions", 'PostDAO', "getAllMentions",
        array($instance->network_username, 15, $instance->network));
        $amtab->addDataset($amtabds1);
        $amtab->addDataset($amtabds2);
        array_push($child_tabs, $amtab);

        //All Replies
        $artab = new WebappTab("mentions-allreplies", "Replies",
        "Posts that directly reply to you (i.e., start with your name)", $twitter_data_tpl);
        $artabds = new WebappTabDataset("all_replies", 'PostDAO', "getAllReplies",
        array($instance->network_user_id, 15));
        $artab->addDataset($artabds);
        array_push($child_tabs, $artab);

        //All Orphan Mentions
        $omtab = new WebappTab("mentions-orphan", "Not Replies or Forwards",
        "Mentions that are not associated with a specific post", $twitter_data_tpl);
        $omtabds1 = new WebappTabDataset("all_tweets", 'PostDAO',
        "getAllPosts", array($instance->network_user_id, 15));
        $omtabds2 = new WebappTabDataset("orphan_replies", 'PostDAO', "getOrphanReplies",
        array($instance->network_username, 5, $instance->network));
        $omtab->addDataset($omtabds1);
        $omtab->addDataset($omtabds2);
        array_push($child_tabs, $omtab);

        //All Mentions Standalone
        $sttab = new WebappTab("mentions-standalone", "Standalone Mentions", "Mentions you have marked as standalone",
        $twitter_data_tpl);
        $sttabds1 = new WebappTabDataset("standalone_replies", 'PostDAO', "getStandaloneReplies",
        array($instance->network_username, 15));
        $sttabds2 = new WebappTabDataset("all_tweets", 'PostDAO', "getAllPosts", array($instance->network_user_id,
        15));
        $sttab->addDataset($sttabds1);
        $sttab->addDataset($sttabds2);
        array_push($child_tabs, $sttab);

        return $child_tabs;
    }

    public function getChildTabsUnderFriends($instance) {
        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl';

        $child_tabs = array();

        //Most Active Friends
        $motab = new WebappTab("friends-mostactive", 'Chatterboxes', '', $twitter_data_tpl);
        $motabds = new WebappTabDataset('people', 'FollowDAO', "getMostActiveFollowees", array($instance->network_user_id, 15));
        $motab->addDataset($motabds);
        array_push($child_tabs, $motab);

        //Least Active Friends
        $latab = new WebappTab("friends-leastactive", 'Deadbeats', '', $twitter_data_tpl);
        $latabds = new WebappTabDataset("people", 'FollowDAO', "getLeastActiveFollowees", array($instance->network_user_id,
        15));
        $latab->addDataset($latabds);
        array_push($child_tabs, $latab);

        //Popular friends
        $poptab = new WebappTab("friends-mostfollowed", 'Popular', '', $twitter_data_tpl);
        $poptabds = new WebappTabDataset("people", 'FollowDAO', "getMostFollowedFollowees", array($instance->network_user_id,
        15));
        $poptab->addDataset($poptabds);
        array_push($child_tabs, $poptab);

        //Former Friends
        $fftab = new WebappTab("friends-former", "Former", '', $twitter_data_tpl);
        $fftabds = new WebappTabDataset("people", 'FollowDAO', "getFormerFollowees", array($instance->network_user_id, 15));
        $fftab->addDataset($fftabds);
        array_push($child_tabs, $fftab);

        //Not Mutual Friends
        $nmtab = new WebappTab("friends-notmutual", "Not Mutual", '', $twitter_data_tpl);
        $nmtabds = new WebappTabDataset("people", 'FollowDAO', "getFriendsNotFollowingBack", array($instance->network_user_id));
        $nmtab->addDataset($nmtabds);
        array_push($child_tabs, $nmtab);

        return $child_tabs;
    }

    public function getChildTabsUnderFollowers($instance) {
        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl';

        $child_tabs = array();

        //Most followed
        $mftab = new WebappTab("followers-mostfollowed", 'Most-followed', 'Followers with most followers',
        $twitter_data_tpl);
        $mftabds = new WebappTabDataset("people", 'FollowDAO', "getMostFollowedFollowers", array($instance->network_user_id,
        15));
        $mftab->addDataset($mftabds);
        array_push($child_tabs, $mftab);

        //Least likely
        $lltab = new WebappTab("followers-leastlikely", "Least Likely",
        'Followers with the greatest follower-to-friend ratio', $twitter_data_tpl);
        $lltabds = new WebappTabDataset("people", 'FollowDAO', "getLeastLikelyFollowers", array($instance->network_user_id,
        15));
        $lltab->addDataset($lltabds);
        array_push($child_tabs, $lltab);

        //Former followers
        $fftab = new WebappTab("followers-former", "Former", '', $twitter_data_tpl);
        $fftabds = new WebappTabDataset("people", 'FollowDAO', "getFormerFollowers", array($instance->network_user_id, 15));
        $fftab->addDataset($fftabds);
        array_push($child_tabs, $fftab);

        //Earliest
        $eftab = new WebappTab("followers-earliest", "Earliest Joiners", '', $twitter_data_tpl);
        $eftabds = new WebappTabDataset("people", 'FollowDAO', "getEarliestJoinerFollowers", array($instance->network_user_id,
        15));
        $eftab->addDataset($eftabds);
        array_push($child_tabs, $eftab);

        return $child_tabs;
    }

    public function getChildTabsUnderLinks($instance) {
        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl';

        $child_tabs = array();

        //Links from friends
        $fltab = new WebappTab("links-friends", 'Links From Friends', 'Links your friends posted', $twitter_data_tpl);
        $fltabds = new WebappTabDataset("links", 'LinkDAO', "getLinksByFriends", array($instance->network_user_id));
        $fltab->addDataset($fltabds);
        array_push($child_tabs, $fltab);

        //Links from favorites
        /* $lftab = new WebappTab("links-favorites", 'Links From Favorites', 'Links in posts you favorited');
        $lftabds = new WebappTabDataset("links", 'LinkDAO', "getLinksByFriends", array($instance->network_user_id));
        $lftab->addDataset($lftabds);
        array_push($child_tabs, $lftab);
        */
        //Photos
        $ptab = new WebappTab("links-photos", "Photos", 'Photos your friends have posted', $twitter_data_tpl);
        $ptabds = new WebappTabDataset("links", 'LinkDAO', "getPhotosByFriends", array($instance->network_user_id));
        array_push($child_tabs, $ptab);

        return $child_tabs;
    }
}
