<?php
$version = explode('.', PHP_VERSION); //dont run redis or instagram test for php less than 5.3
if ($version[0] >= 5 && $version[1] >= 3) { //only run Instagram tests if PHP 5.3
	require_once dirname(__FILE__) . '/PHP5.3/' . basename(__FILE__);
}
?>