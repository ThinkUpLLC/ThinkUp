<?php
chdir('..');
require_once 'init.php';
require_once 'controller/class.LogoutController.php';
require_once 'controller/class.PublicTimelineController.php';

$controller = new LogoutController();
echo $controller->go();
