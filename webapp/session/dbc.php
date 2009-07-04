<?php
$dbname = $TWITALYTIC_CFG['db_name'];
$link = mysql_connect($TWITALYTIC_CFG['db_host'],$TWITALYTIC_CFG['db_user'],$TWITALYTIC_CFG['db_password'] ) or die("Couldn't make connection.");
$db = mysql_select_db($dbname, $link) or die("Couldn't select database");
?>