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
    /*
     * @var PostDAO
     */
    protected $post_dao;

    public function __construct() {
        global $db; //@TODO remove this when PDO port is complete
        $this->post_dao = new PostDAO($db);
    }

    public function crawl() {
        global $db;
        global $conn;

        $config = Config::getInstance();
        $logger = Logger::getInstance();
        $id = DAOFactory::getDAO('InstanceDAO');
        $oid = new OwnerInstanceDAO($db, $logger);

        $instances = $id->getAllActiveInstancesStalestFirstByNetwork('twitter');
        foreach ($instances as $i) {
            $logger->setUsername($instance->network_username);
            $tokens = $oid->getOAuthTokens($instance->id);
            $noauth = true;

            if (isset($tokens['oauth_access_token']) && $tokens['oauth_access_token'] != '' && isset($tokens['oauth_access_token_secret']) && $tokens['oauth_access_token_secret'] != '') {
                $noauth = false;
            }

            if ($noauth) {
                $api = new CrawlerTwitterAPIAccessorOAuth('NOAUTH', 'NOAUTH', $config->getValue('oauth_consumer_key'), $config->getValue('oauth_consumer_secret'), $i, $config->getValue('archive_limit'));
            } else {
                $api = new CrawlerTwitterAPIAccessorOAuth($tokens['oauth_access_token'], $tokens['oauth_access_token_secret'], $config->getValue('oauth_consumer_key'), $config->getValue('oauth_consumer_secret'), $i, $config->getValue('archive_limit'));
            }

            $crawler = new TwitterCrawler($i, $api, $db);

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


    public function renderConfiguration() {
        global $s;
        global $od;
        global $id;
        global $db;
        global $config;
        global $owner;

        $oauth_consumer_key = $config->getValue('oauth_consumer_key');
        $oauth_consumer_secret = $config->getValue('oauth_consumer_secret');

        //Add public user instance
        if (isset($_GET['twitter_username'])) { // if form was submitted
            $logger = Logger::getInstance();

            //Check user exists and is public
            $api = new TwitterAPIAccessorOAuth('NOAUTH', 'NOAUTH', $oauth_consumer_key, $oauth_consumer_secret, $config->getValue('archive_limit'));
            $api_call = str_replace("[id]", $_GET['twitter_username'], $api->cURL_source['show_user']);
            list($cURL_status, $data) = $api->apiRequestFromWebapp($api_call);
            if ($cURL_status == 200) {
                $thisFeed = array();
                try {
                    $xml = $api->createParserFromString(utf8_encode($data));
                    $user = array('user_id'=>$xml->id, 'user_name'=>$xml->screen_name, 'is_protected'=>$xml->protected );
                }
                catch(Exception $e) {
                    $s->assign('errormsg', $e->getMessage());
                }
                if (isset($user) && $user["is_protected"] == 'false') {
                    // if so, add to instances table and owners table

                    $i = $id->getByUsername($_GET['twitter_username']);
                    $oid = new OwnerInstanceDAO($db);

                    $msg = '';
                    if (isset($i)) {
                        //$msg .= "Instance already exists.<br />";

                        $oi = $oid->get($owner->id, $instance->id);
                        if ($oi != null) {
                            //$msg .= "Owner already has this instance, no insert or update required.<br />";
                        } else {
                            $oid->insert($owner->id, $instance->id, '', '');
                            //$msg .= "Added owner instance.<br />";
                        }

                    } else {
                        //$msg .= "Instance does not exist.<br />";

                        $id->insert($user["user_id"], $user["user_name"]);
                        //$msg .= "Created instance.<br />";

                        $i = $id->getByUsername($user["user_name"]);
                        $oid->insert($owner->id, $instance->id, '', '');
                        //$msg .= "Created an owner instance.<br />";
                    }
                    $s->assign('successmsg', $_GET['twitter_username']." has been added to ThinkTank.");

                    $s->assign('successmsg', "Added ".$_GET['twitter_username']." to ThinkTank.");
                } else { // if not, return error
                    $s->assign('errormsg', $_GET['twitter_username']." is a private Twitter account; ThinkTank cannot track it without authorization.");
                }
            } else {
                $s->assign('errormsg', $_GET['twitter_username']." is not a valid Twitter username.");
            }
        }

        $to = new TwitterOAuth($oauth_consumer_key, $oauth_consumer_secret);
        /* Request tokens from twitter */
        $tok = $to->getRequestToken();
        if (isset($tok['oauth_token'])) {
            $token = $tok['oauth_token'];
            $_SESSION['oauth_request_token_secret'] = $tok['oauth_token_secret'];

            /* Build the authorization URL */
            $oauthorize_link = $to->getAuthorizeURL($token);
        } else {
            //set error message here
            $s->assign('errormsg', "Unable to obtain OAuth token. Check your Twitter consumer key and secret configuration.");
            $oauthorize_link = '';
        }

        $owner_instances = $id->getByOwnerAndNetwork($owner, 'twitter');

        $s->assign('owner_instances', $owner_instances);
        $s->assign('oauthorize_link', $oauthorize_link);
    }

    public function getChildTabsUnderPosts($instance) {
        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl';

        $child_tabs = array();

        //All tab
        $alltab = new WebappTab("tweets-all", "All", "All tweets", $twitter_data_tpl);
        $alltabds = new WebappTabDataset("all_tweets", $this->post_dao, "getAllPosts", array($instance->network_user_id, 15));
        $alltab->addDataset($alltabds);
        array_push($child_tabs, $alltab);

        // Most replied-to tab
        $mrttab = new WebappTab("tweets-mostreplies", "Most replied-to", "Tweets with most replies", $twitter_data_tpl);
        $mrttabds = new WebappTabDataset("most_replied_to_tweets", $this->post_dao, "getMostRepliedToPosts", array($instance->network_user_id, 15));
        $mrttab->addDataset($mrttabds);
        array_push($child_tabs, $mrttab);

        // Most shared tab
        $mstab = new WebappTab("tweets-mostretweeted", "Most retweeted", "Most retweeted tweets", $twitter_data_tpl);
        $mstabds = new WebappTabDataset("most_retweeted", $this->post_dao, "getMostRetweetedPosts", array($instance->network_user_id, 15));
        $mstab->addDataset($mstabds);
        array_push($child_tabs, $mstab);

        // Conversations
        $convotab = new WebappTab("tweets-convo", "Conversations", "", $twitter_data_tpl);
        $convotabds = new WebappTabDataset("author_replies", $this->post_dao, "getPostsAuthorHasRepliedTo", array($instance->network_user_id, 15));
        $convotab->addDataset($convotabds);
        array_push($child_tabs, $convotab);

        return $child_tabs;
    }

    public function getChildTabsUnderReplies($instance) {
        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl';

        $child_tabs = array();

        //All Mentions
        $amtab = new WebappTab("mentions-all", "All Mentions", "Any post that mentions you", $twitter_data_tpl);
        $amtabds1 = new WebappTabDataset("all_tweets", $this->post_dao, "getAllPosts", array($instance->network_user_id, 15));
        $amtabds2 = new WebappTabDataset("all_mentions", $this->post_dao, "getAllMentions", array($instance->network_username, 15, $instance->network));
        $amtab->addDataset($amtabds1);
        $amtab->addDataset($amtabds2);
        array_push($child_tabs, $amtab);

        //All Replies
        $artab = new WebappTab("mentions-allreplies", "Replies", "Posts that directly reply to you (i.e., start with your name)", $twitter_data_tpl);
        $artabds = new WebappTabDataset("all_replies", $this->post_dao, "getAllReplies", array($instance->network_user_id, 15));
        $artab->addDataset($artabds);
        array_push($child_tabs, $artab);

        //All Orphan Mentions
        $omtab = new WebappTab("mentions-orphan", "Not Replies or Forwards", "Mentions that are not associated with a specific post", $twitter_data_tpl);
        $omtabds1 = new WebappTabDataset("all_tweets", $this->post_dao, "getAllPosts", array($instance->network_user_id, 15));
        $omtabds2 = new WebappTabDataset("orphan_replies", $this->post_dao, "getOrphanReplies", array($instance->network_username, 5, $instance->network));
        $omtab->addDataset($omtabds1);
        $omtab->addDataset($omtabds2);
        array_push($child_tabs, $omtab);

        //All Mentions Standalone
        $sttab = new WebappTab("mentions-standalone", "Standalone Mentions", "Mentions you have marked as standalone", $twitter_data_tpl);
        $sttabds1 = new WebappTabDataset("standalone_replies", $this->post_dao, "getStandaloneReplies", array($instance->network_username, 15));
        $sttabds2 = new WebappTabDataset("all_tweets", $this->post_dao, "getAllPosts", array($instance->network_user_id, 15));
        $sttab->addDataset($sttabds1);
        $sttab->addDataset($sttabds2);
        array_push($child_tabs, $sttab);

        return $child_tabs;
    }

    public function getChildTabsUnderFriends($instance) {
        global $fd;

        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl';

        $child_tabs = array();

        //Most Active Friends
        $motab = new WebappTab("friends-mostactive", 'Chatterboxes', '', $twitter_data_tpl);
        $motabds = new WebappTabDataset('people', $fd, "getMostActiveFollowees", array($instance->network_user_id, 15));
        $motab->addDataset($motabds);
        array_push($child_tabs, $motab);

        //Least Active Friends
        $latab = new WebappTab("friends-leastactive", 'Deadbeats', '', $twitter_data_tpl);
        $latabds = new WebappTabDataset("people", $fd, "getLeastActiveFollowees", array($instance->network_user_id, 15));
        $latab->addDataset($latabds);
        array_push($child_tabs, $latab);

        //Popular friends
        $poptab = new WebappTab("friends-mostfollowed", 'Popular', '', $twitter_data_tpl);
        $poptabds = new WebappTabDataset("people", $fd, "getMostFollowedFollowees", array($instance->network_user_id, 15));
        $poptab->addDataset($poptabds);
        array_push($child_tabs, $poptab);

        //Former Friends
        $fftab = new WebappTab("friends-former", "Former", '', $twitter_data_tpl);
        $fftabds = new WebappTabDataset("people", $fd, "getFormerFollowees", array($instance->network_user_id, 15));
        $fftab->addDataset($fftabds);
        array_push($child_tabs, $fftab);

        //Not Mutual Friends
        $nmtab = new WebappTab("friends-notmutual", "Not Mutual", '', $twitter_data_tpl);
        $nmtabds = new WebappTabDataset("people", $fd, "getFriendsNotFollowingBack", array($instance->network_user_id));
        $nmtab->addDataset($nmtabds);
        array_push($child_tabs, $nmtab);

        return $child_tabs;
    }

    public function getChildTabsUnderFollowers($instance) {
        global $fd;

        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl';

        $child_tabs = array();

        //Most followed
        $mftab = new WebappTab("followers-mostfollowed", 'Most-followed', 'Followers with most followers', $twitter_data_tpl);
        $mftabds = new WebappTabDataset("people", $fd, "getMostFollowedFollowers", array($instance->network_user_id, 15));
        $mftab->addDataset($mftabds);
        array_push($child_tabs, $mftab);

        //Least likely
        $lltab = new WebappTab("followers-leastlikely", "Least Likely", 'Followers with the greatest follower-to-friend ratio', $twitter_data_tpl);
        $lltabds = new WebappTabDataset("people", $fd, "getLeastLikelyFollowers", array($instance->network_user_id, 15));
        $lltab->addDataset($lltabds);
        array_push($child_tabs, $lltab);

        //Former followers
        $fftab = new WebappTab("followers-former", "Former", '', $twitter_data_tpl);
        $fftabds = new WebappTabDataset("people", $fd, "getFormerFollowers", array($instance->network_user_id, 15));
        $fftab->addDataset($fftabds);
        array_push($child_tabs, $fftab);

        //Earliest
        $eftab = new WebappTab("followers-earliest", "Earliest Joiners", '', $twitter_data_tpl);
        $eftabds = new WebappTabDataset("people", $fd, "getEarliestJoinerFollowers", array($instance->network_user_id, 15));
        $eftab->addDataset($eftabds);
        array_push($child_tabs, $eftab);

        return $child_tabs;
    }

    public function getChildTabsUnderLinks($instance) {
        global $ld;

        $twitter_data_tpl = Utils::getPluginViewDirectory('twitter').'twitter.inline.view.tpl';

        $child_tabs = array();

        //Links from friends
        $fltab = new WebappTab("links-friends", 'Links From Friends', 'Links your friends posted', $twitter_data_tpl);
        $fltabds = new WebappTabDataset("links", $ld, "getLinksByFriends", array($instance->network_user_id));
        $fltab->addDataset($fltabds);
        array_push($child_tabs, $fltab);

        //Links from favorites
        /* $lftab = new WebappTab("links-favorites", 'Links From Favorites', 'Links in posts you favorited');
        $lftabds = new WebappTabDataset("links", $ld, "getLinksByFriends", array($instance->network_user_id));
        $lftab->addDataset($lftabds);
        array_push($child_tabs, $lftab);
        */
        //Photos
        $ptab = new WebappTab("links-photos", "Photos", 'Photos your friends have posted', $twitter_data_tpl);
        $ptabds = new WebappTabDataset("links", $ld, "getPhotosByFriends", array($instance->network_user_id));
        array_push($child_tabs, $ptab);

        return $child_tabs;
    }
}
?>
