<?php
chdir("..");
require_once 'init.php';

$controller = new AccountConfigurationController();
echo $controller->go();
