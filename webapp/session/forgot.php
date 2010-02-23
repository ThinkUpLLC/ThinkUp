<?php 
session_start();

// set up
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");

$session = new Session();
if ($session->isLogedin()) { 
    header("Location: ../index.php");
}

$SQLLogger = new LoggerSlowSQL($THINKTANK_CFG['sql_log_location']);
$db = new Database($THINKTANK_CFG, $SQLLogger);
$conn = $db->getConnection();
$od = new OwnerDAO($db);

if ($_POST['Submit'] == 'Send') {
    $host = $_SERVER['HTTP_HOST'];
    if (getUserExist($_POST['email'])) {
        $newpwd = rand(10000, 99999);
        $host = $_SERVER['HTTP_HOST'];
        $cryptpass = $session->pwdcrypt($newpass);
        $od->updatePaassword($_POST['email'], $cryptpass);

        $message = "You have requested new login details from $host. Here are the login details...\n\n";
        $message .= "User Name: ".$_POST['email']." \n";
        $message .= "Password: $newpwd \n";
        $message .= "____________________________________________";
        $message .= "*** LOGIN ***** \n";
        $message .= "To Login: http://".$host.$THINKTANK_CFG['site_root_path']."session/login.php \n\n";
        $message .= "_____________________________________________";
        $message .= "Thank you. This is an automated response. PLEASE DO NOT REPLY.";
        $header = "From: \"Auto-Response\" <robot@$host>\r\n";
        $header .= "X-Mailer: PHP/".phpversion();
        mail($_POST['email'], "New Login Details", $message, $header);
        
        die("Thank you. New Login details has been sent to your email address");
    } else
        die("Account with given email does not exist");
}
$s = new SmartyThinkTank();
$s->display('session.forgot.tpl');
$SQLLogger->close();
?>
