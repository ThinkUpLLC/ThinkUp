<?php
chdir("..");
require_once 'model/class.Loader.php';
Loader::register();

Utils::defineConstants();

$controller = new InstallerController();
echo $controller->go();