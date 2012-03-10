<?php
/**
 * Return current version and download location in JSON format, with override for web updater tests.
 */
require_once('version.inc.php');
header('Content-type: application/json');

// Test override
if (isset($_GET['version']) && isset($_GET['url'])) {
	$current_version = $_GET['version'];
	$current_version_download_link = $_GET['url'];
}
?>
{"version":"<?= $current_version ?>", "url":"<?= $current_version_download_link ?>"}