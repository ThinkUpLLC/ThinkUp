<?php
chdir("..");
require_once '_lib/model/class.Loader.php';
Loader::register();

Utils::defineConstants();

$controller = new UpgradeController();
echo $controller->go();

