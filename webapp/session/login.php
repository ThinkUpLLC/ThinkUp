<?php 
session_start();

// set up
chdir("..");
require_once ('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("init.php");

$session = new Session();
if ($session->isLoggedIn()) {
    header("Location: ../index.php");
}

$SQLLogger = new LoggerSlowSQL($THINKTANK_CFG['sql_log_location']);
$db = new Database($THINKTANK_CFG, $SQLLogger);
$conn = $db->getConnection();

$od = new OwnerDAO($db);
$user_email = mysql_real_escape_string($_POST['email']);
$s = new SmartyThinkTank();
$s->caching=false;

if ($_POST['Submit'] == 'Login') {
    $result = $od->getForLogin($user_email);
    if (!$result) {
        header("Location: login.php?emsg=Invalid+email+or+password");
    } elseif (!$session->pwdCheck($_POST['pwd'], $result['pwd'])) {
        header("Location: login.php?emsg=Incorrect+email+or+password");
    } else {
        // this sets variables in the session
        $session->completeLogin($result);
		$od->updateLastLogin($user_email);
        if (isset($_GET['ret']) && ! empty($_GET['ret'])) {
            header("Location: $_GET[ret]");
        } else {
            header("Location: ".$THINKTANK_CFG['site_root_path']);
        }
        exit();
    }
}
if (isset($_GET["emsg"]))
    $emsg = $_GET["emsg"];
    
if (isset($_GET["smsg"]))
    $smsg = $_GET["smsg"];
    
if (isset($emsg)) {
    $s->assign('errormsg', $emsg);
} elseif (isset($smsg)) {
    $s->assign('successmsg', $smsg);
}
$cfg = new Config();
$s->assign('cfg', $cfg);
$s->display('session.login.tpl');

$SQLLogger->close();

?>
