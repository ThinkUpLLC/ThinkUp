<?php
require_once('version.inc.php');
header('Content-type: application/json');
?>
{"version":"<?= $current_version ?>", "url":"<?= $current_version_download_link ?>"}