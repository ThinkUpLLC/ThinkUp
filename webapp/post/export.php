<?php
chdir("..");
require_once 'init.php';
require_once 'controller/class.ExportController.php';

$controller = new ExportController();
echo $controller->go();
