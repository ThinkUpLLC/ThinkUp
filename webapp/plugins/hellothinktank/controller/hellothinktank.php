<?php

/* 
 Plugin Name: Hello ThinkTank
 Plugin URI: http://github.com/ginatrapani/thinktank/tree/master/model/plugins/hellothinktank/
 Description: The "Hello, world!" of ThinkTank plugins.
 Version: 0.01
 Icon: assets/img/plugin_icon.png
 Author: Gina Trapani
*/

function hellothinktank_configuration() {
    global $s;
    $s->assign('message', 'Hello, world! This is the configuration page for the test plugin.');
}


$webapp->addToConfigMenu('hellothinktank', 'Hello ThinkTank');
$webapp->registerCallback('hellothinktank_configuration', 'configuration|hellothinktank');


?>