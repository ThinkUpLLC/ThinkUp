<?php  
# invoke this hourly in cron
require_once('crawl.config.inc.php');

ini_set("include_path", ini_get("include_path").":".$CRAWLER_INCLUDE_PATH);

$root_path="";
require_once("init.php");

// Instantiate and initialize needed objects
$db = new Database();
$conn = $db->getConnection();

$id = new InstanceDAO();
$instances = $id->getAllInstances();

foreach ($instances as $i) {
	$crawler = new Crawler($i);
	$cfg = new Config($i->owner_username, $i->owner_user_id);
	$logger = new Logger($i->owner_username);
	$api = new TwitterAPIAccessor($TWITALYTIC_CFG['app_title'], $i);
	$api -> init($logger);

	if ( $api->available_api_calls_for_crawler > 0 ) {
	
		$crawler->fetchOwnerInfo($cfg, $api, $logger);

		$crawler->fetchOwnerTweets($cfg, $api, $logger);
	
		$crawler->fetchOwnerReplies($cfg, $api, $logger);
	
		$crawler->fetchOwnerFollowers($cfg, $api, $logger);
	
		$crawler->fetchOwnerFriends($cfg, $api, $logger);

		// TODO: Get direct messages
		// TODO: Gather favorites data

		$crawler->updateQueuedUsers($logger);
	
		$owner_tweet_count = $crawler->owner_object->tweet_count;
	} else {
		$owner_tweet_count = '';
	}

	// Save instance
	$id->save($crawler->instance, $owner_tweet_count, $logger, $api);
	$logger->close();			# Close logging

	#Clean up
	$crawler = null;
	$cfg = null;
	$logger = null;
	$api->close();				# Clean up connection
	$api = null;
}

// Clean up
if ( isset($conn) ) $db->closeConnection($conn);
?>