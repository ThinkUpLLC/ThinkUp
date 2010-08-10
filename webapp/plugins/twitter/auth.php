<?php
chdir("..");
chdir("..");
require_once 'init.php';
require_once 'plugins/twitter/controller/class.TwitterAuthController.php';

$controller = new TwitterAuthController();
echo $controller->go();
