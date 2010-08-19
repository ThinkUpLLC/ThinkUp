<?php
chdir('..');
require_once 'init.php';

$controller = new ActivateAccountController();
echo $controller->go();
