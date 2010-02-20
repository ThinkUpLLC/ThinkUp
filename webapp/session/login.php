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

$db = new Database($THINKTANK_CFG);
$conn = $db->getConnection();

$od = new OwnerDAO($db);
$user_email = mysql_real_escape_string($_POST['email']);
$s = new SmartyThinkTank();

if ($_POST['Submit']=='Login') {
    $result = $od->getForLogin($user_email);
    if (!$result){ 
        header("Location: login.php?msg=Invalid Login");
    } elseif (!$session->pwdCheck($_POST['pwd'], $result['pwd'])) {
        header("Location: login.php?msg=Invalid Login");
    } else {
        // this sets variables in the session 
        $session->CompleteLogin($result);
        if (isset($_GET['ret']) && !empty($_GET['ret'])){
            header("Location: $_GET[ret]");
        } else {
            header("Location: ".$THINKTANK_CFG['site_root_path']);
        }
        exit();
    }
}
if (isset($msg)) {
    $s->assign('msg', $msg);
    $s->display('session.login.tpl', sha1($msg));
} else {
    $s->display('session.login.tpl');
}

?>
