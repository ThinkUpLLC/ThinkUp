<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: /session/login.php"); }

$u = $_GET["u"];
$p = $_GET["p"];
if ($p != 1)
	$p = 0;

chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");

$cfg = new Config();
$SQLLogger = new LoggerSlowSQL($THINKTANK_CFG['sql_log_location']);
$db = new Database($THINKTANK_CFG, $SQLLogger);
$conn = $db->getConnection();

$id = new InstanceDAO($db);

$id->setPublic($u, $p);

$db->closeConnection($conn);	
$SQLLogger->close();

?>
