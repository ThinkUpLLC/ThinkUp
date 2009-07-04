<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: /session/login.php"); }

//TODO: check that parent id and all orphan id's are valid and in the db, pass a success or error message back
echo $_GET["pid"];
echo "<br />";
$pid = $_GET["pid"];


$oid =  $_GET["oid"];

foreach ($oid as $o) {
	echo $o;
	echo "<br />";	
}



// set up
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").":".$INCLUDE_PATH);
require_once("init.php");

$cfg = new Config();
$db = new Database();
$conn = $db->getConnection();

$td = new TweetDAO();


foreach ($oid as $o) {
	echo "<br />";
	
	$td->assignParent($pid, $o);
	
}

$db->closeConnection($conn);	

echo 'Assignment complete.<br /><a href="'.$TWITALYTIC_CFG['site_root_path'].'">Back home</a>.';

?>