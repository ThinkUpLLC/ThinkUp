<?php  
# invoke this hourly in cron
require_once('crawl.config.inc.php');

ini_set("include_path", ini_get("include_path").":".$CRAWLER_INCLUDE_PATH);

$root_path="";
require_once("init.php");

// Instantiate needed objects
$cfg = new Config();
$logger = new Logger();
$api = new TwitterAPIAccessor();
$db = new Database();
$crawler = new Crawler();

// Initialize objects
$api -> init($logger);
$conn = $db->getConnection();
$crawler->init();

if ( $api->available_api_calls_for_crawler > 0 ) {
	
	$crawler->fetchOwnerInfo($cfg, $api, $logger);

	$crawler->fetchOwnerTweets($cfg, $api, $logger);
	
	$crawler->fetchOwnerReplies($cfg, $api, $logger);
	
	$crawler->fetchOwnerFollowers($cfg, $api, $logger);
	
	// TODO: Get Followees
	$crawler->fetchOwnerFriends($cfg, $api, $logger);
	// TODO: Get direct messages
	// TODO: Gather favorites data

	$crawler->updateQueuedUsers($logger);
	
	$owner_tweet_count = $crawler->owner_object->tweet_count;
} else {
	$owner_tweet_count = '';
}

// Save crawler state
$crawler->saveState($owner_tweet_count, $logger, $api);

// Clean up
if ( isset($conn) ) $db->closeConnection($conn);
$api->close();				# Clean up connection
$logger->close();			# Close logging
?>