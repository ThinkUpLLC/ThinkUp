<?php
chdir("..");
require_once 'init.php';

$controller = new UserController();
echo $controller->go();
