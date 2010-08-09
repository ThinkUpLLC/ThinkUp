<?php
chdir("..");
require_once 'init.php';
require_once 'controller/class.TogglePublicInstanceController.php';

$controller = new TogglePublicInstanceController();
echo $controller->go();
