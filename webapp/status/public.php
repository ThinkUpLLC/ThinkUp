<?php
// set up
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");

$cfg = new Config();
$db = new Database($TWITALYTIC_CFG);
$s = new SmartyTwitalytic();
$c = new Crawler();

$conn = $db->getConnection();

// instantiate data access objects
$ud = new UserDAO($db);
$fd = new FollowDAO($db);
$td = new TweetDAO($db);
$c->init();

//TODO error checking here, is tweet in db, etc
$status_id = $_REQUEST['t'];

$s->assign('tweet', $td->getTweet($status_id));
$s->assign('replies', $td->getPublicRepliesToTweet($status_id) );
$s->assign('cfg', $cfg);

# clean up
$db->closeConnection($conn);	

echo $s->fetch('replies.public.tpl');




/*


$root_path			=  "../uar/includes/";
include $root_path.'environment.php';				# Determine relevant paths
include $root_path.'status_messages.php';			# Turn error code into message
include $root_path.'validate_data.php';				# Validate application data
include $root_path.'api_config.php';				# Configuring the API connection
include $root_path.'database.php';					# Connection to the database
#include $root_path.'sql_queries.php';				# Format the SQL queries

# include smarty
require('smarty/libs/Smarty.class.php');

# get user name from u param, default to ginatrapani
$status_id = $_REQUEST['t'];

# prep db
$db_connection 	= openDB();						# Open database connection

$sql_query		= "select tweet_text, author_username from tweet where status_id=".$status_id.";";	# Retrieve the SQL statements
$sql_result = mysql_query($sql_query)  or die('Error, selection query failed: '.$sql_query);
$tweet_replied_to 		= array();

while ($row = mysql_fetch_assoc($sql_result)) { $tweet_replied_to[] = $row; } 



# get all replies to tweet
$sql_query		= "select tweet_html, author_username, follower_count, status_id, is_protected from reply r inner join user u on r.author_user_id = u.user_id where in_reply_to_status_id=".$status_id." and u.is_protected = 0 order by follower_count desc;";	# Retrieve the SQL statements
$sql_result = mysql_query($sql_query)  or die('Error, selection query failed:'. $sql_query);
$tweets_stored 		= array();
while ($row = mysql_fetch_assoc($sql_result)) { $tweets_stored[] = $row; } 
mysql_free_result($sql_result);					# Free up memory

closeDB($db_connection);							# Close database connection


# invoke smarty template
$smarty = new Smarty();

$smarty->template_dir = 'templates';
$smarty->compile_dir = 'templates_c';
#$smarty->caching=false;
$smarty->assign('tweet', $tweet_replied_to);
$smarty->assign('data', $tweets_stored);

echo $smarty->fetch('reply-public.tpl');

*/
?>