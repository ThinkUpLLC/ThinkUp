<?php   
require_once('config.crawler.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");

$db = new Database($TWITALYTIC_CFG);
$conn = $db->getConnection();

$logger = new Logger($TWITALYTIC_CFG['log_location']);
$id = new InstanceDAO();
$oid = new OwnerInstanceDAO();
$lurlapi = new LongUrlAPIAccessor($TWITALYTIC_CFG['app_title']);
$flickrapi = new FlickrAPIAccessor($TWITALYTIC_CFG['flickr_api_key']);


$instances = $id->getAllInstancesStalestFirst();
foreach ($instances as $i) {
	$logger->setUsername($i->twitter_username);
	$tokens = $oid->getOAuthTokens($i->id);
	$api = new CrawlerTwitterAPIAccessorOAuth($tokens['oauth_access_token'], $tokens['oauth_access_token_secret'], $TWITALYTIC_CFG['oauth_consumer_key'], $TWITALYTIC_CFG['oauth_consumer_secret'], $i, $TWITALYTIC_CFG['archive_limit']);
	$crawler = new Crawler($i, $logger, $api);
	$cfg = new Config($i->twitter_username, $i->twitter_user_id);
	
	$api->init($logger);

	if ( $api->available_api_calls_for_crawler > 0 ) {

		$id->updateLastRun($i->id);
		
		$crawler->fetchInstanceUserInfo();

		$crawler->fetchInstanceUserTweets($lurlapi, $flickrapi);

		$crawler->fetchInstanceUserReplies($lurlapi, $flickrapi);

		$crawler->fetchInstanceUserFriends();

		$crawler->fetchInstanceUserFollowers();

		$crawler->fetchStrayRepliedToTweets($lurlapi, $flickrapi);

		$crawler->fetchUnloadedFollowerDetails();

		$crawler->fetchFriendTweetsAndFriends($lurlapi, $flickrapi);

		// TODO: Get direct messages
		// TODO: Gather favorites data

		$crawler->cleanUpFollows();
	
		// Save instance
		$id->save($crawler->instance,  $crawler->owner_object->tweet_count, $logger, $api);
	} 
}

$logger->close();			# Close logging

if ( isset($conn) ) $db->closeConnection($conn); // Clean up
?>