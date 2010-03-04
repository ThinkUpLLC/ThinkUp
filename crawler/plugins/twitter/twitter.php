<?php


function twitter_crawl() {
	global $THINKTANK_CFG;
	$db = new Database($THINKTANK_CFG);
	$conn = $db->getConnection();

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


$crawler->registerCallback('twitter_crawl', 'crawl');

?>