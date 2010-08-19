<?php
chdir("..");
require_once 'init.php';

$controller = new ToggleActiveInstanceController();
echo $controller->go();
