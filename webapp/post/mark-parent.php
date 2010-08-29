<?php
chdir("..");
require_once 'init.php';

$controller = new MarkParentController();
echo $controller->go();
