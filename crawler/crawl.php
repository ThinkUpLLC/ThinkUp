<?php 
require_once ('config.crawler.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("init.php");

$crawler->crawl();


if (isset($conn)) {
    $db->closeConnection($conn); // Clean up
}

?>
