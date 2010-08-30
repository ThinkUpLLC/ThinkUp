<?php
chdir("..");
require_once '_lib/model/class.Loader.php';
Loader::register();

Utils::defineConstants();

$controller = new InstallerController();
echo $controller->go();