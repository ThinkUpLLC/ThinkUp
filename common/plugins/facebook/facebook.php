<?php 
function facebook_crawl() {
    //TODO Crawl Facebook posts and comments and insert them into the database
    global $THINKTANK_CFG;
    global $db;
    global $conn;
    
    $logger = new Logger($THINKTANK_CFG['log_location']);
    $id = new InstanceDAO($db, $logger);
    $oid = new OwnerInstanceDAO($db, $logger);
    
    $instances = $id->getAllActiveInstancesStalestFirstByNetwork('facebook');
    foreach ($instances as $i) {
        $logger->setUsername($i->network_username);
        $tokens = $oid->getOAuthTokens($i->id);
        $session_key = $tokens['oauth_access_token'];
        
        $fb = new Facebook($THINKTANK_CFG['facebook_api_key'], $THINKTANK_CFG['facebook_api_secret']);
        
        $cfg = new Config($i->network_username, $i->network_user_id);
        
        $id->updateLastRun($i->id);
        $crawler = new FacebookCrawler($i, $logger, $fb, $db);
        
        $crawler->fetchInstanceUserInfo($i->network_user_id, $session_key);
        $crawler->fetchUserPostsAndReplies($i->network_user_id, $session_key);
        
        $id->save($crawler->instance, $crawler->owner_object->post_count, $logger, $fb);
        
    }
    $logger->close(); # Close logging
    
}

function facebook_webapp_configuration() {
    global $THINKTANK_CFG;
    global $s;
    global $od;
    global $id;
    global $cfg;
    global $owner;
    global $oid;
    
    $facebook = new Facebook($THINKTANK_CFG['facebook_api_key'], $THINKTANK_CFG['facebook_api_secret']);
    
    $owner_instances = $id->getByOwnerAndNetwork($owner, 'facebook');
    //$fb_user = $facebook->require_login($required_permissions = 'email,read_stream');
    
    $fbconnect_link = '<a href="#" onclick="FB.Connect.requireSession(); return false;" ><img id="fb_login_image" src="http://static.ak.fbcdn.net/images/fbconnect/login-buttons/connect_light_medium_long.gif" alt="Connect"/>	</a>';
    //$fbconnect_link = '<fb:login-button size="medium" background="light" length="long" onlogin="facebook_onlogin_ready();"></fb:login-button>';
    $s->assign('fbconnect_link', $fbconnect_link);
    $s->assign('owner_instances', $owner_instances);
    $s->assign('fb_api_key', $THINKTANK_CFG['facebook_api_key']);
}


$crawler->registerCallback('facebook_crawl', 'crawl');

$webapp->addToConfigMenu('facebook', 'Facebook');
$webapp->registerCallback('facebook_webapp_configuration', 'configuration|facebook');

?>
