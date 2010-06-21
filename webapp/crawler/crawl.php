<?php
chdir("..");
require_once 'init.php';

$authorized = false;

if (isset($argc) && $argc > 1) { // check for CLI credentials
    $session = new Session();
    $username = $argv[1];
    $pw = $argv[2];

    $od = DAOFactory::getDAO('OwnerDAO');
    $result = $od->getForLogin($username);
    if ($session->pwdCheck($pw, $result['pwd'])) {
        $authorized = true;
    } else {
        echo "ERROR: Incorrect username and password.";
    }
} else { // check user is logged in on the web
    session_start();
    $session = new Session();
    if ($session->isLoggedIn()) {
        $authorized = true;
    } else {
        echo "ERROR: Invalid or missing username and password.";
    }
}

if ($authorized) {
    $crawler->crawl();

    if (isset($conn)) {
        $db->closeConnection($conn); // Clean up
    }
}
