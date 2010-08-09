<?php
chdir("..");
require_once 'init.php';
require_once 'controller/class.ToggleActiveInstanceController.php';

$controller = new ToggleActiveInstanceController();
echo $controller->go();
