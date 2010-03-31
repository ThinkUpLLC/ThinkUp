<?php

/* 
 Plugin Name: Hello ThinkTank
 Plugin URI: http://github.com/ginatrapani/thinktank/tree/master/common/plugins/testplugin/
 Description: The "Hello, world!" of ThinkTank plugins.
 Version: 0.01
 Author: Gina Trapani
*/

function testplugin_configuration() {
	global $s;
	$s->assign('message', 'Hello, world! This is the configuration page for the test plugin.');
}


$webapp->addToConfigMenu('testplugin', 'Hello ThinkTank');
$webapp->registerCallback('testplugin_configuration', 'configuration|testplugin');


?>