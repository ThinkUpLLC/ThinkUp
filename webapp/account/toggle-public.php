<?php
chdir("..");
require_once ('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("init.php");

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

$db = new Database($THINKTANK_CFG);
$conn = $db->getConnection();

$id = new InstanceDAO($db);

$id->setPublic($u, $p);

$db->closeConnection($conn);
?>
