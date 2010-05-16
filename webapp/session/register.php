<?php

session_start();

// set up
chdir("..");

require_once 'init.php';
require_once 'model/class.Mailer.php';

$session = new Session();
if ($session->isLoggedIn()) {
    header("Location: ../index.php");
}

$od = new OwnerDAO($db);
$s = new SmartyThinkTank();
$s->caching=false;

if (!$THINKTANK_CFG['is_registration_open']) {
    $s->assign('closed', true);
    $errormsg = '<p>Sorry, registration is closed on this ThinkTank installation.</p><p><a href="http://github.com/ginatrapani/thinktank/tree/master">Install ThinkTank on your own server.</a></p>';
}
else {
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
            if (!isset($errormsg)) {
                $errormsg = "Password does not match or empty.";
            }
        }
        elseif (!$captcha->check()) {
            // Captcha not valid, captcha handles message...
        }
        else {
            if ($od->doesOwnerExist($_POST['email'])) {
                $errormsg = "User account already exists.";
            }
            else {
                $es = new SmartyThinkTank();
                $es->caching=false;

                $activ_code = rand(1000, 9999);
                $cryptpass = $session->pwdcrypt($_POST['pass2']);
                $server = $_SERVER['HTTP_HOST'];
                $od->create($_POST['email'], $cryptpass, $_POST['country'], $activ_code, $_POST['full_name']);

                $es->assign('apptitle', $THINKTANK_CFG['app_title'] );
                $es->assign('server', $server );
                $es->assign('site_root_path', $THINKTANK_CFG['site_root_path'] );
                $es->assign('email', urlencode($_POST['email']) );
                $es->assign('activ_code', $activ_code );
                $message = $es->fetch('_email.registration.tpl');

                Mailer::mail($_POST['email'], "Activate Your ".$THINKTANK_CFG['app_title'] ." Account", $message);
                // echo $message; // debug

                unset($_SESSION['ckey']);
                $successmsg = "Success! Check your email for an activation link.";
            }
        }
        $s->assign('name', $_POST["full_name"]);
        $s->assign('mail', $_POST["email"]);
    }
    $challenge = $captcha->generate();
    $s->assign('captcha', $challenge);
}

if (isset($errormsg)) {
    $s->assign('errormsg', $errormsg);
}
elseif (isset($successmsg)) {
    $s->assign('successmsg', $successmsg);
}

$db->closeConnection($conn);
$cfg = new Config();
$s->assign('cfg', $cfg);
$s->display('session.register.tpl');

?>
