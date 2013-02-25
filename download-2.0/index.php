<?php
chdir('..');
require_once('version.beta.inc.php');

header('Location: '.$current_version_download_link);