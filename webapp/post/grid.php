<?php
chdir("..");
require_once 'init.php';
require_once 'controller/class.GridController.php';

$controller = new GridController();
echo $controller->go();