<?php
chdir('..');
require_once 'init.php';
require_once 'controller/class.PublicTimelineController.php';
require_once 'model/class.Mailer.php';
require_once 'controller/class.RegisterController.php';
require_once 'controller/class.PrivateDashboardController.php';

$controller = new RegisterController();
echo $controller->go();
