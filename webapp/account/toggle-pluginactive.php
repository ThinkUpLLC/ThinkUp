<?php
chdir("..");
require_once 'init.php';

$controller = new ToggleActivePluginController();
echo $controller->go();
