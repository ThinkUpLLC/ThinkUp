<?php
chdir("..");
require_once 'init.php';

$controller = new ExportController();
echo $controller->go();
