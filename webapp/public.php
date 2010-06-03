<?php
require_once 'init.php';
require_once 'controller/class.PublicTimelineController.php';

$controller = new PublicTimelineController();
echo $controller->go();
