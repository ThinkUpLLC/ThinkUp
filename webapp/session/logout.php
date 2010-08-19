<?php
chdir('..');
require_once 'init.php';

$controller = new LogoutController();
echo $controller->go();
