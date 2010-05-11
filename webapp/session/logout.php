<?php

// set up
chdir("..");


require_once ("init.php");

session_start(); 
$session = new Session();
$session->logout();
//header("Location: login.php?smsg=You+have+successfully+logged+out.");
header("Location: ../index.php?smsg=You+have+successfully+logged+out.")
?> 
