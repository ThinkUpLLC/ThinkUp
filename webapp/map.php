<?php
require_once 'init.php';
require_once 'controller/class.MapController.php';

$controller = new MapController();
echo $controller->go();