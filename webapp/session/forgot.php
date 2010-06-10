<?php
chdir('..');
require_once 'init.php';
require_once 'model/class.Mailer.php';
require_once 'controller/class.ForgotPasswordController.php';

$controller = new ForgotPasswordController();
echo $controller->go();
