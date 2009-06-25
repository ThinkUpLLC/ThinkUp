<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: /u/login.php"); }

// set up
chdir("..");
$root_path 			= realpath('./../include')."/";
require_once($root_path . "init.php");
$db = new Database();
$conn = $db->getConnection();

$id = new InstanceDAO();
$od = new OwnerDAO();
$cfg = new Config();
$s = new SmartyTwitalytic();

$owner = $od->getByEmail($_SESSION['user']);
$owner_instances = $id->getByOwnerId($owner->id);

$s->assign('owner_instances', $owner_instances );
$s->assign('owner', $owner);
$s->assign('cfg', $cfg);

# clean up
$db->closeConnection($conn);	

echo $s->fetch('accounts.index.tpl');
?>