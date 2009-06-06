<?php
// set up
$root_path 			= realpath('./../include')."/";
require_once($root_path . "init.php");
$cfg = new Config();
$api = new TwitterAPIAccessor();
$db = new Database();
$s = new SmartyTwitalytic();
$c = new Crawler();
$u = new Utils();
$conn = $db->getConnection();

// instantiate data access objects
$ud = new UserDAO();
$fd = new FollowDAO();
$td = new TweetDAO();
$c->init();

// pass data to smarty
$owner_stats = $ud->getDetails($cfg->owner_user_id);
$s->assign('owner_stats', $owner_stats);
$s->assign('most_followed_followers', $fd->getMostFollowedFollowers($cfg->owner_user_id, 15));
$s->assign('least_likely_followers', $fd->getLeastLikelyFollowers($cfg->owner_user_id, 15));
$s->assign('earliest_joiner_followers', $fd->getEarliestJoinerFollowers($cfg->owner_user_id, 15));

$s->assign('all_tweets', $td->getAllTweets($cfg->owner_user_id, 25) );
$s->assign('all_replies', $td->getAllReplies($cfg->owner_username, 15) );

$s->assign('most_replied_to_tweets', $td->getMostRepliedToTweets($cfg->owner_user_id, 15));
$s->assign('orphan_replies', $td->getOrphanReplies($cfg->owner_username, 5));
$s->assign('standalone_replies', $td->getStandaloneReplies());

$s->assign('most_active_friends', $fd->getMostActiveFollowees($cfg->owner_user_id, 25));
$s->assign('least_active_friends', $fd->getLeastActiveFollowees($cfg->owner_user_id, 25));
$s->assign('most_followed_friends', $fd->getMostFollowedFollowees($cfg->owner_user_id, 25));

$s->assign('crawler', $c);
$s->assign('cfg', $cfg);

//Percentages
$percent_followers_loaded = $u->getPercentage($owner_stats['follower_count'], $c->total_follows_in_system);
$percent_tweets_loaded = $u->getPercentage($owner_stats['tweet_count'],$c->total_tweets_in_system );

$s->assign('percent_followers_loaded', $percent_followers_loaded);
$s->assign('percent_tweets_loaded', $percent_tweets_loaded);

# clean up
$db->closeConnection($conn);	

echo $s->fetch('index.tpl');



/*  People you've gotten the most replies from in the last XXX months (use date of oldest reply)

	select 
		author_username, count(author_user_id) as total_replies 
	from
		reply
	group by
		author_user_id
	order by 
		total_replies desc
	limit 5;
	
	
	People you reply to the most since (date of oldest tweet)
	
		select
			u.user_name, count(t.in_reply_to_user_id) as total_replies
		from
			tweet t
		inner join
			user u
		on
			u.user_id = t.in_reply_to_user_id
		group by
			in_reply_to_user_id
		order by 
			total_replies desc
		limit 10;	
*/




?>