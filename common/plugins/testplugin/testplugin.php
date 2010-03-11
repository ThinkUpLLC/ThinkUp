<?php


function testplugin_configuration() {
	global $s;
	$s->assign('message', 'Hello, world! This is the configuration page for the test plugin.');
}


$webapp->addToConfigMenu('testplugin', 'My Test Plugin');
$webapp->registerCallback('testplugin_configuration', 'configuration|testplugin');


?>