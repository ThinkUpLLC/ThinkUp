<?php
chdir("..");


require_once ("common/init.php");

session_start();
$session = new Session();
if (!$session->isLoggedIn()) {
    header("Location: ../index.php");
}

$u = $_GET["u"];
$p = $_GET["p"];
if ($p != 1) {
	$p = 0;
}

$id = new InstanceDAO($db);

$id->setActive($u, $p);

$db->closeConnection($conn);	
?>
