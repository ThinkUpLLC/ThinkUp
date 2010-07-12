<?php
chdir("..");
require_once 'init.php';
require_once 'controller/class.ThinkUpAdminController.php';
require_once 'controller/class.PluginOptionController.php';

$controller = new PluginOptionController();
echo $controller->go();
