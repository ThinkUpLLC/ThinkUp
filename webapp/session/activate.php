<?php 
session_start();

// set up
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");

if (!isset($_GET['usr']) || !isset($_GET['code']) ) {
    echo "ERROR: Invalid code given...";
    exit(); 
}
$db = new Database($THINKTANK_CFG);
$conn = $db->getConnection();
$od = new OwnerDAO($db);

$acode = $od->getActivationCode($_GET['usr']);

if ($_GET['code'] == $acode['activation_code']) {
    $od->updateActivate($_GET['usr']);
    echo "<h3>Thank you </h3>Email confirmed and account activated. You can <a href=\"login.php\">login</a> now..";
} else {
    echo "ERROR: Incorrect activation code...not valid"; 
}

?>
