<?php
require_once 'init.php';
require_once 'controller/class.PrivateDashboardController.php';
require_once 'controller/class.PublicTimelineController.php';

$controller = new PrivateDashboardController();
echo $controller->go();
