<?php

// set up
chdir("..");
require_once ('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("init.php");

session_start(); 
$session = new Session();
$session->logout();
//header("Location: login.php?smsg=You+have+successfully+logged+out.");
header("Location: ../index.php?smsg=You+have+successfully+logged+out.")
?> 
