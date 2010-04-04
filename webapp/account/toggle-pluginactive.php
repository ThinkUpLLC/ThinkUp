<?php
chdir("..");


require_once ("common/init.php");

session_start();
$session = new Session();
if (!$session->isLoggedIn()) {
    header("Location: ../index.php");
}

$pid = $_GET["pid"];
$a = $_GET["a"];
if ($a != 1) {
	$a = 0;
}

$pd = new PluginDAO($db);

$pd->setActive($pid, $a);

$db->closeConnection($conn);	
?>
