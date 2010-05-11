<?php
/*
 Plugin Name: Twitter
 Plugin URI: http://github.com/ginatrapani/thinktank/tree/master/model/plugins/twitter/
 Description: Crawler plugin fetches data from Twitter.com for the authorized user.
 Icon: assets/img/twitter_icon.png
 Version: 0.01
 Author: Gina Trapani
 */

function twitter_crawl() {
    global $THINKTANK_CFG;
    global $db;
    global $conn;

    $logger = new Logger($THINKTANK_CFG['log_location']);
    $id = new InstanceDAO($db, $logger);
    $oid = new OwnerInstanceDAO($db, $logger);

    $instances = $id->getAllActiveInstancesStalestFirstByNetwork('twitter');
    foreach ($instances as $i) {
        $logger->setUsername($i->network_username);
        $tokens = $oid->getOAuthTokens($i->id);
        $noauth = true;

        if (isset($tokens['oauth_access_token']) && $tokens['oauth_access_token'] != '' && isset($tokens['oauth_access_token_secret']) && $tokens['oauth_access_token_secret'] != '') {
            $noauth = false;
        }

        if ($noauth) {
            $api = new CrawlerTwitterAPIAccessorOAuth('NOAUTH', 'NOAUTH', $THINKTANK_CFG['oauth_consumer_key'], $THINKTANK_CFG['oauth_consumer_secret'], $i, $THINKTANK_CFG['archive_limit']);
        } else {
            $api = new CrawlerTwitterAPIAccessorOAuth($tokens['oauth_access_token'], $tokens['oauth_access_token_secret'], $THINKTANK_CFG['oauth_consumer_key'], $THINKTANK_CFG['oauth_consumer_secret'], $i, $THINKTANK_CFG['archive_limit']);
        }

        $crawler = new TwitterCrawler($i, $logger, $api, $db);
        $cfg = new Config($i->network_username, $i->network_user_id);

        $api->init($logger);

        if ($api->available_api_calls_for_crawler > 0) {

            $id->updateLastRun($i->id);

            // No auth req'd
            $crawler->fetchInstanceUserInfo();

            // No auth for public Twitter users
            $crawler->fetchInstanceUserTweets();

            if (!$noauth) {
                // Auth req'd, for calling user only
                $crawler->fetchInstanceUserRetweetsByMe();

                // Auth req'd, for calling user only
                $crawler->fetchInstanceUserMentions();

                // Auth req'd, for calling user only
                $crawler->fetchInstanceUserFriends();

                // Auth req'd, for calling user only
                $crawler->fetchInstanceUserFollowers();
            }

            $crawler->fetchStrayRepliedToTweets();

            $crawler->fetchUnloadedFollowerDetails();

            $crawler->fetchFriendTweetsAndFriends();

            // TODO: Get direct messages
            // TODO: Gather favorites data

            if ($noauth) {
                // No auth req'd
                $crawler->fetchSearchResults($i->network_username);
            }

            $crawler->cleanUpFollows();

            // Save instance
            $id->save($crawler->instance, $crawler->owner_object->post_count, $logger, $api);
        }
    }

    $logger->close(); # Close logging

}

function twitter_webapp_configuration() {
    global $THINKTANK_CFG;
    global $s;
    global $od;
    global $id;
    global $db;
    global $cfg;
    global $owner;

    //Add public user instance
    if (isset($_GET['twitter_username'])) { // if form was submitted
        $logger = new Logger($THINKTANK_CFG['log_location']);

        //Check user exists and is public
        $api = new TwitterAPIAccessorOAuth('NOAUTH', 'NOAUTH', $THINKTANK_CFG['oauth_consumer_key'], $THINKTANK_CFG['oauth_consumer_secret'], $THINKTANK_CFG['archive_limit']);
        $api_call = str_replace("[id]", $_GET['twitter_username'], $api->cURL_source['show_user']);
        list($cURL_status, $data) = $api->apiRequestFromWebapp($api_call, $logger);
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

                    $oi = $oid->get($owner->id, $i->id);
                    if ($oi != null) {
                        //$msg .= "Owner already has this instance, no insert or update required.<br />";
                    } else {
                        $oid->insert($owner->id, $i->id, '', '');
                        //$msg .= "Added owner instance.<br />";
                    }

                } else {
                    //$msg .= "Instance does not exist.<br />";

                    $id->insert($user["user_id"], $user["user_name"]);
                    //$msg .= "Created instance.<br />";

                    $i = $id->getByUsername($user["user_name"]);
                    $oid->insert($owner->id, $i->id, '', '');
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

    $to = new TwitterOAuth($cfg->oauth_consumer_key, $cfg->oauth_consumer_secret);
    /* Request tokens from twitter */
    $tok = $to->getRequestToken();
    if (isset($tok['oauth_token']) ) {
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


$crawler->registerCallback('twitter_crawl', 'crawl');

$webapp->addToConfigMenu('twitter', 'Twitter');
$webapp->registerCallback('twitter_webapp_configuration', 'configuration|twitter');

?>
