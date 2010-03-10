<?php 
function twitter_crawl() {
    global $THINKTANK_CFG;
    global $db;
    global $conn;
    
    $logger = new Logger($THINKTANK_CFG['log_location']);
    $id = new InstanceDAO($db, $logger);
    $oid = new OwnerInstanceDAO($db, $logger);
    $lurlapi = new LongUrlAPIAccessor($THINKTANK_CFG['app_title']);
    $flickrapi = new FlickrAPIAccessor($THINKTANK_CFG['flickr_api_key']);

    
    $instances = $id->getAllActiveInstancesStalestFirst();
    foreach ($instances as $i) {
        $logger->setUsername($i->twitter_username);
        $tokens = $oid->getOAuthTokens($i->id);
        $api = new CrawlerTwitterAPIAccessorOAuth($tokens['oauth_access_token'], $tokens['oauth_access_token_secret'], $THINKTANK_CFG['oauth_consumer_key'], $THINKTANK_CFG['oauth_consumer_secret'], $i, $THINKTANK_CFG['archive_limit']);
        $crawler = new TwitterCrawler($i, $logger, $api, $db);
        $cfg = new Config($i->twitter_username, $i->twitter_user_id);
        
        $api->init($logger);
        
        if ($api->available_api_calls_for_crawler > 0) {
        
            $id->updateLastRun($i->id);
            
            $crawler->fetchInstanceUserInfo();
            
            $crawler->fetchInstanceUserTweets($lurlapi, $flickrapi);
            
            $crawler->fetchInstanceUserRetweetsByMe($lurlapi, $flickrapi);
            
            $crawler->fetchInstanceUserMentions($lurlapi, $flickrapi);
            
            $crawler->fetchInstanceUserFriends();
            
            $crawler->fetchInstanceUserFollowers();
            
            $crawler->fetchStrayRepliedToTweets($lurlapi, $flickrapi);
            
            $crawler->fetchUnloadedFollowerDetails();
            
            $crawler->fetchFriendTweetsAndFriends($lurlapi, $flickrapi);
            
            // TODO: Get direct messages
            // TODO: Gather favorites data
            
            $crawler->cleanUpFollows();
            
            // Save instance
            $id->save($crawler->instance, $crawler->owner_object->tweet_count, $logger, $api);
        }
    }
    
    $logger->close(); # Close logging
    
    if (isset($conn))
        $db->closeConnection($conn); // Clean up
        
}

function twitter_webapp_configuration() {
    global $s;
    global $od;
    global $id;
    global $cfg;
    global $owner;
    
    $to = new TwitterOAuth($cfg->oauth_consumer_key, $cfg->oauth_consumer_secret);
    /* Request tokens from twitter */
    $tok = $to->getRequestToken();
    $token = $tok['oauth_token'];
    $_SESSION['oauth_request_token_secret'] = $tok['oauth_token_secret'];
    
    /* Build the authorization URL */
    $oauthorize_link = $to->getAuthorizeURL($token);
    
    $owner_instances = $id->getByOwner($owner);
    
    $s->assign('owner_instances', $owner_instances);
    $s->assign('oauthorize_link', $oauthorize_link);
}


$crawler->registerCallback('twitter_crawl', 'crawl');

$webapp->addToConfigMenu('twitter', 'Twitter');
$webapp->registerCallback('twitter_webapp_configuration', 'configuration|twitter');
?>
