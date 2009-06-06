<?php
//TODO: check that parent id and all orphan id's are valid and in the db, pass a success or error message back
echo $_GET["pid"];
echo "<br />";
$pid = $_GET["pid"];


$oid =  $_GET["oid"];

foreach ($oid as $o) {
	echo $o;
	echo "<br />";	
}

chdir("..");
$root_path 			= realpath('./../include')."/";
require_once($root_path . "init.php");
$cfg = new Config();
$db = new Database();
$c = new Crawler();

$conn = $db->getConnection();

$td = new TweetDAO();


foreach ($oid as $o) {
	echo "<br />";
	
	$td->assignParent($pid, $o);
	
}


header("Location: ".$cfg->webapp_home."replies/?t=$pid#replies");
?>