<?php
chdir("..");
chdir("..");
require_once 'init.php';
require_once 'plugins/facebook/controller/class.FacebookAuthController.php';

$controller = new FacebookAuthController();
echo $controller->go();
