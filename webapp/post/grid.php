<?php
chdir("..");
require_once 'init.php';

$controller = new GridController();
echo $controller->go();