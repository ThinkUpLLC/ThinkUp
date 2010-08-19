<?php
chdir("..");
require_once 'init.php';

$controller = new PluginOptionController();
echo $controller->go();
