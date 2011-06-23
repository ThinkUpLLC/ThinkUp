<?php
echo 'ThinkUpAppVersion.serverResponse([{';
$current_version = '0.13';
if (isset($_GET['v'])) {
	$install_version = $_GET['v'];
	if (version_compare($current_version, $install_version, '>')) {
		echo '"version":"Version '.$current_version.' available"';
	} else {
		echo '"version":""';
	}
} else {
	echo '"version":""';
}
echo "}]);";

