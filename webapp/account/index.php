<?php 
// set up
chdir("..");
require_once ('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("init.php");

session_start();
$session = new Session();
if (!$session->isLoggedIn()) {
    header("Location: ../index.php");
}


$db = new Database($THINKTANK_CFG);
$conn = $db->getConnection();

$od = new OwnerDAO($db);


if ($_POST['changepass'] == 'Change Password') {
    $originalpass = $od->getPass($_SESSION['user']);
    $origpass = $originalpass['pwd'];
    if ($session->pwdCheck($_POST['oldpass'], $origpass)) {
        $errormsg = "Old password does not match or empty.";
    } elseif ($_POST['pass1'] != $_POST['pass2']) {
        $errormsg = "New passwords did not match. Your password has not been changed.";
    } elseif (strlen($_POST['pass1']) < 5) {
        $errormsg = "New password must be at least 5 characters. Your password has not been changed.";
    } else {
        $cryptpass = $session->pwdcrypt($_POST['pass1']);
        $od->updatePassword($_SESSION['user'], $cryptpass);
        $successmsg = "Your password has been updated.";

    }
}
$id = new InstanceDAO($db);
$od = new OwnerDAO($db);
$cfg = new Config($db);
$s = new SmartyThinkTank();
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

$s->assign('owner_instances', $owner_instances);
$s->assign('owner', $owner);
$s->assign('cfg', $cfg);
$s->assign('oauthorize_link', $oauthorize_link);
# clean up
$db->closeConnection($conn);

if (isset($errormsg)) 
    $s->assign('errormsg', $errormsg);
if (isset($successmsg)) 
    $s->assign('successmsg', $successmsg);

$s->display('account.index.tpl');
?>
