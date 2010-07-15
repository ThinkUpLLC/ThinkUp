<?php
chdir('..');
require_once 'init.php';
require_once 'controller/class.ActivateAccountController.php';
require_once 'controller/class.LoginController.php';

$controller = new ActivateAccountController();
echo $controller->go();
