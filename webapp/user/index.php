<?php
chdir("..");
require_once 'init.php';
require_once 'controller/class.UserController.php';

$controller = new UserController();
echo $controller->go();
