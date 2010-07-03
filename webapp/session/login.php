<?php
chdir('..');
require_once 'init.php';
require_once 'controller/class.LoginController.php';
require_once 'controller/class.PrivateDashboardController.php';

$controller = new LoginController();
echo $controller->go();
