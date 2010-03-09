<?php
require_once ('config.crawler.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once ("init.php");

$crawler = new Crawler();


//Include all the php files in the common/plugins/ directories.
$plugin_files = Utils::getPlugins($THINKTANK_CFG['source_root_path'].'crawler/plugins');
foreach ($plugin_files as $pf) {
	foreach(glob($THINKTANK_CFG['source_root_path'].'crawler/plugins/'.$pf."/*.php") as $includefile) {
		require_once($includefile);
	}
}

$crawler->crawl();

?>
