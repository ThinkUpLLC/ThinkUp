<?php 
session_start();

// set up
chdir("..");
require_once ('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("init.php");

$session = new Session();
if ($session->isLogedin()) { 
    header("Location: ../index.php");
}

$SQLLogger = new LoggerSlowSQL($THINKTANK_CFG['sql_log_location']);
$db = new Database($THINKTANK_CFG, $SQLLogger);
$conn = $db->getConnection();
$od = new OwnerDAO($db);

if (!$THINKTANK_CFG['is_registration_open']) {
    echo 'So sorry, but registration on this instance of ThinkTank is closed. <br /><br /><a href="http://github.com/ginatrapani/thinktank/tree/master">Install thinktank on your own server</a> or go back to <a href="'.$THINKTANK_CFG['site_root_path'].'public.php">the public timeline</a>.';
    die();
} else {
    $s = new SmartyThinkTank();
    $captcha = new Captcha($THINKTANK_CFG);
    if ($_POST['Submit'] == 'Register') {
        if (strlen($_POST['email']) < 5) {
            $msg = "Incorrect email. Please enter valid email address..";
        }
        if (strcmp($_POST['pass1'], $_POST['pass2']) || empty($_POST['pass1'])) {
            //die ("Password does not match");
            $msg = "ERROR: Password does not match or empty.";
        } elseif ( !$captcha->check()) {
            //Captcha not valid, captcha handles message...
        } else {
            if ($od->getUserExist($_POST['email'])) {
                $msg = "ERROR: User account already exists..";
                exit();
            } else {
                $cryptpass = $session->pwdcrypt($_POST['pass2']);
                $server = $_SERVER['HTTP_HOST'];
                $host = ereg_replace('www.', '', $server);
                $od->create($_POST['email'], $cryptpass, $_POST['country'], $activ_code, $_POST['full_name']);
                $message = "Thank you for registering an account with ".$THINKTANK_CFG['app_title'];
                $message .= ". Click on the link below to activate your account...\n\n";
                $message .= "http://$server/".$THINKTANK_CFG['site_root_path'];
                $message .= "session/activate.php?usr=".urlencode($_POST[email])."&code=$activ_code \n\n";
                $message .= "_____________________________________________";
                $message .= "Thank you. This is an automated response. PLEASE DO NOT REPLY.";
                $mailheader = "From: \"Auto-Response\" <notifications@$host>\r\n";
                $mailheader .= "X-Mailer: PHP/".phpversion();
                mail($_POST['email'], "Login Activation", $message, $mailheader);
                unset($_SESSION['ckey']);
                echo("Registration Successful! An activation code has been sent to your email address with an activation link.");
                exit;
            }
        }
        $s->assign('name', $_POST["full_name"]);
        $s->assign('mail', $_POST["email"]);
    }
    $challenge = $captcha->generate($msg);
    $s->assign('captcha', $challenge);

    if (isset($msg)) {
        $s->assign('msg', $_GET[msg]);
        $s->display('session.register.tpl', sha1($_GET['msg']));
    } else {
        $s->display('session.register.tpl');
    }
}
$SQLLogger->close();
?>
