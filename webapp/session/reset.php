<?php
chdir('..');
require_once 'init.php';

$controller = new PasswordResetController();
echo $controller->go();
