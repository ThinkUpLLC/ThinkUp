<?php
session_start(); 
$session = new Session();
$session->logout();
header("Location: login.php");
?> 
