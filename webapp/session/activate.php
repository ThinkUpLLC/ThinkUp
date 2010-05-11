<?php
session_start();

// set up
chdir("..");


require_once("init.php");

if (!isset($_GET['usr']) || !isset($_GET['code']) ) {
	echo "ERROR: Invalid code given...";
	exit();
}

$od = new OwnerDAO($db);

$acode = $od->getActivationCode($_GET['usr']);

$success = false;
if ($_GET['code'] == $acode['activation_code']) {
	$od->updateActivate($_GET['usr']);
	$success = true;
} 

$db->closeConnection($conn);

if ( $success ) {
	header("Location: login.php?smsg=Success!+Your+account+has+been+activated.+You+may+sign+into+ThinkTank.");
} else {
	header("Location: login.php?emsg=Houston,+we+have+a+problem:+account+activation+failed.");
}

?>
