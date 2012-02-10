<?php
chdir('..');
require_once('version.inc.php');

header('Location: '.$current_version_download_link);