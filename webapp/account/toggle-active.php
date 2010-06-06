<?php
chdir("..");


require_once 'init.php';

session_start();
$session = new Session();
if (!$session->isLoggedIn()) {
    header("Location: ../index.php");
}

$uid = $_GET["u"];
$p = $_GET["p"];
if ($p != 1) {
    $p = false;
} else  {
    $p = true;
}

$id = DAOFactory::getDAO('InstanceDAO');

echo $id->setActive($uid, $p);

$db->closeConnection($conn);
?>
