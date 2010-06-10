<?php
chdir('..');
require_once 'init.php';
require_once 'controller/class.PasswordResetController.php';

$controller = new PasswordResetController();
echo $controller->go();
