<?php
$dbname = $THINKTANK_CFG['db_name'];
$link = mysql_connect($THINKTANK_CFG['db_host'],$THINKTANK_CFG['db_user'],$THINKTANK_CFG['db_password'] ) or die("Couldn't make connection.");
$db = mysql_select_db($dbname, $link) or die("Couldn't select database");
?>