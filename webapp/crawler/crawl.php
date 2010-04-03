<?php 
chdir("..");
require_once ("common/init.php");

$authorized = false;

if ($argc > 1) { // check for CLI credentials
    $session = new Session();
    $username = $argv[1];
    $pw = $argv[2];
    
    $od = new OwnerDAO($db);
    $result = $od->getForLogin($username);
    if ($session->pwdCheck($pw, $result['pwd'])) {
        $authorized = true;
        echo "Authorized to run crawler.";
    } else {
        echo "Incorrect username and password.";
    }
} else { // check user is logged in on the web
    session_start();
    $session = new Session();
    if ($session->isLoggedIn()) {
        $authorized = true;
    }
}

if ($authorized) {
    $crawler->crawl();
    
    if (isset($conn)) {
        $db->closeConnection($conn); // Clean up
    }
}



?>
