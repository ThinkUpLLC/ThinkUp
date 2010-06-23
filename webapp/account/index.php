<?php
chdir("..");
require_once 'init.php';
require_once 'controller/class.AccountConfigurationController.php';

$controller = new AccountConfigurationController();
echo $controller->go();
