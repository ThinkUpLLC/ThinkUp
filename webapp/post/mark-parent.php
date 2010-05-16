<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: /session/login.php"); }

//TODO: check that parent id and all orphan id's are valid and in the db, pass a success or error message back
echo $_GET["pid"];
echo "<br />";
$pid = $_GET["pid"];


$oid =  $_GET["oid"];

$template = $_GET["t"];
$cache_key = $_GET["ck"];

foreach ($oid as $o) {
    echo $o;
    echo "<br />";
}

// set up
chdir("..");

require_once 'init.php';

$cfg = new Config();

$pd = new PostDAO($db);


foreach ($oid as $o) {
    echo "<br />";

    if ( isset($_GET["fp"]))
    $pd->assignParent($pid, $o, $_GET["fp"]);
    else
    $pd->assignParent($pid, $o);

}

$db->closeConnection($conn);

$s = new SmartyThinkTank();
$s->clear_cache($template, $cache_key);

echo 'Assignment complete.<br /><a href="'.$THINKTANK_CFG['site_root_path'].'?u='.$_GET['u'].'#replies">Back home</a>.';

?>
