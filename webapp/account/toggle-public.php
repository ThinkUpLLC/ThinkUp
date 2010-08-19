<?php
chdir("..");
require_once 'init.php';

$controller = new TogglePublicInstanceController();
echo $controller->go();
