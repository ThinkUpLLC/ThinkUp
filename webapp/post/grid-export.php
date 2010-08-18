<?php
chdir("..");
require_once 'init.php';

$controller = new GridExportController();
echo $controller->go();