<?php
require_once ('config.crawler.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("init.php");

$crawler = new Crawler();

//Include crawler plugin files
$plugin_files = Utils::getPlugins('plugins');
foreach ($plugin_files as $pf) {
	require_once 'plugins/'.$pf.'/'.$pf.'.php';
}

$crawler->crawl();

?>
