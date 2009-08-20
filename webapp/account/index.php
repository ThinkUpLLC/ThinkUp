<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: /session/login.php"); }

// set up
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").":".$INCLUDE_PATH);
require_once("init.php");

$db = new Database();
$conn = $db->getConnection();

$id = new InstanceDAO();
$od = new OwnerDAO();
$cfg = new Config();
$s = new SmartyTwitalytic();

$owner = $od->getByEmail($_SESSION['user']);
$owner_instances = $id->getByOwner($owner);


$to = new TwitterOAuth($cfg->oauth_consumer_key, $cfg->oauth_consumer_secret);
/* Request tokens from twitter */
$tok = $to->getRequestToken();
$token = $tok['oauth_token'];
$_SESSION['oauth_request_token_secret'] = $tok['oauth_token_secret'];

/* Build the authorization URL */
$oauthorize_link = $to->getAuthorizeURL($token);



$s->assign('owner_instances', $owner_instances );
$s->assign('owner', $owner);
$s->assign('cfg', $cfg);
$s->assign('oauthorize_link',$oauthorize_link );
# clean up
$db->closeConnection($conn);	

echo $s->fetch('accounts.index.tpl');
?>