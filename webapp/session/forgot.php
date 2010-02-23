<?php 
session_start();

// set up
chdir("..");
require_once ('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("init.php");

require_once ("class.Mailer.php");


$session = new Session();
if ($session->isLoggedIn()) {
    header("Location: ../index.php");
}

$SQLLogger = new LoggerSlowSQL($THINKTANK_CFG['sql_log_location']);
$db = new Database($THINKTANK_CFG, $SQLLogger);
$conn = $db->getConnection();
$od = new OwnerDAO($db);

$s = new SmartyThinkTank();
$s->caching=false;

if ($_POST['Submit'] == 'Send') {
    $host = $_SERVER['HTTP_HOST'];
    if ($od->doesOwnerExist($_POST['email'])) {
        $newpwd = rand(10000, 99999);
        $host = $_SERVER['HTTP_HOST'];
        $cryptpass = $session->pwdcrypt($newpwd);
        $od->updatePassword($_POST['email'], $cryptpass);
        
        $message = "Password recovery information you requested from $host:\n\n";
        $message .= "User Name: ".$_POST['email']." \n";
        $message .= "Password: $newpwd \n";
        $message .= "____________________________________________\n";
        $message .= "*** LOGIN ***** \n";
        $message .= "http://".$host.$THINKTANK_CFG['site_root_path']."session/login.php \n\n";
        $message .= "_____________________________________________";
        $message .= "Thank you. This is an automated response. PLEASE DO NOT REPLY.";

        Mailer::mail($_POST['email'], "New ThinkTank Login Details", $message);

        $successmsg = "Password recovery information has been sent to your email address. <a href=\"login.php\">Sign in.</a>";
    } else
        $errormsg = "Account does not exist";
}

if (isset($errormsg)) {
    $s->assign('errormsg', $errormsg);
} elseif (isset($successmsg)) {
    $s->assign('successmsg', $successmsg);
}
$cfg = new Config();
$s->assign('cfg', $cfg);
$s->display('session.forgot.tpl');
$SQLLogger->close();
?>
