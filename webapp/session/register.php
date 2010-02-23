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

$s = new SmartyThinkTank();
$s->caching=false;

if (!$THINKTANK_CFG['is_registration_open']) {
    $s->assign('closed', true);
    $errormsg = 'Sorry, registration on this instance of ThinkTank is closed. <br /><br /><a href="http://github.com/ginatrapani/thinktank/tree/master">Install ThinkTank on your own server</a> or go back to <a href="'.$THINKTANK_CFG['site_root_path'].'public.php">the public timeline</a>.';
} else {
    $db = new Database($THINKTANK_CFG);
    $conn = $db->getConnection();
    $od = new OwnerDAO($db);

    
    $s->assign('closed', false);
    $captcha = new Captcha($THINKTANK_CFG);
    if ($_POST['Submit'] == 'Register') {
        if (strlen($_POST['email']) < 5) {
            $errormsg = "Incorrect email. Please enter valid email address.";
        }
        if (strcmp($_POST['pass1'], $_POST['pass2']) || empty($_POST['pass1'])) {
            if (!isset($errormsg))
                $errormsg = "Password does not match or empty.";
        } elseif (!$captcha->check()) {
            //Captcha not valid, captcha handles message...
        } else {
            if ($od->doesOwnerExist($_POST['email'])) {
                $errormsg = "User account already exists.";
            } else {
                $activ_code = rand(1000, 9999);
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
                
                Mailer::mail($_POST['email'], "ThinkTank Login Activation", $message);
                
                unset($_SESSION['ckey']);
                $successmsg = "Success! Check your email for an activation link.";
            }
        }
        $s->assign('name', $_POST["full_name"]);
        $s->assign('mail', $_POST["email"]);
    }
    $challenge = $captcha->generate($msg);
    $s->assign('captcha', $challenge);
    
}
if (isset($errormsg)) {
    $s->assign('errormsg', $errormsg);
} elseif (isset($successmsg)) {
    $s->assign('successmsg', $successmsg);
}
$cfg = new Config();
$s->assign('cfg', $cfg);
$s->display('session.register.tpl');

?>
