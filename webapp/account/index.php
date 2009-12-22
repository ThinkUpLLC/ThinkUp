<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: /session/login.php"); }

// set up

chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");

include ('session/dbc.php');

if ($_POST['changepass'] == 'Change Password')
{
	$originalpass = mysql_query("select user_pwd from ".$TWITALYTIC_CFG['table_prefix']."owners where user_email='".$_SESSION['user']."'");
	$origpass = mysql_result($originalpass,0);
	$oldpassmd5 = md5($_POST['oldpass']);
	if ($oldpassmd5 != $origpass)
	{
		die("ERROR: Old password does not match or empty.");
	}
	elseif ($_POST['pass1'] != $_POST['pass2'])
	{
		die("ERROR: New passwords must match.");
	}
	elseif (strlen($_POST['pass1']) < 5)
	{
		die("ERROR: New password must be at least 5 characters.");
	}
	else
	{
		$newmd5pwd = md5($_POST['pass1']);
		mysql_query("UPDATE ".$TWITALYTIC_CFG['table_prefix']."owners set user_pwd='$newmd5pwd' where user_email='$_SESSION[user]'");
	}
}

$db = new Database($TWITALYTIC_CFG);
$conn = $db->getConnection();

$id = new InstanceDAO($db);
$od = new OwnerDAO($db);
$cfg = new Config($db);
$s = new SmartyTwitalytic();
$s->caching = 0;

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

$s->display('account.index.tpl');
?>
