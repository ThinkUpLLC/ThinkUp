<?php 
session_start();
// set up
chdir("..");
require_once('config.webapp.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once("init.php");

include 'dbc.php';

if (!isset($_GET['usr']) && !isset($_GET['code']) ) {
$msg = "ERROR: Invalid code...";
exit(); }

$rsCode = mysql_query("SELECT activation_code from ".$TWITALYTIC_CFG['table_prefix']."owners where user_email='$_GET[usr]'") or die(mysql_error());

list($acode) = mysql_fetch_array($rsCode);

if ($_GET['code'] == $acode)
{
mysql_query("update ".$TWITALYTIC_CFG['table_prefix']."owners set user_activated=1 where user_email='$_GET[usr]'") or die(mysql_error());
echo "<h3>Thank you </h3>Email confirmed and account activated. You can <a href=\"login.php\">login</a> now..";
} else
{ echo "ERROR: Incorrect activation code...not valid"; }


?>

