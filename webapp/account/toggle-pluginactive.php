<?php
chdir("..");
require_once 'init.php';
require_once 'controller/class.ToggleActivePluginController.php';

$controller = new ToggleActivePluginController();
echo $controller->go();
